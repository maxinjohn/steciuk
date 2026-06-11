<?php

namespace Tests\Feature;

use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_desktop_menu_markup_and_logo_on_internal_pages(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        foreach (['/events', '/ministries', '/news', '/service-times'] as $path) {
            $response = $this->get($path);

            $response->assertOk();
            $response->assertSee('data-menu-item', false);
            $response->assertSee('data-menu-trigger', false);
            $response->assertSee('images/steci-mark.svg', false);
            $response->assertSee('UK Parish', false);
            $response->assertSee('gospel-reminder', false);
            $response->assertSee('sanctuary-peace', false);
            $response->assertSee('faith-pillars--footer', false);
        }
    }

    public function test_home_location_tabs_render_with_single_visible_panel(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('data-location-tabs', false);
        $response->assertSee('data-location-tab', false);
        $response->assertSee('Manchester', false);
        $response->assertSee('Monthly worship service', false);
        $response->assertSee('data-location-panel', false);

        $html = $response->getContent();
        $panelCount = substr_count($html, 'data-location-panel');
        $hiddenPanelCount = preg_match_all('/data-location-panel[^>]*\shidden/', $html);

        $this->assertGreaterThanOrEqual(5, $panelCount);
        $this->assertSame($panelCount - 1, $hiddenPanelCount);
    }

    public function test_member_area_menu_is_separate_from_contact(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Member area', false);
        $response->assertSee('Join the parish', false);
        $response->assertSee('Membership enquiry', false);
        $response->assertDontSee('>Register<', false);
        $response->assertDontSee('>New Member<', false);
        $this->assertDatabaseHas('menu_items', ['seed_key' => 'member-area', 'label' => 'Member area']);
        $this->assertDatabaseMissing('menu_items', ['seed_key' => 'contact.register']);
        $this->assertDatabaseMissing('menu_items', ['seed_key' => 'contact.new-member']);
    }

    public function test_guest_header_shows_member_chip_on_mobile(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-member-chip', false)
            ->assertSee('site-member-chip-label', false);
    }

    public function test_mobile_menu_uses_vanilla_navigation_markup(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('id="mobile-menu"', false);
        $response->assertSee('data-mobile-nav-trigger', false);
        $response->assertSee('data-close-mobile-menu', false);
        $response->assertDontSee('mobile-theme-toggle', false);
        $response->assertDontSee('x-data="{ mobileOpen: false }"', false);
    }
}
