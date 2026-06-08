<?php

namespace App\Console\Commands;

use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Console\Command;

class SiteSyncReferenceDataCommand extends Command
{
    protected $signature = 'site:sync-reference-data
                            {--force : Run without confirmation in production}';

    protected $description = 'Sync dev reference data to prod: upsert seeded records, preserve prod-only data';

    public function handle(): int
    {
        if ($this->laravel->environment('production') && ! $this->option('force') && ! $this->confirm('Sync reference data on production? Prod-only records will be kept.')) {
            $this->components->warn('Aborted.');

            return self::FAILURE;
        }

        config(['site.seed.mode' => SeedConfig::MODE_SYNC]);

        $this->components->info('Syncing reference data (SEED_MODE=sync)...');
        $this->components->comment('Preserves: prod-only pages/menus, admin passwords, and settings (unless SEED_OVERWRITE_* is true).');

        (new ReferenceDataSeeder)->setCommand($this)->run();

        $this->components->success('Reference data sync complete.');

        return self::SUCCESS;
    }
}
