<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\AdminMobileDock;
use App\Support\AdminPanelConfig;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMobileDockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_super_admin_gets_home_worship_events_and_menu(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $this->actingAs($admin);

        $labels = array_column(AdminMobileDock::items(), 'label');

        $this->assertSame(['Home', 'Worship', 'Events', 'Menu'], $labels);
    }

    public function test_editor_dock_has_home_menu_and_two_middle_links(): void
    {
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $this->actingAs($editor);

        $items = AdminMobileDock::items();

        $this->assertCount(4, $items);
        $this->assertSame('Home', $items[0]['label']);
        $this->assertSame('link', $items[0]['type']);
        $this->assertSame('link', $items[1]['type']);
        $this->assertSame('link', $items[2]['type']);
        $this->assertSame('Menu', $items[3]['label']);
        $this->assertSame('menu', $items[3]['type']);
    }

    public function test_home_item_is_active_on_dashboard_only(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $this->actingAs($admin);

        $this->get(AdminPanelConfig::url());
        $home = AdminMobileDock::items()[0];
        $this->assertTrue($home['isActive']);

        $this->get(AdminPanelConfig::url('pages'));
        $home = AdminMobileDock::items()[0];
        $this->assertFalse($home['isActive']);
    }
}
