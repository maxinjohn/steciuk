<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;

class SiteBootstrapIfEmptyCommand extends Command
{
    protected $signature = 'site:bootstrap-if-empty
                            {--force : Run without confirmation in production}';

    protected $description = 'Bootstrap reference data when the database has no pages (first deploy)';

    public function handle(): int
    {
        if (Page::query()->exists()) {
            $this->components->info('Reference data already present — bootstrap skipped.');

            return self::SUCCESS;
        }

        $this->components->warn('No pages found — running first-time bootstrap.');

        return $this->call('site:bootstrap', [
            '--force' => $this->option('force'),
        ]);
    }
}
