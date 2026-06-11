<?php

namespace App\Services;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use PDO;
use Throwable;

class SqliteOptimizer
{
    /** @var array<int, true> */
    private static array $configuredConnections = [];

    private static bool $persistentPragmasEnsured = false;

    /**
     * Apply per-connection performance pragmas once. Persistent settings (WAL) are
     * configured separately so concurrent PHP processes do not fight for locks.
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

        static::ensurePersistentPragmas($pdo, $database);

        static::$configuredConnections[$connectionId] = true;
    }

    /**
     * Set WAL mode and related persistent pragmas once per database file.
     */
    public static function ensurePersistentPragmas(PDO $pdo, ?string $databasePath = null): void
    {
        if (static::$persistentPragmasEnsured) {
            return;
        }

        $databasePath ??= (string) config('database.connections.sqlite.database');

        if ($databasePath === '' || $databasePath === ':memory:') {
            static::$persistentPragmasEnsured = true;

            return;
        }

        $resolvedPath = realpath($databasePath) ?: $databasePath;
        $lockPath = dirname($resolvedPath).'/.sqlite-pragmas.lock';
        $lockHandle = @fopen($lockPath, 'c+');

        if ($lockHandle === false) {
            static::applyPersistentPragmas($pdo);
            static::$persistentPragmasEnsured = true;

            return;
        }

        try {
            flock($lockHandle, LOCK_EX);

            static::applyPersistentPragmas($pdo);
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }

        static::$persistentPragmasEnsured = true;
    }

    public static function initializeNewDatabase(string $databasePath): void
    {
        if ($databasePath === '' || $databasePath === ':memory:') {
            return;
        }

        $pdo = new PDO('sqlite:'.$databasePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        static::applyPersistentPragmas($pdo);
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

        static::ensurePersistentPragmas($pdo);

        if ($reclaim) {
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

    private static function applyPersistentPragmas(PDO $pdo): void
    {
        $busyTimeout = (int) config('database.connections.sqlite.busy_timeout', 30000);
        $journalMode = strtolower((string) config('database.connections.sqlite.persistent_pragmas.journal_mode', 'wal'));
        $synchronous = strtolower((string) config('database.connections.sqlite.persistent_pragmas.synchronous', 'normal'));

        static::execWithRetry($pdo, 'PRAGMA busy_timeout = '.$busyTimeout);
        static::execWithRetry($pdo, 'PRAGMA foreign_keys = ON');

        $currentJournalMode = strtolower((string) $pdo->query('PRAGMA journal_mode')->fetchColumn());

        if ($currentJournalMode !== $journalMode) {
            static::execWithRetry($pdo, 'PRAGMA journal_mode = '.static::quotePragmaValue($journalMode));
        }

        $currentSynchronous = strtolower((string) $pdo->query('PRAGMA synchronous')->fetchColumn());

        if ($currentSynchronous !== $synchronous) {
            static::execWithRetry($pdo, 'PRAGMA synchronous = '.static::quotePragmaValue($synchronous));
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

    private static function execWithRetry(?PDO $pdo, string $statement, int $attempts = 5): void
    {
        if ($pdo === null) {
            return;
        }

        $delayMs = 50;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                $pdo->exec($statement);

                return;
            } catch (Throwable $exception) {
                if (! static::isLockError($exception) || $attempt === $attempts) {
                    throw $exception;
                }

                usleep($delayMs * 1000);
                $delayMs = min($delayMs * 2, 500);
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
