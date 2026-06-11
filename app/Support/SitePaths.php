<?php

namespace App\Support;

use RuntimeException;

class SitePaths
{
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

    public static function directoryMode(): int
    {
        $mode = env('SITE_DATA_DIR_MODE', '0775');

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
        $mode = self::directoryMode();

        self::ensureLaravelStorageLayout(
            self::resolve(env('APP_STORAGE_PATH')) ?? storage_path(),
            $mode,
        );

        self::ensureParentDirectoryForFile(env('DB_DATABASE'), $mode);

        self::ensureDirectoryExists(
            self::resolve(env('PRIVATE_STORAGE_PATH')) ?? storage_path('app/private'),
            $mode,
        );

        self::ensureDirectoryExists(
            self::resolve(env('PUBLIC_STORAGE_PATH')) ?? storage_path('app/public'),
            $mode,
        );
    }
}
