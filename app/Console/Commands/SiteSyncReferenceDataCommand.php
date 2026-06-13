<?php

namespace App\Console\Commands;

use App\Database\ReferenceDataMigrator;
use App\Support\SeedConfig;
use Illuminate\Console\Command;

class SiteSyncReferenceDataCommand extends Command
{
    protected $signature = 'site:sync-reference-data
                            {--force : Run without confirmation in production}';

    protected $description = 'Legacy manual re-sync. Prefer php artisan migrate, which upserts reference data automatically.';

    public function handle(): int
    {
        if ($this->laravel->environment('production') && ! $this->option('force') && ! $this->confirm('Sync reference data on production? Prod-only records will be kept.')) {
            $this->components->warn('Aborted.');

            return self::FAILURE;
        }

        config(['site.seed.mode' => SeedConfig::MODE_SYNC]);

        $this->components->info('Syncing reference data (SEED_MODE=sync)...');
        $this->line('Prefer php artisan migrate on deploy. This command preserves prod-only records and custom settings.');

        ReferenceDataMigrator::sync();

        $this->components->success('Reference data sync complete.');

        return self::SUCCESS;
    }
}
