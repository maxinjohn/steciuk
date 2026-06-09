<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Idempotent reference data for first install and controlled prod sync.
 *
 * - Upserts records by stable keys (slug, email, seed_key, setting key)
 * - Never deletes prod-only records (custom pages, menu links, form submissions)
 * - Preserves prod passwords and settings unless overwrite flags are enabled
 */
class ReferenceDataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            SettingsSeeder::class,
            ServiceSeeder::class,
            MinistrySeeder::class,
            LeadershipSeeder::class,
            PageSeeder::class,
            MenuSeeder::class,
            EventSeeder::class,
            NewsSeeder::class,
            SermonSeeder::class,
            ResourceSeeder::class,
            GallerySeeder::class,
        ]);
    }
}
