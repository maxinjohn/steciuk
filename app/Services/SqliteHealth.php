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
    private const READY_MARKER = '.sqlite-ready';

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
            $pdo = static::openReadOnlyPdo($path);
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
            $pdo = static::openReadOnlyPdo($path);
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

    public static function fingerprint(string $path): ?string
    {
        $mtime = @filemtime($path);
        $size = @filesize($path);

        if ($mtime === false || $size === false) {
            return null;
        }

        return $mtime.'-'.$size;
    }

    public static function rememberHealthy(string $path): void
    {
        $fingerprint = static::fingerprint($path);

        if ($fingerprint === null) {
            return;
        }

        @file_put_contents(dirname($path).'/'.static::READY_MARKER, $fingerprint);
    }

    public static function fastHealthy(string $path): bool
    {
        $fingerprint = static::fingerprint($path);

        if ($fingerprint === null) {
            return false;
        }

        $marker = dirname($path).'/'.static::READY_MARKER;

        return is_file($marker) && trim((string) @file_get_contents($marker)) === $fingerprint;
    }

    public static function tableExists(string $table, ?string $path = null): bool
    {
        $path ??= static::databasePath();

        if ($path === null || $path === ':memory:' || ! is_file($path)) {
            return false;
        }

        try {
            $pdo = static::openReadOnlyPdo($path);
            $quoted = $pdo->quote($table);
            $name = $pdo->query(
                "SELECT name FROM sqlite_master WHERE type = 'table' AND name = {$quoted} LIMIT 1",
            )->fetchColumn();
            $pdo = null;

            return is_string($name) && $name === $table;
        } catch (Throwable) {
            return false;
        }
    }

    public static function journalMode(?string $path = null): ?string
    {
        $path ??= static::databasePath();

        if ($path === null || $path === ':memory:' || ! is_file($path)) {
            return null;
        }

        try {
            $pdo = static::openReadOnlyPdo($path);

            return strtolower((string) $pdo->query('PRAGMA journal_mode')->fetchColumn());
        } catch (Throwable) {
            return null;
        }
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
        static::rememberHealthy($path);
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

        foreach (['.sqlite-wal-ready', static::READY_MARKER] as $marker) {
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

        static::rememberHealthy($path);

        return $path;
    }

    protected static function openReadOnlyPdo(string $path): PDO
    {
        $pdo = new PDO('sqlite:'.$path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $pdo->exec('PRAGMA busy_timeout = 5000');

        return $pdo;
    }

    public static function ensureReady(): void
    {
        if (config('database.default') !== 'sqlite') {
            return;
        }

        SitePaths::ensureSqliteDatabaseFile();

        $path = static::databasePath();

        if ($path === null) {
            return;
        }

        if (static::fastHealthy($path)) {
            return;
        }

        if (static::isHealthy($path)) {
            static::rememberHealthy($path);

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
