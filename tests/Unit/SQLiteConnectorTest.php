<?php

namespace Tests\Unit;

use App\Database\SQLiteConnector;
use App\Support\SitePaths;
use PDO;
use Tests\TestCase;

class SQLiteConnectorTest extends TestCase
{
    public function test_skips_journal_mode_when_already_configured(): void
    {
        $database = storage_path('framework/testing/sqlite-connector-'.bin2hex(random_bytes(4)).'.sqlite');
        SitePaths::ensureParentDirectoryForFile($database);
        touch($database);

        $initial = new PDO('sqlite:'.$database);
        $initial->exec('PRAGMA journal_mode = wal');
        $initial = null;

        $connector = new SQLiteConnector;
        $connection = $connector->connect([
            'driver' => 'sqlite',
            'database' => $database,
            'foreign_key_constraints' => true,
            'busy_timeout' => 5000,
            'journal_mode' => 'wal',
            'synchronous' => 'normal',
        ]);

        $this->assertSame('wal', strtolower((string) $connection->query('PRAGMA journal_mode')->fetchColumn()));

        @unlink($database);
        @unlink($database.'-wal');
        @unlink($database.'-shm');
    }
}
