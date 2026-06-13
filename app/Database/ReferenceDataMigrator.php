<?php

namespace App\Database;

use App\Services\SiteCache;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Support\Facades\Schema;

/**
 * Applies predefined parish reference data during deploy migrations.
 *
 * Uses sync semantics: upsert seeded records by stable keys, add missing rows,
 * update shipped reference definitions, and never delete prod-only content.
 */
class ReferenceDataMigrator
{
    public static function sync(): void
    {
        if (! Schema::hasTable('migrations')) {
            return;
        }

        $previousMode = config('site.seed.mode');
        config(['site.seed.mode' => SeedConfig::MODE_SYNC]);

        try {
            ReferenceSiteProvisioner::ensureStructure();
            (new ReferenceDataSeeder)->run();
        } finally {
            config(['site.seed.mode' => $previousMode]);
        }

        SiteCache::forgetAfterReferenceDataChange();
    }
}
