<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use App\Services\LaunchModeService;
use App\Services\MaintenanceModeService;
use App\Support\AdminPanelConfig;
use App\Support\SeedConfig;
use App\Support\SitePathGate;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LaunchModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_site_wide_launch_shows_countdown_page(): void
    {
        $this->enableLaunch([
            'scope' => LaunchModeService::SCOPE_SITE,
            'countdown_at' => now()->addDay()->toIso8601String(),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('launch-countdown', false)
            ->assertSee('data-launch-fullscreen', false)
            ->assertSee('aria-label="Full screen"', false)
            ->assertSee('Something beautiful is on the way', false);
    }

    public function test_path_scoped_launch_blocks_only_matching_url(): void
    {
        $this->enableLaunch([
            'scope' => LaunchModeService::SCOPE_PATH,
            'target_path' => 'events',
            'path_match' => LaunchModeService::MATCH_PREFIX,
            'countdown_at' => now()->addDay()->toIso8601String(),
        ]);

        $this->get('/events')->assertOk()->assertSee('launch-countdown', false);
        $this->get('/news')->assertOk()->assertDontSee('launch-countdown', false);
    }

    public function test_exact_path_match_does_not_block_subpages(): void
    {
        $this->enableLaunch([
            'scope' => LaunchModeService::SCOPE_PATH,
            'target_path' => 'events',
            'path_match' => LaunchModeService::MATCH_EXACT,
            'countdown_at' => now()->addDay()->toIso8601String(),
        ]);

        $this->get('/events')->assertOk()->assertSee('launch-countdown', false);
    }

    public function test_maintenance_mode_takes_priority_over_launch(): void
    {
        $this->enableLaunch([
            'scope' => LaunchModeService::SCOPE_SITE,
            'countdown_at' => now()->addDay()->toIso8601String(),
        ]);

        MaintenanceModeService::saveGates([MaintenanceModeService::normalizeGate([
            'id' => SitePathGate::newId('mg'),
            'enabled' => true,
            'label' => 'Site maintenance',
            'scope' => SitePathGate::SCOPE_SITE,
            'path_match' => SitePathGate::MATCH_PREFIX,
        ])]);

        $this->get('/')
            ->assertStatus(503)
            ->assertSee('Site refresh mode', false)
            ->assertDontSee('launch-countdown', false);
    }

    public function test_admin_preview_bypasses_launch_gate(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->enableLaunch([
            'scope' => LaunchModeService::SCOPE_SITE,
            'countdown_at' => now()->addDay()->toIso8601String(),
        ]);

        $this->actingAs($admin)
            ->get('/?preview=1')
            ->assertOk()
            ->assertDontSee('launch-countdown', false);
    }

    public function test_logged_in_admin_still_sees_launch_gate_without_preview(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->enableLaunch([
            'scope' => LaunchModeService::SCOPE_PATH,
            'target_path' => 'liturgy',
            'countdown_at' => now()->addDay()->toIso8601String(),
        ]);

        $this->actingAs($admin)
            ->get('/liturgy')
            ->assertOk()
            ->assertSee('launch-countdown', false);
    }

    public function test_auto_launch_style_lifts_gate_when_countdown_expires(): void
    {
        Carbon::setTestNow(now());

        $this->enableLaunch([
            'scope' => LaunchModeService::SCOPE_SITE,
            'launch_style' => LaunchModeService::STYLE_COUNTDOWN,
            'countdown_at' => now()->subMinute()->toIso8601String(),
        ]);

        $this->get('/')->assertOk()->assertDontSee('launch-countdown', false);
        $this->assertFalse(LaunchModeService::isGateActive('/'));
    }

    public function test_ribbon_launch_shows_ceremony_without_countdown_for_visitors(): void
    {
        $this->enableLaunch([
            'scope' => LaunchModeService::SCOPE_PATH,
            'target_path' => 'events',
            'launch_style' => LaunchModeService::STYLE_RIBBON,
            'countdown_at' => '',
        ]);

        $this->get('/events')
            ->assertOk()
            ->assertSee('launch-ribbon-screen', false)
            ->assertSee('data-launch-fullscreen', false)
            ->assertSee('aria-label="Full screen"', false)
            ->assertSee('parish team member', false)
            ->assertDontSee('data-launch-ribbon-cut', false)
            ->assertDontSee('data-launch-countdown', false);
    }

    public function test_admin_sees_ribbon_cut_on_gate_page(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->enableLaunch([
            'scope' => LaunchModeService::SCOPE_PATH,
            'target_path' => 'events',
            'launch_style' => LaunchModeService::STYLE_RIBBON,
            'countdown_at' => '',
        ]);

        $this->actingAs($admin)
            ->get('/events')
            ->assertOk()
            ->assertSee('data-launch-ribbon-cut', false);
    }

    public function test_public_cannot_cut_ribbon(): void
    {
        $gate = LaunchModeService::normalizeGate(array_merge(LaunchModeService::defaultGate(), [
            'enabled' => true,
            'scope' => LaunchModeService::SCOPE_PATH,
            'target_path' => 'events',
            'launch_style' => LaunchModeService::STYLE_RIBBON,
            'countdown_at' => '',
        ]));

        LaunchModeService::saveGates([$gate]);

        $token = 'test-csrf-token';

        $this->withSession(['_token' => $token])
            ->from('/events')
            ->post(route('launch.cut-ribbon'), [
                '_token' => $token,
                'gate_id' => $gate['id'],
            ])
            ->assertForbidden();

        $this->assertTrue(LaunchModeService::isGateActive('events'));
    }

    public function test_admin_ribbon_cut_launches_path(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $gate = LaunchModeService::normalizeGate(array_merge(LaunchModeService::defaultGate(), [
            'enabled' => true,
            'scope' => LaunchModeService::SCOPE_PATH,
            'target_path' => 'events',
            'launch_style' => LaunchModeService::STYLE_RIBBON,
            'countdown_at' => '',
        ]));

        LaunchModeService::saveGates([$gate]);

        $token = 'test-csrf-token';

        $this->actingAs($admin)
            ->withSession(['_token' => $token])
            ->from('/events')
            ->post(route('launch.cut-ribbon'), [
                '_token' => $token,
                'gate_id' => $gate['id'],
            ])
            ->assertRedirect();

        $this->assertFalse(LaunchModeService::isGateActive('events'));
    }

    public function test_countdown_launch_does_not_show_ribbon_ceremony(): void
    {
        $this->enableLaunch([
            'scope' => LaunchModeService::SCOPE_PATH,
            'target_path' => 'events',
            'launch_style' => LaunchModeService::STYLE_COUNTDOWN,
            'countdown_at' => now()->addDay()->toIso8601String(),
        ]);

        $this->get('/events')
            ->assertOk()
            ->assertSee('launch-countdown', false)
            ->assertDontSee('launch-ribbon-screen', false);
    }

    public function test_launch_page_renders_selected_theme(): void
    {
        $this->enableLaunch([
            'scope' => LaunchModeService::SCOPE_SITE,
            'launch_style' => LaunchModeService::STYLE_COUNTDOWN,
            'theme' => LaunchModeService::THEME_NEON,
            'countdown_at' => now()->addDay()->toIso8601String(),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('launch-page--theme-neon', false);
    }

    public function test_admin_can_cut_ribbon_early(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $gate = LaunchModeService::normalizeGate(array_merge(LaunchModeService::defaultGate(), [
            'enabled' => true,
            'scope' => LaunchModeService::SCOPE_SITE,
            'countdown_at' => now()->addDay()->toIso8601String(),
        ]));

        LaunchModeService::saveGates([$gate]);

        $token = 'test-csrf-token';

        $this->actingAs($admin)
            ->withSession(['_token' => $token])
            ->post(route('launch.cut-ribbon'), [
                '_token' => $token,
                'gate_id' => $gate['id'],
            ])
            ->assertRedirect();

        $this->assertFalse(LaunchModeService::isGateActive('/'));
    }

    public function test_admin_and_health_check_remain_available_during_launch(): void
    {
        $this->enableLaunch([
            'scope' => LaunchModeService::SCOPE_SITE,
            'countdown_at' => now()->addDay()->toIso8601String(),
        ]);

        $this->get('/up')->assertOk();
        $this->get(AdminPanelConfig::url('login'))->assertOk();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function enableLaunch(array $overrides = []): void
    {
        $gate = LaunchModeService::normalizeGate(array_merge(LaunchModeService::defaultGate(), [
            'enabled' => true,
            'label' => 'Test launch',
        ], $overrides));

        LaunchModeService::saveGates([$gate]);
    }
}
