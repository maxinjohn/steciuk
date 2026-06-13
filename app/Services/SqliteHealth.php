<?php

namespace App\Services;

use App\Support\SitePaths;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
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

    public static function schemaReady(?string $path = null): bool
    {
        $path ??= static::databasePath();

        if ($path === null || $path === ':memory:' || ! is_file($path)) {
            return false;
        }

        try {
            $pdo = new PDO('sqlite:'.$path, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $table = $pdo->query(
                "SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'migrations' LIMIT 1",
            )->fetchColumn();
            $pdo = null;

            return is_string($table) && $table === 'migrations';
        } catch (Throwable) {
            return false;
        }
    }

    public static function isHealthy(?string $path = null): bool
    {
        return static::integrityOk($path) && static::schemaReady($path);
    }

    public static function purgeConnections(): void
    {
        $connection = config('database.default');

        if (! is_string($connection) || $connection === '') {
            return;
        }

        DB::purge($connection);
    }

    public static function migrateIfNeeded(): void
    {
        $path = static::databasePath();

        if ($path === null || static::schemaReady($path)) {
            return;
        }

        static::purgeConnections();
        Artisan::call('migrate', ['--force' => true]);
        static::purgeConnections();
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

        foreach (['.sqlite-wal-ready'] as $marker) {
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

    public static function repair(bool $forceBootstrap = false): string
    {
        $path = static::databasePath();

        if ($path === null) {
            throw new RuntimeException('SQLite database path is not configured.');
        }

        static::purgeConnections();

        if (is_file($path) && ! static::integrityOk($path)) {
            static::quarantine($path, 'corrupt');
        } elseif (is_file($path)) {
            static::backup($path, 'before-repair');
        }

        static::recreateEmptyDatabase($path);
        static::purgeConnections();

        Artisan::call('migrate', ['--force' => true]);

        if ($forceBootstrap) {
            Artisan::call('site:bootstrap', ['--force' => true]);
        }

        Artisan::call('cache:clear');
        static::purgeConnections();

        if (! static::isHealthy($path)) {
            throw new RuntimeException(
                'SQLite repair completed but database is still not ready '
                .'(integrity: '.(static::integrityOk($path) ? 'ok' : 'failed')
                .', schema: '.(static::schemaReady($path) ? 'ok' : 'missing').').',
            );
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

        if ($path === null || static::isHealthy($path)) {
            return;
        }

        if (! static::integrityOk($path)) {
            if (app()->environment('production')) {
                throw new RuntimeException(
                    'SQLite database is corrupt. Restore from backup with: php artisan db:repair-sqlite --force',
                );
            }

            return;
        }

        static::migrateIfNeeded();
    }
}
