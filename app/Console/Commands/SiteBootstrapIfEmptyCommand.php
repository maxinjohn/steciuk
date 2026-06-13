<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;

class SiteBootstrapIfEmptyCommand extends Command
{
    protected $signature = 'site:bootstrap-if-empty
                            {--force : Run without confirmation in production}';

    protected $description = 'Run migrate when the database has no pages (legacy helper; migrate already syncs reference data)';

    public function handle(): int
    {
        if (Page::query()->exists()) {
            $this->components->info('Reference data already present — bootstrap skipped.');

            return self::SUCCESS;
        }

        $this->components->warn('No pages found — running migrate to provision reference data.');

        return $this->call('migrate', [
            '--force' => $this->option('force'),
        ]);
    }
}
