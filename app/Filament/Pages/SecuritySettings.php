<?php

namespace App\Filament\Pages;

use App\Enums\AdminNavigationGroup;
use App\Enums\AdminPermission;
use App\Filament\Support\SettingsFormTabs;
use App\Models\Setting;
use App\Services\SecurityLogger;
use App\Support\AdminSecurityConfig;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class SecuritySettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::Security;

    protected static ?string $navigationLabel = 'Security';

    protected static ?int $navigationSort = 0;

    protected static ?string $title = 'Security Settings';

    protected static ?string $slug = 'security-settings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user?->hasFullPanelAccess()
            || $user?->hasAdminPermission(AdminPermission::SettingsPermissions)
            || $user?->hasAdminPermission(AdminPermission::SecurityAuditLog);
    }

    public function mount(): void
    {
        $this->form->fill([
            'admin_session_lifetime_minutes' => AdminSecurityConfig::sessionLifetimeMinutes(),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $minutes = (int) ($data['admin_session_lifetime_minutes'] ?? 120);

        if (! in_array($minutes, AdminSecurityConfig::ALLOWED_SESSION_MINUTES, true)) {
            $minutes = 120;
        }

        DB::transaction(function () use ($minutes): void {
            Setting::persistBatch(function () use ($minutes): void {
                Setting::set('admin_session_lifetime_minutes', (string) $minutes, 'security');
            });
        });

        SecurityLogger::logSettingsSaved('Security settings');

        Notification::make()
            ->success()
            ->title('Security settings saved')
            ->body('Admin sessions will time out after '.$minutes.' minutes of inactivity.')
            ->send();
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SettingsFormTabs::make('Security settings', [
                    Tab::make('Admin sessions')
                        ->icon('heroicon-o-clock')
                        ->schema([
                            Section::make('Admin inactivity timeout')
                                ->description('Parish admins are signed out automatically after this period with no activity in the admin panel. Activity is tracked on admin pages and admin Livewire requests.')
                                ->schema([
                                    Select::make('admin_session_lifetime_minutes')
                                        ->label('Sign out after inactivity')
                                        ->options(AdminSecurityConfig::sessionLifetimeOptions())
                                        ->required()
                                        ->native(false)
                                        ->helperText('Choose 1 or 2 hours for tighter security, or up to 3 hours for longer editing sessions. Server .env ADMIN_SESSION_LIFETIME is only used when no value is saved here.'),
                                    Placeholder::make('session_policy_note')
                                        ->label('How it works')
                                        ->content('Every click, save, or navigation in admin resets the timer. When time runs out, the session is destroyed and the admin must sign in again. A warning appears five minutes before expiry.'),
                                ]),
                            Section::make('Environment defaults')
                                ->schema([
                                    Placeholder::make('login_attempts')
                                        ->label('Failed admin login lockout')
                                        ->content(config('security.max_login_attempts', 5).' attempts per '.config('security.login_decay_minutes', 15).' minutes (set via .env)'),
                                    Placeholder::make('member_login_note')
                                        ->label('Member portal')
                                        ->content('Member sign-in uses a separate rate limiter and is not affected by admin lockout settings.'),
                                ]),
                        ]),
                ], 'security-tab'),
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
                            Action::make('save')
                                ->label('Save security settings')
                                ->submit('save'),
                        ]),
                    ]),
            ]);
    }
}
