<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class SitePaths
{
    /**
     * @var list<string>
     */
    private const UPLOAD_DIRECTORIES = [
        'settings/branding',
        'settings/seo',
        'gallery/photos',
        'gallery/albums',
        'events/featured',
        'news/featured',
        'pages/featured',
        'pages/og',
        'ministries/featured',
        'blocks/hero',
        'blocks/media',
        'sermons/audio',
        'sermons/pdf',
    ];
    /**
     * Resolve a path from .env — absolute paths pass through, relative paths
     * are resolved from the Laravel project root (not PHP's cwd).
     */
    public static function resolve(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $path = trim($path);

        if ($path === ':memory:') {
            return $path;
        }

        if (self::isAbsolute($path)) {
            return $path;
        }

        return base_path($path);
    }

    public static function isAbsolute(string $path): bool
    {
        if (str_starts_with($path, '/')) {
            return true;
        }

        return (bool) preg_match('/^[A-Za-z]:[\\\\\\/]/', $path);
    }

    public static function configuredRaw(string $key): ?string
    {
        $value = config("site.paths.{$key}");

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return trim($value);
    }

    public static function configuredPath(string $key, ?string $default = null): ?string
    {
        return self::resolve(self::configuredRaw($key) ?? $default);
    }

    public static function directoryMode(): int
    {
        $mode = config('site.dir_mode', '0775');

        if (is_string($mode) && str_starts_with($mode, '0')) {
            return octdec($mode);
        }

        return is_numeric($mode) ? (int) $mode : 0775;
    }

    public static function ensureDirectoryExists(?string $path, ?int $mode = null): ?string
    {
        $resolved = self::resolve($path);

        if ($resolved === null) {
            return null;
        }

        if (is_dir($resolved)) {
            return $resolved;
        }

        $mode ??= self::directoryMode();

        if (! @mkdir($resolved, $mode, true) && ! is_dir($resolved)) {
            throw new RuntimeException("Unable to create directory at {$resolved}. Check permissions or create it manually.");
        }

        return $resolved;
    }

    public static function ensureParentDirectoryForFile(?string $filePath, ?int $mode = null): ?string
    {
        $resolved = self::resolve($filePath);

        if ($resolved === null || $resolved === ':memory:') {
            return $resolved;
        }

        return self::ensureDirectoryExists(dirname($resolved), $mode);
    }

    public static function ensureLaravelStorageLayout(?string $storagePath, ?int $mode = null): ?string
    {
        $root = self::ensureDirectoryExists($storagePath, $mode);

        if ($root === null) {
            return null;
        }

        foreach ([
            'app',
            'app/public',
            'app/private',
            'framework/cache/data',
            'framework/sessions',
            'framework/views',
            'framework/testing',
            'logs',
        ] as $directory) {
            self::ensureDirectoryExists($root.'/'.$directory, $mode);
        }

        return $root;
    }

    public static function ensureConfiguredDataPaths(): void
    {
        $verifiedFlag = storage_path('framework/.site-paths-verified');

        if (app()->environment('production') && is_file($verifiedFlag)) {
            return;
        }

        $mode = self::directoryMode();

        self::ensureLaravelStorageLayout(
            self::configuredPath('storage') ?? storage_path(),
            $mode,
        );

        self::ensureParentDirectoryForFile(self::configuredRaw('database'), $mode);

        self::ensureDirectoryExists(
            self::configuredPath('public_uploads') ?? storage_path('app/public'),
            $mode,
        );

        self::ensureDirectoryExists(
            self::configuredPath('private_uploads') ?? storage_path('app/private'),
            $mode,
        );

        self::ensureCommonUploadDirectories($mode);

        if (app()->environment('production')) {
            @file_put_contents($verifiedFlag, now()->toIso8601String());
        }
    }

    public static function ensureCommonUploadDirectories(?int $mode = null): void
    {
        $publicRoot = config('filesystems.disks.public.root');

        if (! is_string($publicRoot) || $publicRoot === '') {
            return;
        }

        foreach (self::UPLOAD_DIRECTORIES as $directory) {
            self::ensureDirectoryExists($publicRoot.'/'.$directory, $mode);
        }
    }

    public static function ensureSqliteDatabaseFile(): void
    {
        $database = config('database.connections.sqlite.database');

        if (! is_string($database) || $database === '' || $database === ':memory:') {
            return;
        }

        self::ensureParentDirectoryForFile($database);

        if (file_exists($database)) {
            return;
        }

        if (! @touch($database)) {
            throw new RuntimeException("Unable to create SQLite database file at {$database}. Check permissions.");
        }

        @chmod($database, 0664);
    }

    public static function ensurePublicStorageLink(): bool
    {
        $link = public_path('storage');
        $target = config('filesystems.disks.public.root');

        if (! is_string($target) || $target === '' || ! is_dir($target)) {
            return false;
        }

        $targetPath = realpath($target) ?: $target;

        if (is_link($link)) {
            $linked = realpath($link) ?: readlink($link);

            return $linked === $targetPath;
        }

        if (file_exists($link)) {
            return is_dir($link);
        }

        try {
            return @symlink($targetPath, $link);
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }

    public static function isWritableDirectory(?string $path): bool
    {
        $resolved = self::resolve($path);

        return is_string($resolved)
            && is_dir($resolved)
            && is_writable($resolved);
    }

    /**
     * @return list<array{status: string, label: string, detail: string}>
     */
    public static function productionChecks(): array
    {
        $checks = [];

        $storageRoot = self::configuredPath('storage') ?? storage_path();
        $checks[] = self::check(
            self::isWritableDirectory($storageRoot),
            'Storage directory',
            $storageRoot,
        );

        $publicRoot = config('filesystems.disks.public.root');
        $checks[] = self::check(
            self::isWritableDirectory(is_string($publicRoot) ? $publicRoot : null),
            'Public uploads directory',
            is_string($publicRoot) ? $publicRoot : 'not configured',
        );

        $privateRoot = config('filesystems.disks.local.root');
        $checks[] = self::check(
            self::isWritableDirectory(is_string($privateRoot) ? $privateRoot : null),
            'Private uploads directory',
            is_string($privateRoot) ? $privateRoot : 'not configured',
        );

        $database = config('database.connections.sqlite.database');

        if ($database === ':memory:') {
            $checks[] = self::check(true, 'SQLite database file', ':memory:');
        } else {
            $databaseOk = is_string($database)
                && $database !== ''
                && file_exists($database)
                && is_readable($database)
                && is_writable($database);
            $checks[] = self::check(
                $databaseOk,
                'SQLite database file',
                is_string($database) ? $database : 'not configured',
            );
        }

        $link = public_path('storage');
        $linkOk = is_link($link) || (is_dir($link) && file_exists($link));
        $checks[] = self::check(
            $linkOk,
            'Public storage link',
            is_link($link)
                ? $link.' -> '.readlink($link)
                : ($linkOk ? $link : 'missing — run php artisan storage:link'),
        );

        $checks[] = self::check(
            filled(config('app.key')),
            'Application key',
            filled(config('app.key')) ? 'set' : 'missing — run php artisan key:generate',
        );

        $checks[] = self::check(
            ! app()->environment('production') || ! (bool) config('app.debug'),
            'Debug mode',
            config('app.debug') ? 'APP_DEBUG=true (disable on production)' : 'off',
        );

        $bootstrapCache = base_path('bootstrap/cache');
        $checks[] = self::check(
            is_dir($bootstrapCache) && is_writable($bootstrapCache),
            'Bootstrap cache directory',
            $bootstrapCache,
        );

        if (config('session.driver') === 'database') {
            $sessionTable = (string) config('session.table', 'sessions');
            $checks[] = self::check(
                Schema::hasTable($sessionTable),
                'Sessions table',
                $sessionTable,
            );
        }

        return $checks;
    }

    /**
     * @return array{status: string, label: string, detail: string}
     */
    private static function check(bool $ok, string $label, string $detail): array
    {
        return [
            'status' => $ok ? 'ok' : 'fail',
            'label' => $label,
            'detail' => $detail,
        ];
    }
}
