<?php

namespace App\Providers;

use App\Services\SqliteOptimizer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($storagePath = env('APP_STORAGE_PATH')) {
            $this->app->useStoragePath($storagePath);
        }
    }

    public function boot(): void
    {
        SqliteOptimizer::configureConnection();

        Password::defaults(function () {
            return Password::min(12)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised();
        });

        Gate::before(function ($user, $ability) {
            return $user?->isSuperAdmin() ? true : null;
        });

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            function (\Illuminate\Auth\Events\Login $event): void {
                if ($event->user) {
                    \App\Services\SecurityLogger::info('user_login', $event->user->id);
                }
            }
        );

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Failed::class,
            function (\Illuminate\Auth\Events\Failed $event): void {
                \App\Services\SecurityLogger::warning('login_failed', null, [
                    'email' => $event->credentials['email'] ?? 'unknown',
                ]);
            }
        );
    }
}
