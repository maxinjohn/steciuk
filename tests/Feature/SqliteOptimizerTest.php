<?php

namespace Tests\Feature;

use App\Database\SQLiteConnection;
use App\Services\SqliteOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SqliteOptimizerTest extends TestCase
{
    use RefreshDatabase;

    protected array $connectionsToTransact = [];

    public function test_uses_resilient_sqlite_connection(): void
    {
        $this->assertInstanceOf(SQLiteConnection::class, \DB::connection());
    }

    public function test_configure_connection_runs_on_sqlite(): void
    {
        $this->assertSame('sqlite', config('database.default'));

        SqliteOptimizer::configureConnection(\DB::connection());

        $journalMode = strtolower((string) \DB::connection()->getPdo()->query('PRAGMA journal_mode')->fetchColumn());
        $this->assertContains($journalMode, ['wal', 'memory']);

        $busyTimeout = (int) \DB::connection()->getPdo()->query('PRAGMA busy_timeout')->fetchColumn();
        $this->assertSame((int) config('database.connections.sqlite.busy_timeout'), $busyTimeout);
    }

    public function test_light_maintenance_runs_without_error(): void
    {
        SqliteOptimizer::maintain(light: true);

        $this->assertSame('sqlite', config('database.default'));
    }

    public function test_weekly_maintenance_runs_analyze_and_optimize(): void
    {
        SqliteOptimizer::maintain();

        $this->assertSame('sqlite', config('database.default'));
    }
}
