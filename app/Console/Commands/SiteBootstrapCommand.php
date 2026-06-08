<?php

namespace App\Console\Commands;

use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Console\Command;

class SiteBootstrapCommand extends Command
{
    protected $signature = 'site:bootstrap
                            {--force : Run without confirmation in production}';

    protected $description = 'First-time install: migrate reference pages, menus, settings, and demo content';

    public function handle(): int
    {
        if ($this->laravel->environment('production') && ! $this->option('force') && ! $this->confirm('Bootstrap reference data on production?')) {
            $this->components->warn('Aborted.');

            return self::FAILURE;
        }

        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);

        $this->components->info('Bootstrapping reference data (SEED_MODE=bootstrap)...');

        $this->call('migrate', ['--force' => true]);

        (new ReferenceDataSeeder)->setCommand($this)->run();

        $this->components->success('Site bootstrap complete.');

        return self::SUCCESS;
    }
}
