<?php

namespace App\Console\Commands;

use App\Support\SitePaths;
use Illuminate\Console\Command;

class SiteEnsurePathsCommand extends Command
{
    protected $signature = 'site:ensure-paths';

    protected $description = 'Create configured storage, upload, and database directories';

    public function handle(): int
    {
        SitePaths::ensureConfiguredDataPaths();

        $paths = array_filter([
            'Storage' => SitePaths::resolve(env('APP_STORAGE_PATH')) ?? storage_path(),
            'Public uploads' => SitePaths::resolve(env('PUBLIC_STORAGE_PATH')) ?? storage_path('app/public'),
            'Private uploads' => SitePaths::resolve(env('PRIVATE_STORAGE_PATH')) ?? storage_path('app/private'),
            'Database directory' => SitePaths::resolve(env('DB_DATABASE'))
                ? dirname((string) SitePaths::resolve(env('DB_DATABASE')))
                : null,
        ]);

        $this->components->info('Site data directories are ready:');

        foreach ($paths as $label => $path) {
            $this->line("  {$label}: {$path}");
        }

        $this->newLine();
        $this->comment('If uploads still fail, run: php artisan storage:link');

        return self::SUCCESS;
    }
}
