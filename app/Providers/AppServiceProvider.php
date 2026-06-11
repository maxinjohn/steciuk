<?php

namespace App\Providers;

use App\Filament\Auth\Login;
use App\Http\Middleware\ThrottleAdminLogin;
use App\Listeners\FilamentAdminAuditListener;
use App\Models\User;
use App\Services\MailConfigService;
use App\Services\SecurityLogger;
use App\Services\SqliteOptimizer;
use App\Support\SitePaths;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Resources\Events\RecordCreated;
use Filament\Resources\Events\RecordUpdated;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->applyCustomDataPaths();

        if ($storagePath = SitePaths::configuredPath('storage')) {
            $this->app->useStoragePath($storagePath);
        }
    }

    public function boot(): void
    {
        $this->configureTrustedProxies();

        SitePaths::ensureConfiguredDataPaths();
        SitePaths::ensureSqliteDatabaseFile();
        SitePaths::ensurePublicStorageLink();

        SqliteOptimizer::configureConnection();

        MailConfigService::applyFromSettings();

        Password::defaults(function () {
            return Password::min(12)
                ->mixedCase()
                ->numbers()
                ->symbols();
        });

        ResetPassword::createUrlUsing(function (object $notifiable, string $token): string {
            return route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        });

        Gate::before(function ($user, $ability, $arguments) {
            if (! $user instanceof User || ! $user->hasFullPanelAccess()) {
                return null;
            }

            if ($user->isSuperAdmin()) {
                return true;
            }

            $target = collect($arguments)->first(fn ($argument) => $argument instanceof User);

            if ($target instanceof User && $target->isSuperAdmin()) {
                return match ($ability) {
                    'delete', 'forceDelete', 'update', 'restore' => false,
                    default => true,
                };
            }

            return true;
        });

        Event::listen(
            Attempting::class,
            function (Attempting $event): void {
                $email = Login::normalizeEmail($event->credentials['email'] ?? '');

                if (! ThrottleAdminLogin::isLocked(request(), $email !== '' ? $email : null)) {
                    return;
                }

                throw ValidationException::withMessages([
                    'data.email' => ThrottleAdminLogin::lockoutMessage(
                        ThrottleAdminLogin::secondsUntilUnlocked(request(), $email !== '' ? $email : null),
                    ),
                ]);
            }
        );

        Event::listen(
            \Illuminate\Auth\Events\Login::class,
            function (\Illuminate\Auth\Events\Login $event): void {
                if ($event->user) {
                    ThrottleAdminLogin::clear(
                        request(),
                        Login::normalizeEmail($event->user->email ?? ''),
                    );
                    session(['admin_last_activity' => time()]);
                    SecurityLogger::audit('user_login', actor: $event->user, context: [
                        'portal' => SecurityLogger::adminPortalLabel(),
                    ]);
                }
            }
        );

        Event::listen(
            Failed::class,
            function (Failed $event): void {
                $email = Login::normalizeEmail($event->credentials['email'] ?? '');

                if (
                    $event->user instanceof FilamentUser
                    && Hash::check($event->credentials['password'] ?? '', $event->user->password)
                    && ! $event->user->canAccessPanel(Filament::getCurrentOrDefaultPanel())
                ) {
                    request()->attributes->set('admin_login_panel_denied', true);

                    SecurityLogger::audit('login_panel_denied', 'warning', actor: $event->user instanceof User ? $event->user : null, context: [
                        'email' => $email !== '' ? $email : 'unknown',
                        'target_user_id' => $event->user->id ?? null,
                        'portal' => SecurityLogger::adminPortalLabel(),
                        'ip' => request()->ip(),
                    ]);

                    return;
                }

                if (request()->attributes->get('admin_login_panel_denied')) {
                    return;
                }

                ThrottleAdminLogin::recordFailure(request(), $email !== '' ? $email : null);

                if ($email !== '' && ThrottleAdminLogin::isLocked(request(), $email)) {
                    session()->flash('admin_login_locked', [
                        'seconds' => ThrottleAdminLogin::secondsUntilUnlocked(request(), $email),
                        'attempts' => ThrottleAdminLogin::maxAttempts(),
                    ]);
                }

                SecurityLogger::warning('login_failed', null, [
                    'email' => $email !== '' ? $email : 'unknown',
                    'portal' => SecurityLogger::detectPortal(),
                    'ip' => request()->ip(),
                ]);
            }
        );

        Event::listen(
            Logout::class,
            function (Logout $event): void {
                session()->forget('admin_last_activity');

                if ($event->user) {
                    SecurityLogger::audit('user_logout', actor: $event->user, context: [
                        'portal' => SecurityLogger::detectPortal(),
                    ]);
                }
            }
        );

        Paginator::defaultView('vendor.pagination.heavenly');
        Paginator::defaultSimpleView('vendor.pagination.heavenly');

        Event::listen(
            RecordCreated::class,
            [FilamentAdminAuditListener::class, 'recordCreated'],
        );

        Event::listen(
            RecordUpdated::class,
            [FilamentAdminAuditListener::class, 'recordUpdated'],
        );
    }

    private function applyCustomDataPaths(): void
    {
        if ($database = SitePaths::configuredPath('database')) {
            config(['database.connections.sqlite.database' => $database]);
        }

        if ($private = SitePaths::configuredPath('private_uploads')) {
            config(['filesystems.disks.local.root' => $private]);
        }

        if ($public = SitePaths::configuredPath('public_uploads')) {
            config([
                'filesystems.disks.public.root' => $public,
                'filesystems.links' => [
                    public_path('storage') => $public,
                ],
            ]);
        }
    }

    private function configureTrustedProxies(): void
    {
        $trustedProxies = config('security.trusted_proxies');

        if (! filled($trustedProxies)) {
            return;
        }

        $proxies = trim((string) $trustedProxies) === '*'
            ? '*'
            : array_map('trim', explode(',', (string) $trustedProxies));

        Request::setTrustedProxies(
            $proxies,
            Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );
    }
}
