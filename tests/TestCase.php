<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Filament\Auth\Login;
use App\Http\Middleware\ThrottleAdminLogin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $this->forceTestingDatabaseEnv();

        $app = parent::createApplication();

        // Cached config from `php artisan config:cache` ignores phpunit.xml env values.
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        $app['config']->set('database.connections.sqlite.busy_timeout', 30000);

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') === 'sqlite') {
            DB::connection()->getPdo()->exec('PRAGMA busy_timeout = 30000');
        }
    }

    /**
     * @param  \Livewire\Features\SupportTesting\Testable  $component
     * @return \Livewire\Features\SupportTesting\Testable
     */
    protected function fillAdminLoginForm($component, string $email, string $password)
    {
        return $component
            ->set('data.email', $email)
            ->set('data.password', $password);
    }

    protected function clearAdminLoginRateLimiters(?string $email = null): void
    {
        RateLimiter::clear(ThrottleAdminLogin::key(request(), $email));
        RateLimiter::clear('livewire-rate-limiter:'.sha1(Login::class.'|authenticate|'.request()->ip()));
    }

    protected function clearPublicLivewireRateLimiter(): void
    {
        RateLimiter::clear('livewire-form:'.request()->ip());
    }

    private function forceTestingDatabaseEnv(): void
    {
        foreach ([
            'APP_ENV' => 'testing',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
            'DB_URL' => '',
            'DB_BUSY_TIMEOUT' => '30000',
            'CACHE_STORE' => 'array',
            'SESSION_DRIVER' => 'array',
        ] as $key => $value) {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}
