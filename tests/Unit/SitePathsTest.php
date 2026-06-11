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
        $relative = '../site_data/testing/auto-uploads-'.bin2hex(random_bytes(4));
        $absolute = base_path($relative);

        $this->assertDirectoryDoesNotExist($absolute);

        config(['site.paths.public_uploads' => $relative]);

        SitePaths::ensureConfiguredDataPaths();

        $this->assertDirectoryExists($absolute);

        rmdir($absolute);
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
        rmdir(dirname($database));
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
}
