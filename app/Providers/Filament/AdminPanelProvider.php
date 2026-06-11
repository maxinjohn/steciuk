<?php

namespace App\Providers\Filament;

use App\Enums\AdminNavigationGroup;
use App\Filament\Support\AdminBranding;
use App\Models\Setting;
use App\Support\AdminPanelConfig;
use App\Filament\Auth\Login as AdminLogin;
use App\Filament\Widgets\AdminQuickLinksWidget;
use App\Filament\Widgets\AdminWelcomeWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\UpcomingEventsWidget;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use App\Http\Middleware\AdminSessionTimeout;
use App\Http\Middleware\ThrottleAdminLogin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path(AdminPanelConfig::path())
            ->login(AdminLogin::class)
            ->passwordReset()
            ->profile(isSimple: false)
            ->brandName(AdminPanelConfig::name())
            ->brandLogo(fn () => view('filament.admin.logo'))
            ->favicon(fn (): string => AdminBranding::faviconUrl())
            ->colors([
                'primary' => Color::Amber,
                'gray' => Color::Slate,
                'danger' => Color::Rose,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
                'info' => Color::Sky,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop()
            ->sidebarWidth('18rem')
            ->maxContentWidth(Width::Full)
            ->databaseTransactions(
                config('database.default') !== 'sqlite' && ! app()->environment('testing'),
            )
            ->darkMode(true)
            ->defaultThemeMode(ThemeMode::Light)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => filament()->auth()->check() ? view('filament.admin.mobile-dock') : '',
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => view('filament.admin.scripts'),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn () => view('filament.admin.login-banner'),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn () => view('filament.admin.session-notice'),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn () => view('filament.admin.login-lockout'),
            )
            ->navigationGroups(AdminNavigationGroup::class)
            ->collapsibleNavigationGroups(true)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AdminWelcomeWidget::class,
                StatsOverviewWidget::class,
                UpcomingEventsWidget::class,
                AdminQuickLinksWidget::class,
                AccountWidget::class,
            ])
            ->multiFactorAuthentication([
                AppAuthentication::make(),
            ], isRequired: fn (): bool => (bool) config('security.require_mfa_for_super_admin'))
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ThrottleAdminLogin::class,
                AuthenticateSession::class,
                AdminSessionTimeout::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
