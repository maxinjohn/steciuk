<?php

namespace App\Console\Commands;

use App\Services\SqliteOptimizer;
use Illuminate\Console\Command;

class OptimizeSqliteCommand extends Command
{
    protected $signature = 'db:optimize-sqlite
                            {--light : WAL checkpoint and incremental vacuum only}
                            {--reclaim : Enable incremental auto-vacuum and run VACUUM}';

    protected $description = 'Maintain SQLite performance (WAL checkpoint, vacuum, ANALYZE, optimize)';

    public function handle(): int
    {
        if (config('database.default') !== 'sqlite') {
            $this->warn('This command only applies to SQLite connections.');

            return self::SUCCESS;
        }

        $light = (bool) $this->option('light');
        $reclaim = (bool) $this->option('reclaim');

        SqliteOptimizer::maintain($light, $reclaim);

        $mode = SqliteOptimizer::autoVacuumMode();
        $modeLabel = match ($mode) {
            2 => 'incremental',
            1 => 'full',
            0 => 'none',
            default => 'unknown',
        };

        if ($reclaim) {
            $this->info("SQLite reclaimed (VACUUM, WAL truncate, auto_vacuum={$modeLabel}).");
        } elseif ($light) {
            $this->info('SQLite light maintenance complete (WAL checkpoint, incremental vacuum).');
        } else {
            $this->info("SQLite optimized (ANALYZE, PRAGMA optimize, auto_vacuum={$modeLabel}).");
        }

        return self::SUCCESS;
    }
}
