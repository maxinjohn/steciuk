<?php

namespace App\Console\Commands;

use App\Support\SiteBrandingAssets;
use App\Support\SitePaths;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SiteEnsurePathsCommand extends Command
{
    protected $signature = 'site:ensure-paths {--link : Create the public/storage symlink if missing}';

    protected $description = 'Create configured storage, upload, database, and SQLite paths for production';

    public function handle(): int
    {
        @unlink(storage_path('framework/.site-paths-verified'));

        SitePaths::ensureConfiguredDataPaths();
        SitePaths::ensureSqliteDatabaseFile();
        SiteBrandingAssets::syncDefaultLogoSetting();

        $linked = SitePaths::ensurePublicStorageLink();

        if ($this->option('link') && ! $linked) {
            Artisan::call('storage:link');
            $linked = SitePaths::ensurePublicStorageLink();
        }

        $paths = array_filter([
            'Storage' => SitePaths::configuredPath('storage') ?? storage_path(),
            'Public uploads' => SitePaths::configuredPath('public_uploads') ?? storage_path('app/public'),
            'Private uploads' => SitePaths::configuredPath('private_uploads') ?? storage_path('app/private'),
            'SQLite database' => config('database.connections.sqlite.database'),
        ]);

        $this->components->info('Site data paths are ready:');

        foreach ($paths as $label => $path) {
            $this->line("  {$label}: {$path}");
        }

        $this->newLine();
        $this->line('Public storage link: '.($linked ? 'ok' : 'missing — run php artisan site:ensure-paths --link'));

        return self::SUCCESS;
    }
}
