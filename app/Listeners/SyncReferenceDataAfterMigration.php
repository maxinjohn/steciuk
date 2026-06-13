<?php

namespace App\Listeners;

use App\Database\ReferenceDataMigrator;
use Illuminate\Database\Events\MigrationsEnded;

class SyncReferenceDataAfterMigration
{
    public function handle(MigrationsEnded $event): void
    {
        if ($event->options['pretend'] ?? false) {
            return;
        }

        ReferenceDataMigrator::sync();
    }
}
