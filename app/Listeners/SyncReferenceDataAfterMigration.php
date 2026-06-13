<?php

namespace App\Listeners;

use App\Database\ReferenceDataMigrator;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Events\NoPendingMigrations;

class SyncReferenceDataAfterMigration
{
    public function handle(MigrationsEnded|NoPendingMigrations $event): void
    {
        if ($event instanceof MigrationsEnded) {
            if ($event->options['pretend'] ?? false) {
                return;
            }
        }

        if ($event instanceof NoPendingMigrations && $event->method !== 'up') {
            return;
        }

        ReferenceDataMigrator::sync();
    }
}
