<?php

namespace Tests\Unit;

use App\Models\MenuItem;
use App\Models\Ministry;
use App\Models\Page;
use App\Services\NavigationMenuSync;
use App\Support\NavigationMenuCatalog;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationMenuSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_pastoral_care_ministry_is_hidden_from_menu_by_default(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $this->assertDatabaseHas('ministries', [
            'slug' => 'pastoral-care',
            'show_in_menu' => false,
        ]);

        $this->assertDatabaseMissing('menu_items', [
            'seed_key' => NavigationMenuCatalog::ministrySeedKey('pastoral-care'),
            'is_visible' => true,
        ]);
    }

    public function test_ministry_can_be_added_to_ministries_menu_from_admin_toggle(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $ministry = Ministry::query()->where('slug', 'pastoral-care')->firstOrFail();
        $ministry->update([
            'show_in_menu' => true,
            'menu_parent_seed_key' => 'ministries',
        ]);

        $this->assertDatabaseHas('menu_items', [
            'seed_key' => 'ministry.pastoral-care',
            'label' => 'Pastoral Care',
            'url' => '/ministries/pastoral-care',
            'is_visible' => true,
        ]);
    }

    public function test_page_show_in_menu_false_hides_reference_menu_item(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $page = Page::query()->where('slug', 'choir')->firstOrFail();
        $page->update(['show_in_menu' => false]);

        $this->assertDatabaseHas('menu_items', [
            'seed_key' => 'ministries.choir',
            'is_visible' => false,
        ]);
    }

    public function test_reference_sync_respects_page_menu_visibility(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        Page::query()->where('slug', 'choir')->update(['show_in_menu' => false]);

        NavigationMenuSync::applyAll();

        $this->assertDatabaseHas('menu_items', [
            'seed_key' => 'ministries.choir',
            'is_visible' => false,
        ]);
    }

    public function test_ministry_menu_sync_skips_when_cms_page_exists(): void
    {
        $page = Page::query()->where('slug', 'custom-ministry-page')->first()
            ?? Page::factory()->create([
                'slug' => 'custom-ministry-page',
                'title' => 'Custom Ministry Page',
                'show_in_menu' => true,
                'menu_parent_seed_key' => 'ministries',
            ]);

        $ministry = Ministry::factory()->create([
            'slug' => 'custom-ministry-page',
            'name' => 'Custom Ministry Page',
            'show_in_menu' => true,
            'menu_parent_seed_key' => 'ministries',
        ]);

        NavigationMenuSync::syncMinistry($ministry);

        $this->assertDatabaseMissing('menu_items', [
            'seed_key' => 'ministry.custom-ministry-page',
            'is_visible' => true,
        ]);

        NavigationMenuSync::syncPage($page);

        $this->assertDatabaseHas('menu_items', [
            'seed_key' => 'page.custom-ministry-page',
            'page_id' => $page->id,
            'is_visible' => true,
        ]);
    }
}
