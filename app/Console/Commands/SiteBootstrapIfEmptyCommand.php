<?php

namespace App\Console\Commands;

use App\Database\ReferenceDataMigrator;
use Illuminate\Console\Command;

class SiteBootstrapIfEmptyCommand extends Command
{
    protected $signature = 'site:bootstrap-if-empty
                            {--force : Run without confirmation in production}';

    protected $description = 'Run migrate when the database has no pages (legacy helper; migrate already syncs reference data)';

    public function handle(): int
    {
        if (! ReferenceDataMigrator::needsSync()) {
            $this->components->info('Reference data already present — bootstrap skipped.');

            return self::SUCCESS;
        }

        $this->components->warn('Reference data is incomplete — running migrate to sync missing records.');

        return $this->call('migrate', [
            '--force' => $this->option('force'),
        ]);
    }
}
