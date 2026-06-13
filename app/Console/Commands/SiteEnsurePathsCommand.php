<?php

namespace App\Console\Commands;

use App\Support\SiteBrandingAssets;
use App\Support\SitePaths;
use Illuminate\Console\Command;

class SiteEnsurePathsCommand extends Command
{
    protected $signature = 'site:ensure-paths {--link : Recreate the public/storage symlink when missing or wrong}';

    protected $description = 'Create configured storage, upload, database, and SQLite paths for production';

    public function handle(): int
    {
        @unlink(storage_path('framework/.site-paths-verified'));

        SitePaths::ensureConfiguredDataPaths();
        SitePaths::ensureSqliteDatabaseFile();
        SiteBrandingAssets::syncDefaultLogoSetting();

        $before = SitePaths::publicStorageLinkDetail();

        if ($this->option('link') || ! $before['ok']) {
            SitePaths::ensurePublicStorageLink();
        }

        $after = SitePaths::publicStorageLinkDetail();

        $paths = array_filter([
            'Storage' => SitePaths::configuredPath('storage') ?? storage_path(),
            'Public uploads' => SitePaths::publicUploadsRoot(),
            'Private uploads' => SitePaths::configuredPath('private_uploads') ?? storage_path('app/private'),
            'SQLite database' => config('database.connections.sqlite.database'),
        ]);

        $this->components->info('Site data paths are ready:');

        foreach ($paths as $label => $path) {
            $this->line("  {$label}: {$path}");
        }

        $this->newLine();
        $this->line('Public storage link: '.($after['ok'] ? 'ok' : 'needs attention'));
        $this->line('  Expected: '.$after['expected']);

        if ($before['current'] !== null) {
            $this->line('  Previous: '.$before['current']);
        }

        $this->line('  Current: '.($after['current'] ?? 'missing'));

        if (! $after['ok']) {
            $this->components->warn('Symlink still wrong — run: rm -f public/storage && php artisan site:ensure-paths --link');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
