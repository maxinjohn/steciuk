<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Pages\SiteLaunchSettings;
use App\Models\User;
use App\Services\LaunchModeService;
use App\Support\AdminPanelConfig;
use App\Support\SeedConfig;
use App\Support\SitePathGate;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SiteLaunchSettingsTest extends TestCase
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

    public function test_admin_can_save_launch_gate_list(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(SiteLaunchSettings::class)
            ->set('data.launch_gates', [[
                'id' => SitePathGate::newId('lg'),
                'enabled' => true,
                'label' => 'Liturgy launch',
                'scope' => LaunchModeService::SCOPE_PATH,
                'target_path' => 'liturgy',
                'path_match' => LaunchModeService::MATCH_PREFIX,
                'launch_style' => LaunchModeService::STYLE_RIBBON,
                'countdown_at' => now()->addDays(2)->toDateTimeString(),
                'show_countdown' => true,
                'allow_admin_ribbon' => true,
                'title' => 'Almost ready',
            ]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(LaunchModeService::isGateActive('liturgy'));
    }

    public function test_path_launch_gate_blocks_public_url(): void
    {
        $gate = LaunchModeService::normalizeGate(array_merge(LaunchModeService::defaultGate(), [
            'enabled' => true,
            'scope' => LaunchModeService::SCOPE_PATH,
            'target_path' => 'liturgy',
            'countdown_at' => now()->addDay()->toIso8601String(),
        ]));

        LaunchModeService::saveGates([$gate]);

        $this->get('/liturgy')
            ->assertOk()
            ->assertSee('launch-countdown', false);

        $this->get('/contact')->assertOk()->assertDontSee('launch-countdown', false);
    }

    public function test_launch_settings_page_loads_for_super_admin(): void
    {
        $this->actingAs($this->admin)
            ->get(AdminPanelConfig::url('site-launch'))
            ->assertOk()
            ->assertSee('Countdown list', false)
            ->assertSee('Help', false);
    }
}
