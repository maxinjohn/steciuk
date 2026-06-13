<?php

namespace App\Services;

use App\Support\SitePaths;
use Illuminate\Support\Facades\Artisan;
use PDO;
use RuntimeException;
use Throwable;

class SqliteHealth
{
    public static function databasePath(): ?string
    {
        if (config('database.default') !== 'sqlite') {
            return null;
        }

        $database = config('database.connections.sqlite.database');

        if (! is_string($database) || $database === '' || $database === ':memory:') {
            return null;
        }

        $resolved = SitePaths::resolve($database) ?? $database;

        return realpath($resolved) ?: $resolved;
    }

    public static function integrityOk(?string $path = null): bool
    {
        $path ??= static::databasePath();

        if ($path === null || $path === ':memory:' || ! is_file($path)) {
            return false;
        }

        try {
            $pdo = new PDO('sqlite:'.$path, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $result = (string) $pdo->query('PRAGMA integrity_check')->fetchColumn();
            $pdo = null;

            return strtolower($result) === 'ok';
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return list<string>
     */
    public static function sidecarPaths(string $databasePath): array
    {
        return [
            $databasePath.'-wal',
            $databasePath.'-shm',
            $databasePath.'-journal',
        ];
    }

    public static function removeSidecarFiles(string $databasePath): void
    {
        foreach (static::sidecarPaths($databasePath) as $sidecar) {
            if (is_file($sidecar)) {
                @unlink($sidecar);
            }
        }

        static::removeMaintenanceMarkers($databasePath);
    }

    public static function removeMaintenanceMarkers(string $databasePath): void
    {
        $directory = dirname($databasePath);

        foreach (['.sqlite-wal-ready', '.sqlite-pragmas.lock'] as $marker) {
            $path = $directory.'/'.$marker;

            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    public static function backupPath(string $databasePath, string $reason = 'backup'): string
    {
        $directory = dirname($databasePath).'/backups';
        SitePaths::ensureDirectoryExists($directory);

        return $directory.'/database-'.$reason.'-'.now()->format('Ymd-His').'.sqlite';
    }

    public static function quarantine(string $databasePath, string $reason = 'corrupt'): ?string
    {
        if (! is_file($databasePath)) {
            return null;
        }

        $destination = static::backupPath($databasePath, $reason);

        if (! @rename($databasePath, $destination)) {
            if (! @copy($databasePath, $destination)) {
                throw new RuntimeException("Unable to quarantine SQLite database at {$databasePath}.");
            }

            @unlink($databasePath);
        }

        static::removeSidecarFiles($databasePath);

        return $destination;
    }

    public static function backup(string $databasePath, string $reason = 'backup'): ?string
    {
        if (! is_file($databasePath)) {
            return null;
        }

        $destination = static::backupPath($databasePath, $reason);

        if (! @copy($databasePath, $destination)) {
            throw new RuntimeException("Unable to back up SQLite database at {$databasePath}.");
        }

        return $destination;
    }

    public static function recreateEmptyDatabase(?string $path = null): string
    {
        $path ??= static::databasePath();

        if ($path === null || $path === ':memory:') {
            throw new RuntimeException('SQLite database path is not configured.');
        }

        SitePaths::ensureParentDirectoryForFile($path);
        static::removeSidecarFiles($path);

        if (is_file($path)) {
            @unlink($path);
        }

        if (! @touch($path)) {
            throw new RuntimeException("Unable to create SQLite database file at {$path}.");
        }

        @chmod($path, 0664);
        SqliteOptimizer::initializeNewDatabase($path);

        return $path;
    }

    public static function rebuildReferenceData(): void
    {
        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('site:bootstrap', ['--force' => true]);
        Artisan::call('cache:clear');
    }

    public static function repair(bool $forceBootstrap = true): string
    {
        $path = static::databasePath();

        if ($path === null) {
            throw new RuntimeException('SQLite database path is not configured.');
        }

        if (is_file($path) && ! static::integrityOk($path)) {
            static::quarantine($path, 'corrupt');
        } elseif (is_file($path)) {
            static::backup($path, 'before-repair');
        }

        static::recreateEmptyDatabase($path);

        Artisan::call('migrate', ['--force' => true]);

        if ($forceBootstrap) {
            Artisan::call('site:bootstrap', ['--force' => true]);
        }

        Artisan::call('cache:clear');

        if (! static::integrityOk($path)) {
            throw new RuntimeException('SQLite repair completed but integrity check still failed.');
        }

        return $path;
    }

    public static function ensureReady(): void
    {
        if (config('database.default') !== 'sqlite') {
            return;
        }

        SitePaths::ensureSqliteDatabaseFile();

        $path = static::databasePath();

        if ($path === null || static::integrityOk($path)) {
            return;
        }

        if (app()->environment('local', 'testing')) {
            app()->booted(static fn (): bool => static::repair());

            return;
        }

        throw new RuntimeException(
            'SQLite database is corrupt. Restore from backup with: php artisan db:repair-sqlite --force',
        );
    }
}
