<?php

namespace App\Services;

use App\Support\SitePaths;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use PDO;
use Throwable;

class SqliteOptimizer
{
    /** @var array<int, true> */
    private static array $configuredConnections = [];

    /**
     * Apply per-connection read/concurrency pragmas on every SQLite connection.
     */
    public static function configureConnection(Connection $connection): void
    {
        if ($connection->getDriverName() !== 'sqlite') {
            return;
        }

        $database = (string) $connection->getConfig('database');

        if ($database === '' || $database === ':memory:') {
            return;
        }

        try {
            $pdo = $connection->getPdo();
        } catch (Throwable) {
            return;
        }

        $connectionId = spl_object_id($pdo);

        if (isset(static::$configuredConnections[$connectionId])) {
            return;
        }

        static::ensureFileLevelPragmas($pdo, $database);
        static::applyConnectionPragmas($pdo);

        static::$configuredConnections[$connectionId] = true;
    }

    /**
     * Configure WAL mode once per database file (safe for many concurrent readers).
     */
    public static function ensureFileLevelPragmas(PDO $pdo, ?string $databasePath = null): void
    {
        $databasePath ??= (string) config('database.connections.sqlite.database');

        if ($databasePath === '' || $databasePath === ':memory:') {
            return;
        }

        $resolvedPath = SitePaths::resolve($databasePath) ?? $databasePath;
        $resolvedPath = realpath($resolvedPath) ?: $resolvedPath;
        $markerPath = dirname($resolvedPath).'/.sqlite-wal-ready';

        if (is_file($markerPath)) {
            return;
        }

        static::applyFileLevelPragmas($pdo, $markerPath);
    }

    public static function initializeNewDatabase(string $databasePath): void
    {
        if ($databasePath === '' || $databasePath === ':memory:') {
            return;
        }

        $resolvedPath = SitePaths::resolve($databasePath) ?? $databasePath;
        $resolvedPath = realpath($resolvedPath) ?: $resolvedPath;
        $markerPath = dirname($resolvedPath).'/.sqlite-wal-ready';

        $pdo = new PDO('sqlite:'.$resolvedPath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        static::applyFileLevelPragmas($pdo, $markerPath);
        static::applyConnectionPragmas($pdo);
    }

    public static function maintain(bool $light = false, bool $reclaim = false): void
    {
        if (config('database.default') !== 'sqlite') {
            return;
        }

        $pdo = static::pdo();

        if ($pdo === null) {
            return;
        }

        static::ensureFileLevelPragmas($pdo);
        static::applyConnectionPragmas($pdo);

        if ($reclaim) {
            $databasePath = SqliteHealth::databasePath();

            if ($databasePath !== null && SqliteHealth::integrityOk($databasePath)) {
                SqliteHealth::backup($databasePath, 'before-reclaim');
            }

            static::enableIncrementalAutoVacuum($pdo, true);
            static::execWithRetry($pdo, 'PRAGMA wal_checkpoint(TRUNCATE)');
            static::execWithRetry($pdo, 'VACUUM');
        } else {
            static::enableIncrementalAutoVacuum($pdo, false);
            static::execWithRetry($pdo, 'PRAGMA wal_checkpoint(PASSIVE)');
            $pages = $light ? 100 : 400;
            static::execWithRetry($pdo, 'PRAGMA incremental_vacuum('.$pages.')');
        }

        if (! $light) {
            static::analyze();
            static::execWithRetry($pdo, 'PRAGMA optimize');
        }
    }

    public static function analyze(): void
    {
        if (config('database.default') !== 'sqlite') {
            return;
        }

        static::execWithRetry(static::pdo(), 'ANALYZE');
    }

    public static function autoVacuumMode(): ?int
    {
        $pdo = static::pdo();

        if ($pdo === null) {
            return null;
        }

        return (int) $pdo->query('PRAGMA auto_vacuum')->fetchColumn();
    }

    public static function journalMode(?PDO $pdo = null): ?string
    {
        $pdo ??= static::pdo();

        if ($pdo === null) {
            return null;
        }

        return strtolower((string) $pdo->query('PRAGMA journal_mode')->fetchColumn());
    }

    private static function applyFileLevelPragmas(PDO $pdo, string $markerPath): void
    {
        $journalMode = strtolower((string) config('database.connections.sqlite.journal_mode', 'wal'));
        $synchronous = strtolower((string) config('database.connections.sqlite.synchronous', 'normal'));

        $currentJournalMode = strtolower((string) $pdo->query('PRAGMA journal_mode')->fetchColumn());

        if ($currentJournalMode !== $journalMode) {
            static::execWithRetry($pdo, 'PRAGMA journal_mode = '.static::quotePragmaValue($journalMode));
        }

        $currentSynchronous = strtolower((string) $pdo->query('PRAGMA synchronous')->fetchColumn());

        if ($currentSynchronous !== $synchronous) {
            static::execWithRetry($pdo, 'PRAGMA synchronous = '.static::quotePragmaValue($synchronous));
        }

        if (strtolower((string) $pdo->query('PRAGMA journal_mode')->fetchColumn()) === 'wal') {
            @file_put_contents($markerPath, now()->toIso8601String());
        }
    }

    private static function applyConnectionPragmas(PDO $pdo): void
    {
        $busyTimeout = (int) config('database.connections.sqlite.busy_timeout', 60000);
        static::execWithRetry($pdo, 'PRAGMA busy_timeout = '.$busyTimeout);
        static::execWithRetry($pdo, 'PRAGMA foreign_keys = ON');

        $walAutocheckpoint = (int) config('database.connections.sqlite.wal_autocheckpoint', 2000);

        if ($walAutocheckpoint > 0) {
            static::execWithRetry($pdo, 'PRAGMA wal_autocheckpoint = '.$walAutocheckpoint);
        }
    }

    private static function enableIncrementalAutoVacuum(PDO $pdo, bool $runVacuum): void
    {
        if ((int) $pdo->query('PRAGMA auto_vacuum')->fetchColumn() === 2) {
            return;
        }

        static::execWithRetry($pdo, 'PRAGMA auto_vacuum = INCREMENTAL');

        if ($runVacuum) {
            static::execWithRetry($pdo, 'VACUUM');
        }
    }

    private static function execWithRetry(?PDO $pdo, string $statement, int $attempts = 8): void
    {
        if ($pdo === null) {
            return;
        }

        $delayMs = 25;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                $pdo->exec($statement);

                return;
            } catch (Throwable $exception) {
                if (! static::isLockError($exception) || $attempt === $attempts) {
                    throw $exception;
                }

                usleep($delayMs * 1000);
                $delayMs = min($delayMs * 2, 750);
            }
        }
    }

    private static function isLockError(Throwable $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, 'database is locked')
            || str_contains($message, 'database table is locked');
    }

    private static function pdo(): ?PDO
    {
        try {
            return DB::connection()->getPdo();
        } catch (Throwable) {
            return null;
        }
    }

    private static function quotePragmaValue(string $value): string
    {
        $normalized = strtolower(trim($value));

        return match ($normalized) {
            'wal', 'delete', 'truncate', 'persist', 'memory', 'normal', 'full', 'extra', 'off' => strtoupper($normalized),
            default => "'".str_replace("'", "''", $value)."'",
        };
    }
}
