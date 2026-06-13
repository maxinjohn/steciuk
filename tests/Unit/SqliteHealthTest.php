<?php

namespace Tests\Unit;

use App\Services\SqliteHealth;
use App\Services\SqliteOptimizer;
use App\Support\SitePaths;
use Tests\TestCase;

class SqliteHealthTest extends TestCase
{
    public function test_repair_rebuilds_corrupt_database(): void
    {
        $database = storage_path('framework/testing/sqlite-health-'.bin2hex(random_bytes(4)).'.sqlite');
        config(['database.connections.sqlite.database' => $database]);

        SitePaths::ensureParentDirectoryForFile($database);
        file_put_contents($database, 'not-a-sqlite-file');

        $repaired = SqliteHealth::repair(forceBootstrap: false);

        $this->assertSame(realpath($database), realpath($repaired));
        $this->assertTrue(SqliteHealth::isHealthy($repaired));
        $this->assertNotEmpty(glob(dirname($database).'/backups/database-corrupt-*.sqlite'));

        @unlink($database);
        SqliteHealth::removeSidecarFiles($database);
    }

    public function test_migrate_if_needed_builds_schema_without_wiping_integrity_ok_file(): void
    {
        $database = storage_path('framework/testing/sqlite-migrate-'.bin2hex(random_bytes(4)).'.sqlite');
        config(['database.connections.sqlite.database' => $database]);

        SitePaths::ensureParentDirectoryForFile($database);
        touch($database);
        SqliteOptimizer::initializeNewDatabase($database);

        $this->assertTrue(SqliteHealth::integrityOk($database));
        $this->assertFalse(SqliteHealth::schemaReady($database));

        SqliteHealth::migrateIfNeeded();

        $this->assertTrue(SqliteHealth::schemaReady($database));

        @unlink($database);
        SqliteHealth::removeSidecarFiles($database);
    }

    public function test_database_path_is_resolved_to_absolute_path(): void
    {
        config(['database.connections.sqlite.database' => 'storage/database/database.sqlite']);

        $path = SqliteHealth::databasePath();

        $this->assertNotNull($path);
        $this->assertTrue(SitePaths::isAbsolute($path));
        $this->assertSame(base_path('storage/database/database.sqlite'), $path);
    }
}
