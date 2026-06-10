<?php

namespace App\Filament\Pages;

use App\Enums\AdminNavigationGroup;
use App\Enums\AdminPermission;
use App\Filament\Support\SettingsFormTabs;
use App\Models\Setting;
use App\Services\MailConfigService;
use App\Services\SecurityLogger;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\ValidationException;

class MailSettings extends Page
{
    use CanUseDatabaseTransactions;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::SiteSettings;

    protected static ?string $navigationLabel = 'Email setup';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Email & SMTP Setup';

    protected static ?string $slug = 'mail-settings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user?->hasFullPanelAccess()
            || $user?->hasAdminPermission(AdminPermission::SettingsMail);
    }

    public function mount(): void
    {
        $mailer = (string) (Setting::get('mail_mailer') ?: 'sendmail');
        $toggles = MailConfigService::togglesFromMailer($mailer);

        $this->form->fill([
            'mail_log_only' => $toggles['mail_log_only'],
            'mail_use_smtp' => $toggles['mail_use_smtp'],
            'mail_host' => Setting::get('mail_host'),
            'mail_port' => Setting::get('mail_port', '587'),
            'mail_username' => Setting::get('mail_username'),
            'mail_encryption' => Setting::get('mail_encryption', 'tls'),
            'mail_smtp_timeout' => Setting::get('mail_smtp_timeout', '10'),
            'mail_sendmail_path' => Setting::get('mail_sendmail_path') ?: config('mail.mailers.sendmail.path'),
            'mail_sendmail_timeout' => Setting::get('mail_sendmail_timeout', '15'),
            'mail_from_address' => Setting::get('mail_from_address') ?: Setting::get('contact_email'),
            'mail_from_name' => Setting::get('mail_from_name') ?: Setting::get('church_name'),
            'mail_test_recipient' => Setting::get('contact_email'),
        ]);
    }

    public function save(): void
    {
        try {
            $this->beginDatabaseTransaction();

            $data = MailConfigService::normalizeFormData($this->form->getState());

            Setting::set('mail_mailer', $data['mail_mailer'] ?? 'sendmail', 'mail');
            Setting::set('mail_host', $data['mail_host'] ?? '', 'mail');
            Setting::set('mail_port', (string) ($data['mail_port'] ?? '587'), 'mail');
            Setting::set('mail_username', $data['mail_username'] ?? '', 'mail');
            Setting::set('mail_encryption', $data['mail_encryption'] ?? 'tls', 'mail');
            Setting::set('mail_smtp_timeout', (string) ($data['mail_smtp_timeout'] ?? '10'), 'mail');
            Setting::set('mail_sendmail_path', $data['mail_sendmail_path'] ?? '', 'mail');
            Setting::set('mail_sendmail_timeout', (string) ($data['mail_sendmail_timeout'] ?? '15'), 'mail');
            Setting::set('mail_from_address', $data['mail_from_address'] ?? '', 'mail');
            Setting::set('mail_from_name', $data['mail_from_name'] ?? '', 'mail');

            if (! empty($data['mail_password'])) {
                Setting::set('mail_password', MailConfigService::encryptPassword($data['mail_password']), 'mail');
            }

            Setting::forgetCache();
            MailConfigService::applyFromSettings();

            $this->commitDatabaseTransaction();

            SecurityLogger::logSettingsSaved('Email & SMTP settings');

            Notification::make()
                ->success()
                ->title('Mail settings saved')
                ->send();
        } catch (\Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }
    }

    public function sendTestEmail(): void
    {
        try {
            try {
                $data = MailConfigService::normalizeFormData($this->form->getState());
            } catch (ValidationException $exception) {
                Notification::make()
                    ->danger()
                    ->title('Fix the form first')
                    ->body(collect($exception->errors())->flatten()->first() ?? 'Check the highlighted fields.')
                    ->send();

                return;
            }

            $recipient = $data['mail_test_recipient'] ?? null;

            if (! $recipient) {
                Notification::make()
                    ->danger()
                    ->title('Enter a test recipient email')
                    ->send();

                return;
            }

            MailConfigService::applyFromFormData($data);

            if ($error = MailConfigService::validateConfiguration()) {
                Notification::make()
                    ->danger()
                    ->title('Mail not configured')
                    ->body($error)
                    ->send();

                return;
            }

            set_time_limit(30);
            MailConfigService::sendTestMessage($recipient);

            $mailer = (string) config('mail.default', 'log');
            $body = match ($mailer) {
                'log' => 'Mail driver is log — check storage/logs/laravel.log instead of an inbox.',
                'sendmail' => "Check {$recipient}. Sent using PHP mail on this server.",
                default => "Check {$recipient}. Sent using SMTP.",
            };

            Notification::make()
                ->success()
                ->title($mailer === 'log' ? 'Test logged' : 'Test email sent')
                ->body($body)
                ->send();
        } catch (\Throwable $exception) {
            report($exception);

            Notification::make()
                ->danger()
                ->title('Test email failed')
                ->body(MailConfigService::friendlyError($exception))
                ->send();
        }
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SettingsFormTabs::make('Email settings', [
                    Tab::make('Delivery')
                        ->icon('heroicon-o-paper-airplane')
                        ->schema([
                            Section::make('Delivery method')
                                ->description('All mail is configured here — nothing is read from server .env. Toggle between PHP sendmail and SMTP, or log only for testing.')
                                ->schema([
                                    Toggle::make('mail_log_only')
                                        ->label('Log only (development)')
                                        ->helperText('Writes messages to storage/logs/laravel.log instead of sending email.')
                                        ->live()
                                        ->afterStateUpdated(function ($state, callable $set): void {
                                            if ($state) {
                                                $set('mail_use_smtp', false);
                                            }
                                        }),
                                    Toggle::make('mail_use_smtp')
                                        ->label('Use SMTP server')
                                        ->helperText('Turn off to use PHP sendmail on this server (typical shared hosting).')
                                        ->live()
                                        ->disabled(fn ($get) => (bool) $get('mail_log_only'))
                                        ->afterStateUpdated(fn ($state, callable $set) => $state ? $set('mail_log_only', false) : null),
                                ]),
                            Section::make('PHP sendmail')
                                ->description('Server mail — uses the sendmail binary on your host.')
                                ->visible(fn ($get) => ! $get('mail_log_only') && ! $get('mail_use_smtp'))
                                ->schema([
                                    TextInput::make('mail_sendmail_path')
                                        ->label('Sendmail command')
                                        ->placeholder('/usr/sbin/sendmail -t -i')
                                        ->helperText('Only needed if PHP mail() is disabled. cPanel default: /usr/sbin/sendmail -t -i'),
                                    TextInput::make('mail_sendmail_timeout')
                                        ->label('Timeout (seconds)')
                                        ->numeric()
                                        ->default(15)
                                        ->minValue(5)
                                        ->maxValue(60),
                                ]),
                            Section::make('SMTP server')
                                ->description('External mail server — Gmail, Office 365, hosting SMTP, etc.')
                                ->visible(fn ($get) => ! $get('mail_log_only') && (bool) $get('mail_use_smtp'))
                                ->schema([
                                    TextInput::make('mail_host')
                                        ->label('SMTP host')
                                        ->placeholder('smtp.example.com')
                                        ->required(fn ($get) => (bool) $get('mail_use_smtp') && ! $get('mail_log_only')),
                                    TextInput::make('mail_port')
                                        ->label('Port')
                                        ->numeric()
                                        ->default(587),
                                    Select::make('mail_encryption')
                                        ->label('Encryption')
                                        ->options([
                                            'tls' => 'TLS',
                                            'ssl' => 'SSL',
                                            'none' => 'None',
                                        ])
                                        ->default('tls'),
                                    TextInput::make('mail_username')
                                        ->label('Username'),
                                    TextInput::make('mail_password')
                                        ->label('Password')
                                        ->password()
                                        ->revealable()
                                        ->helperText(MailConfigService::passwordIsConfigured() ? 'Leave blank to keep the current password.' : 'Enter your SMTP password.'),
                                    TextInput::make('mail_smtp_timeout')
                                        ->label('Timeout (seconds)')
                                        ->numeric()
                                        ->default(10)
                                        ->minValue(5)
                                        ->maxValue(60),
                                ]),
                        ]),
                    Tab::make('Sender')
                        ->icon('heroicon-o-at-symbol')
                        ->schema([
                            Section::make('Sender identity')
                                ->schema([
                                    TextInput::make('mail_from_address')
                                        ->label('From address')
                                        ->email()
                                        ->required(),
                                    TextInput::make('mail_from_name')
                                        ->label('From name'),
                                    TextInput::make('mail_test_recipient')
                                        ->label('Test recipient')
                                        ->email()
                                        ->helperText('Send a test message to verify delivery before going live.'),
                                ]),
                        ]),
                ], 'mail-tab'),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('sendTestEmail')
                                ->label('Send test email')
                                ->icon(Heroicon::OutlinedPaperAirplane)
                                ->action(fn () => $this->sendTestEmail())
                                ->color('gray')
                                ->outlined(),
                            Action::make('save')
                                ->label('Save mail settings')
                                ->submit('save'),
                        ]),
                    ]),
            ]);
    }
}
