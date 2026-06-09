<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use PDO;

class SqliteOptimizer
{
    public static function configureConnection(): void
    {
        if (config('database.default') !== 'sqlite') {
            return;
        }

        $pdo = static::pdo();

        if ($pdo === null) {
            return;
        }

        $busyTimeout = (int) config('database.connections.sqlite.busy_timeout', 5000);
        $journalMode = (string) config('database.connections.sqlite.journal_mode', 'wal');
        $synchronous = (string) config('database.connections.sqlite.synchronous', 'normal');

        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA journal_mode = '.static::quotePragmaValue($journalMode));
        $pdo->exec('PRAGMA synchronous = '.static::quotePragmaValue($synchronous));
        $pdo->exec('PRAGMA busy_timeout = '.$busyTimeout);
        $pdo->exec('PRAGMA cache_size = -64000');
        $pdo->exec('PRAGMA temp_store = MEMORY');
        $pdo->exec('PRAGMA mmap_size = 268435456');
        $pdo->exec('PRAGMA automatic_index = ON');
    }

    public static function maintain(bool $light = false, bool $reclaim = false): void
    {
        if (config('database.default') !== 'sqlite') {
            return;
        }

        $pdo = static::pdo();

        if ($pdo === null) {
            return;
        }

        static::configureConnection();

        if ($reclaim) {
            static::enableIncrementalAutoVacuum($pdo, true);
            $pdo->exec('PRAGMA wal_checkpoint(TRUNCATE)');
            $pdo->exec('VACUUM');
        } else {
            static::enableIncrementalAutoVacuum($pdo, false);
            $pdo->exec('PRAGMA wal_checkpoint(PASSIVE)');
            $pages = $light ? 100 : 400;
            $pdo->exec('PRAGMA incremental_vacuum('.$pages.')');
        }

        if (! $light) {
            static::analyze();
            $pdo->exec('PRAGMA optimize');
        }
    }

    public static function analyze(): void
    {
        if (config('database.default') !== 'sqlite') {
            return;
        }

        static::pdo()?->exec('ANALYZE');
    }

    public static function autoVacuumMode(): ?int
    {
        $pdo = static::pdo();

        if ($pdo === null) {
            return null;
        }

        return (int) $pdo->query('PRAGMA auto_vacuum')->fetchColumn();
    }

    private static function enableIncrementalAutoVacuum(PDO $pdo, bool $runVacuum): void
    {
        if ((int) $pdo->query('PRAGMA auto_vacuum')->fetchColumn() === 2) {
            return;
        }

        $pdo->exec('PRAGMA auto_vacuum = INCREMENTAL');

        if ($runVacuum) {
            $pdo->exec('VACUUM');
        }
    }

    private static function pdo(): ?PDO
    {
        try {
            return DB::connection()->getPdo();
        } catch (\Throwable) {
            return null;
        }
    }

    private static function quotePragmaValue(string $value): string
    {
        $normalized = strtolower(trim($value));

        return match ($normalized) {
            'wal', 'delete', 'truncate', 'persist', 'memory', 'normal', 'full', 'extra', 'off' => strtoupper($normalized),
            default => "'".str_replace("'", "''", $value)."'",
        };
    }
}
