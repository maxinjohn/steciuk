<?php

namespace App\Console\Commands;

use App\Services\SqliteHealth;
use Illuminate\Console\Command;

class RepairSqliteCommand extends Command
{
    protected $signature = 'db:repair-sqlite
                            {--force : Rebuild without confirmation}
                            {--skip-bootstrap : Run migrations only, without reference seed data}';

    protected $description = 'Quarantine a corrupt SQLite file, recreate it, migrate, and bootstrap reference data';

    public function handle(): int
    {
        if (config('database.default') !== 'sqlite') {
            $this->warn('This command only applies to SQLite connections.');

            return self::SUCCESS;
        }

        $path = SqliteHealth::databasePath();

        if ($path === null) {
            $this->components->error('SQLite database path is not configured.');

            return self::FAILURE;
        }

        $healthy = SqliteHealth::integrityOk($path);

        if ($healthy) {
            $this->components->info('SQLite integrity check passed: '.$path);

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('SQLite is corrupt or unreadable. Rebuild reference data now?')) {
            $this->components->warn('Aborted.');

            return self::FAILURE;
        }

        try {
            $repaired = SqliteHealth::repair(forceBootstrap: ! $this->option('skip-bootstrap'));
        } catch (\Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->success('SQLite repaired: '.$repaired);
        $this->line('Previous database copies are kept in storage/database/backups/');

        return self::SUCCESS;
    }
}
