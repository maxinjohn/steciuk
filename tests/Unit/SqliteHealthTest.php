<?php

namespace Tests\Unit;

use App\Services\SqliteHealth;
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
        $this->assertTrue(SqliteHealth::integrityOk($repaired));
        $this->assertNotEmpty(glob(dirname($database).'/backups/database-corrupt-*.sqlite'));

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
