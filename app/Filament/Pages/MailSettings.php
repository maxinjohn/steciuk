<?php

namespace App\Filament\Pages;

use App\Enums\AdminNavigationGroup;
use App\Enums\AdminPermission;
use App\Models\Setting;
use App\Services\MailConfigService;
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
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;

class MailSettings extends Page
{
    use CanUseDatabaseTransactions;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string | \UnitEnum | null $navigationGroup = AdminNavigationGroup::SiteSettings;

    protected static ?string $navigationLabel = 'Email Setup';

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

        return $user?->isSuperAdmin()
            || $user?->hasAdminPermission(AdminPermission::SettingsMail);
    }

    public function mount(): void
    {
        $this->form->fill([
            'mail_use_admin_smtp' => (bool) Setting::get('mail_use_admin_smtp', false),
            'mail_mailer' => Setting::get('mail_mailer', 'smtp'),
            'mail_host' => Setting::get('mail_host'),
            'mail_port' => Setting::get('mail_port', '587'),
            'mail_username' => Setting::get('mail_username'),
            'mail_encryption' => Setting::get('mail_encryption', 'tls'),
            'mail_from_address' => Setting::get('mail_from_address') ?: Setting::get('contact_email'),
            'mail_from_name' => Setting::get('mail_from_name') ?: Setting::get('church_name'),
            'mail_test_recipient' => Setting::get('contact_email'),
        ]);
    }

    public function save(): void
    {
        try {
            $this->beginDatabaseTransaction();

            $data = $this->form->getState();

            Setting::set('mail_use_admin_smtp', $data['mail_use_admin_smtp'] ? '1' : '0', 'mail');
            Setting::set('mail_mailer', $data['mail_mailer'] ?? 'smtp', 'mail');
            Setting::set('mail_host', $data['mail_host'] ?? '', 'mail');
            Setting::set('mail_port', (string) ($data['mail_port'] ?? '587'), 'mail');
            Setting::set('mail_username', $data['mail_username'] ?? '', 'mail');
            Setting::set('mail_encryption', $data['mail_encryption'] ?? 'tls', 'mail');
            Setting::set('mail_from_address', $data['mail_from_address'] ?? '', 'mail');
            Setting::set('mail_from_name', $data['mail_from_name'] ?? '', 'mail');

            if (! empty($data['mail_password'])) {
                Setting::set('mail_password', MailConfigService::encryptPassword($data['mail_password']), 'mail');
            }

            Setting::forgetCache();
            MailConfigService::applyFromSettings();

            $this->commitDatabaseTransaction();

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
        $data = $this->form->getState();
        $recipient = $data['mail_test_recipient'] ?? null;

        if (! $recipient) {
            Notification::make()
                ->danger()
                ->title('Enter a test recipient email')
                ->send();

            return;
        }

        MailConfigService::applyFromSettings();

        try {
            Mail::raw(
                'This is a test message from the STECI UK parish admin panel. SMTP settings are working.',
                fn ($message) => $message->to($recipient)->subject('STECI UK — SMTP test')
            );

            Notification::make()
                ->success()
                ->title('Test email sent')
                ->body("Check {$recipient} for the test message.")
                ->send();
        } catch (\Throwable $exception) {
            Notification::make()
                ->danger()
                ->title('Test email failed')
                ->body($exception->getMessage())
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
                Section::make('SMTP delivery')
                    ->description('When enabled, contact forms and admin notifications use these settings instead of server environment variables.')
                    ->schema([
                        Toggle::make('mail_use_admin_smtp')
                            ->label('Use admin-configured SMTP')
                            ->live(),
                        Select::make('mail_mailer')
                            ->label('Mailer')
                            ->options([
                                'smtp' => 'SMTP',
                                'log' => 'Log only (development)',
                            ])
                            ->default('smtp')
                            ->visible(fn ($get) => (bool) $get('mail_use_admin_smtp')),
                        TextInput::make('mail_host')
                            ->label('SMTP host')
                            ->placeholder('smtp.example.com')
                            ->visible(fn ($get) => (bool) $get('mail_use_admin_smtp')),
                        TextInput::make('mail_port')
                            ->label('Port')
                            ->numeric()
                            ->default(587)
                            ->visible(fn ($get) => (bool) $get('mail_use_admin_smtp')),
                        Select::make('mail_encryption')
                            ->options([
                                'tls' => 'TLS',
                                'ssl' => 'SSL',
                                'none' => 'None',
                            ])
                            ->default('tls')
                            ->visible(fn ($get) => (bool) $get('mail_use_admin_smtp')),
                        TextInput::make('mail_username')
                            ->label('Username')
                            ->visible(fn ($get) => (bool) $get('mail_use_admin_smtp')),
                        TextInput::make('mail_password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->helperText(MailConfigService::passwordIsConfigured() ? 'Leave blank to keep the current password.' : 'Enter your SMTP password.')
                            ->visible(fn ($get) => (bool) $get('mail_use_admin_smtp')),
                    ]),
                Section::make('Sender identity')
                    ->schema([
                        TextInput::make('mail_from_address')
                            ->label('From address')
                            ->email(),
                        TextInput::make('mail_from_name')
                            ->label('From name'),
                        TextInput::make('mail_test_recipient')
                            ->label('Test recipient')
                            ->email()
                            ->helperText('Send a test message to verify SMTP before going live.'),
                    ]),
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
                            Action::make('test')
                                ->label('Send test email')
                                ->action('sendTestEmail')
                                ->color('gray'),
                            Action::make('save')
                                ->label('Save mail settings')
                                ->submit('save'),
                        ]),
                    ]),
            ]);
    }
}
