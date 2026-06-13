<?php

namespace App\Providers;

use App\Http\Controllers\PublicUploadController;
use App\Database\SQLiteConnection;
use App\Database\SQLiteConnector;
use App\Filament\Auth\Login;
use App\Http\Middleware\ThrottleAdminLogin;
use App\Listeners\FilamentAdminAuditListener;
use App\Listeners\SyncReferenceDataAfterMigration;
use App\Models\User;
use App\Services\MailConfigService;
use App\Services\SecurityLogger;
use App\Services\SqliteHealth;
use App\Services\SqliteOptimizer;
use App\Support\SitePaths;
use App\Support\SiteUrl;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Resources\Events\RecordCreated;
use Filament\Resources\Events\RecordUpdated;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Events\NoPendingMigrations;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->configureTestingDatabase();

        $this->app->bind('db.connector.sqlite', SQLiteConnector::class);

        $this->applyCustomDataPaths();
        $this->normalizeSqliteDatabasePath();
        $this->capSqliteBusyTimeout();

        if ($storagePath = SitePaths::configuredPath('storage')) {
            $this->app->useStoragePath($storagePath);
        }
    }

    public function boot(): void
    {
        SiteUrl::configureRootUrl();

        Connection::resolverFor('sqlite', function ($connection, $database, $prefix, $config) {
            return new SQLiteConnection($connection, $database, $prefix, $config);
        });

        $this->configureTrustedProxies();

        if ($this->shouldBootstrapDatabase()) {
            SitePaths::ensureConfiguredDataPaths();
            SqliteHealth::ensureReady();
            SitePaths::ensurePublicStorageLink();
        }

        $this->registerPublicUploadRoute();

        Event::listen(ConnectionEstablished::class, function (ConnectionEstablished $event): void {
            if ($event->connection->getDriverName() === 'sqlite') {
                SqliteOptimizer::configureConnection($event->connection);
            }
        });

        Event::listen(MigrationsEnded::class, SyncReferenceDataAfterMigration::class);
        Event::listen(NoPendingMigrations::class, SyncReferenceDataAfterMigration::class);

        if ($this->shouldBootstrapDatabase()) {
            MailConfigService::applyFromSettings();
        }

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

    private function shouldBootstrapDatabase(): bool
    {
        if ($this->app->environment('testing')) {
            return false;
        }

        if (! $this->app->runningInConsole()) {
            return true;
        }

        $command = $_SERVER['argv'][1] ?? '';

        return in_array($command, [
            'serve',
            'queue:work',
            'schedule:work',
            'schedule:run',
            'migrate',
            'db:optimize-sqlite',
            'db:repair-sqlite',
            'site:ensure-paths',
            'site:bootstrap',
        ], true);
    }

    private function configureTestingDatabase(): void
    {
        $env = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? getenv('APP_ENV');

        if ($env !== 'testing') {
            return;
        }

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.busy_timeout' => 30000,
        ]);
    }

    private function capSqliteBusyTimeout(): void
    {
        if (config('database.default') !== 'sqlite') {
            return;
        }

        $configured = (int) config('database.connections.sqlite.busy_timeout', 10000);
        $maxExecution = (int) ini_get('max_execution_time');

        if ($maxExecution > 0) {
            $cap = max(1000, ($maxExecution - 5) * 1000);
            $configured = min($configured, $cap);
        } else {
            $configured = min($configured, 10000);
        }

        config(['database.connections.sqlite.busy_timeout' => $configured]);
    }

    private function normalizeSqliteDatabasePath(): void
    {
        if (config('database.default') !== 'sqlite') {
            return;
        }

        $database = config('database.connections.sqlite.database');

        if (! is_string($database) || $database === '' || $database === ':memory:') {
            return;
        }

        config([
            'database.connections.sqlite.database' => SitePaths::resolve($database) ?? $database,
        ]);
    }

    private function applyCustomDataPaths(): void
    {
        if ($this->app->environment('testing')) {
            return;
        }

        if ($database = SitePaths::configuredPath('database')) {
            config(['database.connections.sqlite.database' => $database]);
        }

        if ($private = SitePaths::configuredPath('private_uploads')) {
            config(['filesystems.disks.local.root' => $private]);
        }

        SitePaths::syncPublicDiskConfig();
    }

    private function registerPublicUploadRoute(): void
    {
        $prefix = trim(SitePaths::publicStorageBaseUrl(), '/');

        if ($prefix === '') {
            return;
        }

        Route::get($prefix.'/{path}', PublicUploadController::class)
            ->where('path', '.*')
            ->name('site.public-uploads');
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
