<?php

namespace App\Providers;

use App\Http\Middleware\ThrottleAdminLogin;
use App\Services\MailConfigService;
use App\Services\SqliteOptimizer;
use App\Support\SitePaths;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($storagePath = SitePaths::resolve(env('APP_STORAGE_PATH'))) {
            $this->app->useStoragePath($storagePath);
        }
    }

    public function boot(): void
    {
        $this->applyCustomDataPaths();

        SqliteOptimizer::configureConnection();

        MailConfigService::applyFromSettings();

        Password::defaults(function () {
            return Password::min(12)
                ->mixedCase()
                ->numbers()
                ->symbols();
        });

        Gate::before(function ($user, $ability) {
            return $user?->isSuperAdmin() ? true : null;
        });

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Attempting::class,
            function (\Illuminate\Auth\Events\Attempting $event): void {
                $email = \App\Filament\Auth\Login::normalizeEmail($event->credentials['email'] ?? '');

                if (! ThrottleAdminLogin::isLocked(request(), $email !== '' ? $email : null)) {
                    return;
                }

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'data.email' => ThrottleAdminLogin::lockoutMessage(
                        ThrottleAdminLogin::secondsUntilUnlocked(request(), $email !== '' ? $email : null),
                    ),
                ]);
            }
        );

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            function (\Illuminate\Auth\Events\Login $event): void {
                if ($event->user) {
                    ThrottleAdminLogin::clear(
                        request(),
                        \App\Filament\Auth\Login::normalizeEmail($event->user->email ?? ''),
                    );
                    session(['admin_last_activity' => time()]);
                    \App\Services\SecurityLogger::info('user_login', $event->user->id);
                }
            }
        );

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Failed::class,
            function (\Illuminate\Auth\Events\Failed $event): void {
                $email = \App\Filament\Auth\Login::normalizeEmail($event->credentials['email'] ?? '');

                if (
                    $event->user instanceof \Filament\Models\Contracts\FilamentUser
                    && \Illuminate\Support\Facades\Hash::check($event->credentials['password'] ?? '', $event->user->password)
                    && ! $event->user->canAccessPanel(\Filament\Facades\Filament::getCurrentOrDefaultPanel())
                ) {
                    request()->attributes->set('admin_login_panel_denied', true);

                    \App\Services\SecurityLogger::warning('login_panel_denied', $event->user->id ?? null, [
                        'email' => $email !== '' ? $email : 'unknown',
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

                \App\Services\SecurityLogger::warning('login_failed', null, [
                    'email' => $email !== '' ? $email : 'unknown',
                    'ip' => request()->ip(),
                ]);
            }
        );

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Logout::class,
            function (\Illuminate\Auth\Events\Logout $event): void {
                session()->forget('admin_last_activity');

                if ($event->user) {
                    \App\Services\SecurityLogger::info('user_logout', $event->user->id);
                }
            }
        );
    }

    private function applyCustomDataPaths(): void
    {
        if ($database = SitePaths::resolve(env('DB_DATABASE'))) {
            config(['database.connections.sqlite.database' => $database]);
        }

        if ($private = SitePaths::resolve(env('PRIVATE_STORAGE_PATH'))) {
            config(['filesystems.disks.local.root' => $private]);
        }

        if ($public = SitePaths::resolve(env('PUBLIC_STORAGE_PATH'))) {
            config([
                'filesystems.disks.public.root' => $public,
                'filesystems.links' => [
                    public_path('storage') => $public,
                ],
            ]);
        }
    }
}
