<?php

namespace App\Database;

use App\Enums\UserRole;
use App\Services\SiteCache;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Applies predefined parish reference data during deploy migrations.
 *
 * Uses sync semantics: upsert seeded records by stable keys, add missing rows,
 * update shipped reference definitions, and never delete prod-only content.
 */
class ReferenceDataMigrator
{
    /**
     * Stable reference anchors shipped in ReferenceDataSeeder (table, column, value).
     *
     * @var list<array{0: string, 1: string, 2: string}>
     */
    private const REFERENCE_ANCHORS = [
        ['pages', 'slug', 'home'],
        ['gallery_albums', 'slug', 'parish-worship-services'],
        ['settings', 'key', 'church_name'],
        ['events', 'slug', 'uk-parish-fellowship-day'],
        ['news', 'slug', 'lent-prayer-week-uk-parish'],
        ['ministries', 'slug', 'sunday-school'],
        ['resources', 'slug', 'order-of-holy-communion'],
        ['designations', 'slug', 'vicar'],
        ['panels', 'slug', 'parish-committee'],
    ];

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

    public static function needsSync(): bool
    {
        if (! Schema::hasTable('migrations')) {
            return false;
        }

        foreach (self::REFERENCE_ANCHORS as [$table, $column, $value]) {
            if (self::missingRow($table, $column, $value)) {
                return true;
            }
        }

        $adminEmail = strtolower(trim((string) config('site.admin_email', 'admin@steciuk.org')));

        if ($adminEmail !== '' && self::missingRow('users', 'email', $adminEmail)) {
            return true;
        }

        if (self::missingRow('roles', 'slug', UserRole::SuperAdmin->value)) {
            return true;
        }

        if (self::tableEmpty('menu_items')) {
            return true;
        }

        if (self::tableEmpty('services')) {
            return true;
        }

        if (self::tableEmpty('sermons')) {
            return true;
        }

        return false;
    }

    private static function missingRow(string $table, string $column, mixed $value): bool
    {
        if (! Schema::hasTable($table)) {
            return false;
        }

        return ! DB::table($table)->where($column, $value)->exists();
    }

    private static function tableEmpty(string $table): bool
    {
        if (! Schema::hasTable($table)) {
            return false;
        }

        return DB::table($table)->count() === 0;
    }
}
