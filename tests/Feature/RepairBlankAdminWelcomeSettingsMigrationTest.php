<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepairBlankAdminWelcomeSettingsMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_backfills_blank_admin_welcome_settings(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        Setting::query()->where('key', 'admin_dashboard_verse')->update(['value' => '']);
        Setting::query()->where('key', 'admin_dashboard_verse_ref')->update(['value' => '']);
        Setting::forgetCache();

        $migration = include database_path('migrations/2026_06_13_000002_repair_blank_admin_welcome_settings.php');
        $migration->up();

        Setting::forgetCache();

        $this->assertSame('Be still, and know that I am God.', Setting::get('admin_dashboard_verse'));
        $this->assertSame('Psalm 46:10', Setting::get('admin_dashboard_verse_ref'));
    }
}
