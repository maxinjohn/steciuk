<?php

namespace App\Console\Commands;

use App\Services\SqliteOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OptimizeSqliteCommand extends Command
{
    protected $signature = 'db:optimize-sqlite';

    protected $description = 'Apply SQLite performance pragmas and run ANALYZE';

    public function handle(): int
    {
        if (config('database.default') !== 'sqlite') {
            $this->warn('This command only applies to SQLite connections.');

            return self::SUCCESS;
        }

        SqliteOptimizer::configureConnection();
        SqliteOptimizer::analyze();

        DB::connection()->getPdo()->exec('PRAGMA optimize');

        $this->info('SQLite optimized (WAL, ANALYZE, PRAGMA optimize).');

        return self::SUCCESS;
    }
}
