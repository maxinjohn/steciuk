<?php

namespace App\Database;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Database\SQLiteConnection as BaseSQLiteConnection;

class SQLiteConnection extends BaseSQLiteConnection
{
    /**
     * @param  array<int, mixed>  $bindings
     */
    protected function run($query, $bindings, Closure $callback)
    {
        $attempts = max(1, (int) ($this->getConfig('lock_retry_attempts') ?? 8));
        $delayMs = max(1, (int) ($this->getConfig('lock_retry_delay_ms') ?? 25));

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                return parent::run($query, $bindings, $callback);
            } catch (QueryException $exception) {
                if (! $this->isSqliteLockError($exception) || $attempt === $attempts) {
                    throw $exception;
                }

                usleep($delayMs * 1000);
                $delayMs = min($delayMs * 2, 750);
            }
        }

        throw new QueryException(
            $this->getName(),
            $query,
            $this->prepareBindings($bindings),
            new \RuntimeException('SQLite lock retry exhausted.'),
        );
    }

    protected function isSqliteLockError(QueryException $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, 'database is locked')
            || str_contains($message, 'database table is locked');
    }
}
