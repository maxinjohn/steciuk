<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

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
