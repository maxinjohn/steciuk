<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Pages\PageResource;
use App\Models\Page;
use App\Models\User;
use App\Support\AdminPanelConfig;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $this->admin = User::factory()->create([
            'role' => UserRole::SuperAdmin,
        ]);
    }

    public function test_admin_dashboard_has_collapsible_groups_and_theme_switcher(): void
    {
        $response = $this->actingAs($this->admin)->get(AdminPanelConfig::url());

        $response->assertOk();
        $response->assertSee('fi-sidebar-group-collapse-btn', false);
        $response->assertSee('data-group-label', false);
        $response->assertSee('fi-theme-switcher', false);
        $response->assertSee('/build/assets/admin-sidebar-', false);
    }

    public function test_page_editor_loads_rich_editor(): void
    {
        $page = Page::query()->where('slug', 'welcome')->firstOrFail();

        $response = $this->actingAs($this->admin)->get(
            PageResource::getUrl('edit', ['record' => $page]),
        );

        $response->assertOk();
        $response->assertSee('fi-fo-rich-editor', false);
        $response->assertSee('content', false);
    }
}
