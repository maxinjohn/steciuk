<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Pages\PageResource;
use App\Models\Page;
use App\Models\Setting;
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

    public function test_admin_dashboard_renders_mobile_bottom_dock(): void
    {
        $response = $this->actingAs($this->admin)->get(AdminPanelConfig::url());

        $response->assertOk();
        $response->assertSee('admin-dock-wrap', false);
        $response->assertSee('admin-dock-item__icon', false);
        $response->assertSee('admin-dock-label', false);
        $response->assertSee('aria-label="Quick admin navigation"', false);
        $response->assertSee('Home', false);
        $response->assertSee('Worship', false);
        $response->assertSee('Events', false);
        $response->assertSee('Menu', false);
    }

    public function test_admin_login_page_does_not_render_mobile_dock(): void
    {
        $this->get(AdminPanelConfig::loginPath())
            ->assertOk()
            ->assertDontSee('admin-dock-wrap', false);
    }

    public function test_admin_login_page_does_not_preload_form_tab_script(): void
    {
        $response = $this->get(AdminPanelConfig::loginPath());

        $response->assertOk();
        $response->assertDontSee('admin-form-tabs-', false);
    }

    public function test_settings_pages_use_horizontal_form_tabs(): void
    {
        $response = $this->actingAs($this->admin)->get(AdminPanelConfig::url('church-settings'));

        $response->assertOk();
        $response->assertSee('admin-form-tabs', false);
        $response->assertDontSee('admin-form-tabs fi-sc-tabs fi-vertical', false);
        $response->assertSee('Identity', false);
        $response->assertSee('Faith copy', false);
    }

    public function test_mobile_admin_hides_top_sidebar_toggle(): void
    {
        $response = $this->actingAs($this->admin)->get(AdminPanelConfig::url('church-settings'));

        $response->assertOk();
        $response->assertSee('fi-topbar-open-sidebar-btn', false);
        $response->assertSee('admin-dock-wrap', false);
        $response->assertSee('/build/assets/theme-', false);
    }

    public function test_admin_welcome_widget_renders_readable_copy_when_verse_is_blank(): void
    {
        Setting::set('admin_dashboard_verse', '', 'admin');
        Setting::set('admin_dashboard_verse_ref', '', 'admin');
        Setting::forgetCache();

        $response = $this->actingAs($this->admin)->get(AdminPanelConfig::url());

        $response->assertOk();
        $response->assertSee('admin-sanctuary-banner', false);
        $response->assertSee('admin-sanctuary-title', false);
        $response->assertSee('Be still, and know that I am God.', false);
        $response->assertSee('Psalm 46:10', false);
    }

    public function test_admin_pages_do_not_load_external_bunny_font_stylesheet(): void
    {
        $response = $this->actingAs($this->admin)->get(AdminPanelConfig::url());

        $response->assertOk();
        $response->assertDontSee('fonts.bunny.net/css', false);
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
