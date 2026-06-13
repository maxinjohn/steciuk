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

    public static function publicStorageBaseUrl(): string
    {
        return rtrim((string) config('filesystems.disks.public.url', '/storage'), '/');
    }

    public static function publicStorageUrl(?string $relativePath = ''): string
    {
        $base = self::publicStorageBaseUrl();
        $relativePath = ltrim(trim((string) $relativePath), '/');

        if ($relativePath === '') {
            return $base !== '' ? $base : '/';
        }

        return ($base !== '' ? $base : '').'/'.$relativePath;
    }

    public static function normalizeUploadRelativePath(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $path = trim(str_replace('\\', '/', $path));

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return null;
        }

        $publicBase = trim(self::publicStorageBaseUrl(), '/');

        if ($publicBase !== '' && str_starts_with(ltrim($path, '/'), $publicBase.'/')) {
            $path = substr(ltrim($path, '/'), strlen($publicBase) + 1);
        }

        if (str_starts_with($path, '/storage/')) {
            $path = substr($path, strlen('/storage/'));
        } elseif (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        $path = ltrim($path, '/');

        return $path !== '' ? $path : null;
    }

    public static function publicUploadExists(?string $path): bool
    {
        $relative = self::normalizeUploadRelativePath($path);

        if ($relative === null) {
            return false;
        }

        $absolute = self::publicUploadsRoot().'/'. $relative;

        return is_file($absolute);
    }

    public static function publicUploadsRoot(): string
    {
        self::syncPublicDiskConfig();

        $configured = self::configuredPath('public_uploads');

        if ($configured !== null) {
            return $configured;
        }

        $diskRoot = config('filesystems.disks.public.root');

        if (is_string($diskRoot) && $diskRoot !== '') {
            return self::resolve($diskRoot) ?? (realpath($diskRoot) ?: $diskRoot);
        }

        return storage_path('app/public');
    }

    public static function syncPublicDiskConfig(): void
    {
        $configured = self::configuredPath('public_uploads');
        $diskRoot = config('filesystems.disks.public.root');
        $public = $configured ?? (is_string($diskRoot) && $diskRoot !== '' ? self::resolve($diskRoot) : null);

        if ($public === null) {
            return;
        }

        config([
            'filesystems.disks.public.root' => $public,
            'filesystems.disks.public.url' => rtrim((string) config('filesystems.disks.public.url', '/storage'), '/') ?: '/storage',
            'filesystems.links' => [
                public_path('storage') => $public,
            ],
        ]);
    }

    /** @deprecated Use syncPublicDiskConfig() */
    public static function ensurePublicDiskConfigured(): void
    {
        self::syncPublicDiskConfig();
    }

    public static function ensureBrandingUploadDirectory(?int $mode = null): ?string
    {
        return self::ensureDirectoryExists(
            self::publicUploadsRoot().'/settings/branding',
            $mode,
        );
    }

    public static function writePublicUpload(string $relativePath, string $contents): bool
    {
        self::ensurePublicDiskConfigured();

        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');

        if ($relativePath === '' || str_contains($relativePath, '..')) {
            return false;
        }

        $absolute = self::publicUploadsRoot().'/'.$relativePath;

        self::ensureParentDirectoryForFile($absolute);

        return @file_put_contents($absolute, $contents) !== false;
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
        $alreadyVerified = is_file($verifiedFlag);
        $mode = self::directoryMode();

        if (! $alreadyVerified) {
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

            @file_put_contents($verifiedFlag, now()->toIso8601String());
        }

        SiteBrandingAssets::ensureParishLogoInUploads();
        self::ensurePublicStorageLink();
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

        \App\Services\SqliteOptimizer::initializeNewDatabase($database);
    }

    public static function publicStorageLinkDetail(): array
    {
        self::syncPublicDiskConfig();

        $link = public_path('storage');
        $expected = realpath(self::publicUploadsRoot()) ?: self::publicUploadsRoot();
        $current = null;

        if (is_link($link)) {
            $current = realpath($link) ?: readlink($link);
        } elseif (is_dir($link)) {
            $current = realpath($link) ?: $link;
        }

        return [
            'link' => $link,
            'expected' => $expected,
            'current' => is_string($current) ? $current : null,
            'ok' => is_string($current) && $current === $expected,
        ];
    }

    public static function ensurePublicStorageLink(): bool
    {
        self::syncPublicDiskConfig();

        $link = public_path('storage');
        $targetPath = self::publicUploadsRoot();

        if (! is_dir($targetPath)) {
            self::ensureDirectoryExists($targetPath);
            $targetPath = realpath($targetPath) ?: $targetPath;
        }

        if (! is_dir($targetPath)) {
            return false;
        }

        $targetPath = realpath($targetPath) ?: $targetPath;

        if (is_link($link)) {
            $linked = realpath($link) ?: readlink($link);

            if ($linked === $targetPath || (is_string($linked) && realpath($linked) === $targetPath)) {
                return true;
            }

            @unlink($link);
        }

        if (file_exists($link)) {
            if (is_dir($link) && ! is_link($link)) {
                $linkReal = realpath($link);
                $targetReal = realpath($targetPath);

                return $linkReal !== false
                    && $targetReal !== false
                    && $linkReal === $targetReal;
            }

            return false;
        }

        self::ensureDirectoryExists(dirname($link));

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
            $resolvedDatabase = is_string($database) ? (self::resolve($database) ?? $database) : null;
            $databaseOk = is_string($resolvedDatabase)
                && $resolvedDatabase !== ''
                && file_exists($resolvedDatabase)
                && is_readable($resolvedDatabase)
                && is_writable($resolvedDatabase);
            $integrityOk = $databaseOk && \App\Services\SqliteHealth::integrityOk($resolvedDatabase);
            $checks[] = self::check(
                $databaseOk,
                'SQLite database file',
                is_string($resolvedDatabase) ? $resolvedDatabase : 'not configured',
            );
            $checks[] = self::check(
                $integrityOk,
                'SQLite integrity',
                $integrityOk ? 'ok' : 'corrupt — run php artisan db:repair-sqlite --force',
            );

            $journalMode = \App\Services\SqliteHealth::journalMode($resolvedDatabase);
            $checks[] = self::check(
                $integrityOk && $journalMode === 'wal',
                'SQLite WAL mode',
                $journalMode === 'wal' ? 'wal' : ($journalMode ?? 'unknown').' — restart app after php artisan config:clear',
            );

            $busyTimeout = (int) config('database.connections.sqlite.busy_timeout', 10000);
            $checks[] = self::check(
                $busyTimeout > 0 && $busyTimeout <= 25000,
                'SQLite busy timeout',
                $busyTimeout <= 25000
                    ? "{$busyTimeout}ms"
                    : "{$busyTimeout}ms — set DB_BUSY_TIMEOUT=10000 in .env and run php artisan config:clear",
            );
        }

        $sessionDriver = (string) config('session.driver');
        $checks[] = self::check(
            config('database.default') !== 'sqlite' || $sessionDriver !== 'database',
            'Session driver',
            $sessionDriver === 'database'
                ? 'database — use SESSION_DRIVER=file to reduce SQLite lock contention'
                : $sessionDriver,
        );

        $cacheStore = (string) config('cache.default');
        $checks[] = self::check(
            config('database.default') !== 'sqlite' || $cacheStore !== 'database',
            'Cache store',
            $cacheStore === 'database'
                ? 'database — use CACHE_STORE=file to reduce SQLite lock contention'
                : $cacheStore,
        );

        $linkDetail = self::publicStorageLinkDetail();
        $checks[] = self::check(
            $linkDetail['ok'],
            'Public storage link',
            $linkDetail['ok']
                ? $linkDetail['link'].' -> '.$linkDetail['expected']
                : 'points to '.($linkDetail['current'] ?? 'missing').' — expected '.$linkDetail['expected'].' (run php artisan site:ensure-paths --link)',
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

        if (config('database.default') === 'sqlite') {
            $resolvedDatabase = is_string(config('database.connections.sqlite.database'))
                ? (self::resolve(config('database.connections.sqlite.database')) ?? config('database.connections.sqlite.database'))
                : null;
            $migrationsReady = is_string($resolvedDatabase) && $resolvedDatabase !== ':memory:'
                ? \App\Services\SqliteHealth::tableExists('migrations', $resolvedDatabase)
                : Schema::hasTable('migrations');
            $pagesReady = is_string($resolvedDatabase) && $resolvedDatabase !== ':memory:'
                ? \App\Services\SqliteHealth::tableExists('pages', $resolvedDatabase)
                : Schema::hasTable('pages');

            $checks[] = self::check(
                $migrationsReady,
                'Database migrations',
                $migrationsReady ? 'applied' : 'missing — run php artisan migrate --force',
            );

            $checks[] = self::check(
                $pagesReady,
                'Pages table',
                $pagesReady ? 'ready' : 'missing — run php artisan migrate --force',
            );

            if ($pagesReady) {
                $referenceDataReady = ! \App\Database\ReferenceDataMigrator::needsSync();
                $checks[] = self::check(
                    $referenceDataReady,
                    'Reference data',
                    $referenceDataReady
                        ? 'complete'
                        : 'incomplete — run php artisan migrate --force',
                );
            }
        }

        if (config('session.driver') === 'database') {
            $sessionTable = (string) config('session.table', 'sessions');
            $checks[] = self::check(
                Schema::hasTable($sessionTable),
                'Sessions table',
                $sessionTable,
            );
        }

        $bundledLogo = public_path('images/branding/steci-parish-logo.png');
        $checks[] = self::check(
            is_file($bundledLogo),
            'Bundled parish logo',
            is_file($bundledLogo) ? 'images/branding/steci-parish-logo.png' : 'missing — commit public/images/branding/steci-parish-logo.png',
        );

        $syncedLogo = public_path('storage/'.ltrim(\App\Support\SiteBrandingAssets::UPLOAD_LOGO_RELATIVE, '/'));
        $uploadRoot = config('filesystems.disks.public.root');
        $uploadLogo = is_string($uploadRoot)
            ? rtrim($uploadRoot, '/\\').'/'.ltrim(\App\Support\SiteBrandingAssets::UPLOAD_LOGO_RELATIVE, '/')
            : $syncedLogo;
        $checks[] = self::check(
            is_file($uploadLogo) || is_file($syncedLogo) || is_file($bundledLogo),
            'Synced parish logo in storage',
            is_file($uploadLogo)
                ? self::normalizeUploadRelativePath(\App\Support\SiteBrandingAssets::UPLOAD_LOGO_RELATIVE)
                : (is_file($syncedLogo)
                    ? 'storage/'.ltrim(\App\Support\SiteBrandingAssets::UPLOAD_LOGO_RELATIVE, '/')
                    : (is_file($bundledLogo)
                        ? 'run php artisan site:ensure-paths --link'
                        : 'missing bundled logo')),
        );

        $eaukMark = public_path('images/eauk/member-logo-small.png');
        $checks[] = self::check(
            is_file($eaukMark),
            'EAUK trust mark asset',
            is_file($eaukMark) ? 'images/eauk/member-logo-small.png' : 'missing — commit public/images/eauk/member-logo-small.png',
        );

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
