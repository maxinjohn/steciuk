<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SqliteOptimizer
{
    public static function configureConnection(): void
    {
        if (config('database.default') !== 'sqlite') {
            return;
        }

        $pdo = DB::connection()->getPdo();

        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA synchronous = NORMAL');
        $pdo->exec('PRAGMA busy_timeout = 5000');
        $pdo->exec('PRAGMA cache_size = -64000');
        $pdo->exec('PRAGMA temp_store = MEMORY');
        $pdo->exec('PRAGMA mmap_size = 268435456');
    }

    public static function analyze(): void
    {
        if (config('database.default') !== 'sqlite') {
            return;
        }

        DB::connection()->getPdo()->exec('ANALYZE');
    }
}
