<?php

namespace App\Database;

use Illuminate\Database\Connectors\SQLiteConnector as BaseSQLiteConnector;

class SQLiteConnector extends BaseSQLiteConnector
{
    /**
     * Avoid re-setting journal mode when it already matches — that write can block
     * on an exclusive lock while another process holds the database.
     */
    protected function configureJournalMode($connection, array $config): void
    {
        if (! isset($config['journal_mode'])) {
            return;
        }

        $target = strtolower((string) $config['journal_mode']);

        try {
            $current = strtolower((string) $connection->query('PRAGMA journal_mode')->fetchColumn());
        } catch (\Throwable) {
            $current = '';
        }

        if ($current === $target) {
            return;
        }

        parent::configureJournalMode($connection, $config);
    }
}
