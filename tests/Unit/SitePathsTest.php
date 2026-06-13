<?php

namespace Tests\Unit;

use App\Support\SitePaths;
use Tests\TestCase;

class SitePathsTest extends TestCase
{
    public function test_relative_path_resolves_from_project_root(): void
    {
        $resolved = SitePaths::resolve('../site_data/database/database.sqlite');

        $this->assertSame(
            base_path('../site_data/database/database.sqlite'),
            $resolved,
        );
    }

    public function test_absolute_path_is_unchanged(): void
    {
        $path = '/var/lib/steciuk/database/database.sqlite';

        $this->assertSame($path, SitePaths::resolve($path));
    }

    public function test_configured_path_reads_from_site_config(): void
    {
        config(['site.paths.public_uploads' => '../site_data/testing/config-only']);

        $this->assertSame(
            base_path('../site_data/testing/config-only'),
            SitePaths::configuredPath('public_uploads'),
        );
    }

    public function test_ensure_directory_exists_creates_nested_path(): void
    {
        $path = storage_path('framework/testing/site-paths-'.bin2hex(random_bytes(4)));

        $this->assertDirectoryDoesNotExist($path);

        SitePaths::ensureDirectoryExists($path);

        $this->assertDirectoryExists($path);

        rmdir($path);
    }

    public function test_ensure_parent_directory_for_file_creates_database_folder(): void
    {
        $file = base_path('../site_data/testing/db-'.bin2hex(random_bytes(4)).'/database.sqlite');
        $directory = dirname($file);

        $this->assertDirectoryDoesNotExist($directory);

        SitePaths::ensureParentDirectoryForFile($file);

        $this->assertDirectoryExists($directory);

        rmdir($directory);
        @rmdir(dirname($directory));
    }

    public function test_ensure_configured_data_paths_creates_external_public_upload_directory(): void
    {
        @unlink(storage_path('framework/.site-paths-verified'));

        $relative = '../site_data/testing/auto-uploads-'.bin2hex(random_bytes(4));
        $absolute = base_path($relative);

        $this->assertDirectoryDoesNotExist($absolute);

        config([
            'site.paths.public_uploads' => $relative,
            'filesystems.disks.public.root' => $absolute,
        ]);

        SitePaths::ensureConfiguredDataPaths();

        $this->assertDirectoryExists($absolute);

        \Illuminate\Support\Facades\File::deleteDirectory($absolute);
        @rmdir(dirname($absolute));
    }

    public function test_ensure_sqlite_database_file_creates_missing_database(): void
    {
        $database = storage_path('framework/testing/site-db-'.bin2hex(random_bytes(4)).'/database.sqlite');

        config(['database.connections.sqlite.database' => $database]);

        $this->assertFileDoesNotExist($database);

        SitePaths::ensureSqliteDatabaseFile();

        $this->assertFileExists($database);

        unlink($database);
        \Illuminate\Support\Facades\File::deleteDirectory(dirname($database));
    }

    public function test_ensure_common_upload_directories_creates_admin_upload_folders(): void
    {
        $publicRoot = storage_path('framework/testing/common-uploads-'.bin2hex(random_bytes(4)));

        config(['filesystems.disks.public.root' => $publicRoot]);

        SitePaths::ensureCommonUploadDirectories();

        $this->assertDirectoryExists($publicRoot.'/settings/branding');
        $this->assertDirectoryExists($publicRoot.'/gallery/photos');

        \Illuminate\Support\Facades\File::deleteDirectory($publicRoot);
    }

    public function test_public_storage_url_uses_configured_base(): void
    {
        config(['filesystems.disks.public.url' => '/media']);

        $this->assertSame(
            '/media/settings/branding/logo.png',
            SitePaths::publicStorageUrl('settings/branding/logo.png'),
        );
    }

    public function test_normalize_upload_relative_path_strips_storage_prefix(): void
    {
        $this->assertSame(
            'settings/branding/logo.png',
            SitePaths::normalizeUploadRelativePath('/storage/settings/branding/logo.png'),
        );
    }

    public function test_ensure_public_storage_link_repairs_wrong_symlink_target(): void
    {
        $uploadRoot = storage_path('framework/testing/link-target-'.bin2hex(random_bytes(4)));
        $wrongTarget = storage_path('framework/testing/link-wrong-'.bin2hex(random_bytes(4)));

        \Illuminate\Support\Facades\File::ensureDirectoryExists($uploadRoot);
        \Illuminate\Support\Facades\File::ensureDirectoryExists($wrongTarget);

        config(['site.paths.public_uploads' => $uploadRoot]);
        SitePaths::syncPublicDiskConfig();

        $link = public_path('storage');

        if (file_exists($link)) {
            if (is_link($link)) {
                @unlink($link);
            } else {
                \Illuminate\Support\Facades\File::deleteDirectory($link);
            }
        }

        symlink($wrongTarget, $link);

        $this->assertFalse(SitePaths::publicStorageLinkDetail()['ok']);

        $this->assertTrue(SitePaths::ensurePublicStorageLink());
        $this->assertTrue(SitePaths::publicStorageLinkDetail()['ok']);
        $this->assertSame(realpath($uploadRoot), realpath($link));

        if (is_link($link)) {
            @unlink($link);
        }

        \Illuminate\Support\Facades\File::deleteDirectory($uploadRoot);
        \Illuminate\Support\Facades\File::deleteDirectory($wrongTarget);
    }
}
