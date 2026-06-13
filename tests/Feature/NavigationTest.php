<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\User;
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
            $response->assertSee('site-logo--parish-full', false);
            $response->assertSee('steci-parish-logo', false);
            $response->assertSee('site-logo-mark--parish-full', false);
            $response->assertSee('St. Thomas Evangelical Church of India', false);
            $response->assertSee('UK Parish', false);
            $response->assertSee('gospel-reminder', false);
            $response->assertSee('sanctuary-peace', false);
            $response->assertSee('faith-pillars--footer', false);
            $response->assertSee('eauk-trust-mark', false);
            $response->assertDontSee('eauk-member-badge', false);
            $response->assertDontSee('eauk-member-ribbon', false);
            $response->assertSee('images/eauk/member-logo-medium.png', false);
            $response->assertSee('Member of the Evangelical Alliance', false);
        }
    }

    public function test_home_includes_faith_whispers_and_spark_strip(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('faith-whispers', false)
            ->assertSee('faith-spark-strip', false)
            ->assertSee('Anchored in Christ', false)
            ->assertSee('Faith for the journey', false);
    }

    public function test_footer_shows_single_eauk_trust_mark_in_about_column(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $response = $this->get('/');
        $html = (string) $response->getContent();

        $response->assertOk()
            ->assertSee('Member of the Evangelical Alliance', false)
            ->assertSee('eauk-trust-mark', false)
            ->assertSee('Word, worship, and witness across the United Kingdom.', false)
            ->assertDontSee('View our church profile', false)
            ->assertDontSee('eauk-member-badge', false)
            ->assertDontSee('eauk-member-ribbon', false)
            ->assertDontSee('Evangelical Alliance member church', false)
            ->assertSee('https://www.eauk.org/churches/st-thomas-evangelical-church-of-india-uk-parish', false);

        $this->assertSame(2, preg_match_all('/class="[^"]*\beauk-trust-mark\b/', $html));
        $this->assertLessThan(
            strpos($html, 'faith-pillars--footer'),
            strpos($html, 'eauk-trust-mark'),
        );
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
        $response->assertDontSee('Membership enquiry', false);
        $response->assertDontSee('>Register<', false);
        $response->assertDontSee('>New Member<', false);
        $this->assertDatabaseHas('menu_items', ['seed_key' => 'member-area', 'label' => 'Member area']);
        $this->assertDatabaseMissing('menu_items', ['seed_key' => 'contact.register']);
        $this->assertDatabaseMissing('menu_items', ['seed_key' => 'contact.new-member']);
    }

    public function test_legacy_new_member_url_redirects_to_register(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $this->get('/new-member')
            ->assertRedirect(route('register'));
    }

    public function test_authenticated_header_includes_contact_menu_and_body_class(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $member = User::factory()->create([
            'role' => UserRole::Member,
            'account_status' => AccountStatus::Approved->value,
        ]);

        $this->actingAs($member)
            ->get(route('account'))
            ->assertOk()
            ->assertSee('is-authenticated', false)
            ->assertSee('data-menu-item', false)
            ->assertSee('>Contact<', false)
            ->assertSee('aria-label="Account"', false);
    }

    public function test_member_account_parish_links_exclude_membership_enquiry(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $member = User::factory()->create([
            'role' => UserRole::Member,
            'account_status' => AccountStatus::Approved->value,
        ]);

        $this->actingAs($member)
            ->get(route('account'))
            ->assertOk()
            ->assertDontSee('Membership enquiry', false)
            ->assertDontSee('/new-member', false);
    }

    public function test_guest_header_hides_member_chip_on_mobile(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('site-member-chip hidden min-[1300px]:block', false)
            ->assertSee('data-close-mobile-menu', false)
            ->assertSee('mobile-drawer-footer', false)
            ->assertSee('mobile-drawer-auth-link', false);
    }

    public function test_mobile_menu_uses_vanilla_navigation_markup(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('id="mobile-menu"', false);
        $response->assertSee('mobile-drawer', false);
        $response->assertSee('mobile-drawer-footer', false);
        $response->assertSee('mobile-drawer-auth-link', false);
        $response->assertSee('data-mobile-nav-trigger', false);
        $response->assertSee('data-mobile-nav-panel', false);
        $response->assertSee('data-menu-panel', false);
        $response->assertSee('data-close-mobile-menu', false);
        $response->assertSee('min-[1300px]:hidden', false);
        $response->assertSee('mobile-dock-wrap min-[1300px]:hidden', false);
        $response->assertDontSee('mobile-quick-grid', false);
        $response->assertDontSee('mobile-theme-toggle', false);
        $response->assertDontSee('x-data="{ mobileOpen: false }"', false);

        $mobileMenu = $this->extractMobileMenuHtml($response->getContent());
        $navList = $this->extractMobileNavListHtml($response->getContent());
        $this->assertStringNotContainsString('Member area', $navList);
        $this->assertStringContainsString('Sign in', $mobileMenu);
        $this->assertStringContainsString('Join parish', $mobileMenu);
    }

    private function extractMobileMenuHtml(string $html): string
    {
        $start = strpos($html, 'id="mobile-menu"');

        if ($start === false) {
            return '';
        }

        $end = strpos($html, '</nav>', $start);

        return $end === false ? substr($html, $start) : substr($html, $start, $end - $start);
    }

    private function extractMobileNavListHtml(string $html): string
    {
        $mobileMenu = $this->extractMobileMenuHtml($html);
        $start = strpos($mobileMenu, 'class="mobile-nav-list"');

        if ($start === false) {
            return $mobileMenu;
        }

        $end = strpos($mobileMenu, '</div>', $start);

        return $end === false ? substr($mobileMenu, $start) : substr($mobileMenu, $start, $end - $start);
    }

    private function extractDesktopNavHtml(string $html): string
    {
        $start = strpos($html, 'class="desktop-nav-list"');

        if ($start === false) {
            return '';
        }

        $end = strpos($html, '</ul>', $start);

        return $end === false ? substr($html, $start) : substr($html, $start, $end - $start);
    }

    public function test_desktop_submenu_panels_render_for_nested_menu_items(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('desktop-nav-dock', false);
        $response->assertSee('desktop-nav-drop', false);
        $response->assertSee('menu-dropdown-panel', false);

        $desktopNav = $this->extractDesktopNavHtml($response->getContent());
        $this->assertStringNotContainsString('Member area', $desktopNav);
        $response->assertSee('Welcome', false);
        $response->assertSee('Service Times', false);
        $response->assertSee('Join the parish', false);
    }
}
