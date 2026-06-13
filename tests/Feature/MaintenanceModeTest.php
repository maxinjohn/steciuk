<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\MaintenanceModeService;
use App\Support\AdminPanelConfig;
use App\Support\SeedConfig;
use App\Support\SitePathGate;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_public_site_returns_503_when_maintenance_enabled(): void
    {
        MaintenanceModeService::enable();

        $this->get('/')->assertStatus(503);
        $this->get('/events')->assertStatus(503);
    }

    public function test_admin_and_health_check_remain_available_during_maintenance(): void
    {
        MaintenanceModeService::enable();

        $this->get('/up')->assertOk();
        $this->get(AdminPanelConfig::url('login'))->assertOk();
    }

    public function test_maintenance_message_is_shown_to_visitors(): void
    {
        Setting::set('maintenance_mode_message', 'Parish site refresh in progress.');
        MaintenanceModeService::enable();

        $response = $this->get('/');

        $response->assertStatus(503);
        $response->assertSee('Parish site refresh in progress.', false);
        $response->assertSee('Site refresh mode', false);
    }

    public function test_service_times_button_hidden_when_no_active_services(): void
    {
        \App\Models\Service::query()->update(['status' => 'inactive']);

        MaintenanceModeService::enable();

        $this->get('/')
            ->assertStatus(503)
            ->assertDontSee('Service times', false);
    }

    public function test_service_times_page_stays_reachable_during_maintenance_when_services_exist(): void
    {
        MaintenanceModeService::enable();

        $this->get('/')->assertStatus(503)->assertSee('Service times', false);
        $this->get('/service-times')->assertOk()->assertSee('Holy Communion', false);
    }

    public function test_custom_service_times_url_is_used_on_maintenance_page(): void
    {
        Setting::set('maintenance_mode_service_times_url', 'https://example.com/worship');
        MaintenanceModeService::enable();

        $this->get('/')
            ->assertStatus(503)
            ->assertSee('https://example.com/worship', false);
    }

    public function test_path_scoped_maintenance_blocks_only_matching_url(): void
    {
        MaintenanceModeService::saveGates([MaintenanceModeService::normalizeGate([
            'id' => SitePathGate::newId('mg'),
            'enabled' => true,
            'label' => 'Liturgy refresh',
            'scope' => SitePathGate::SCOPE_PATH,
            'target_path' => 'liturgy',
            'path_match' => SitePathGate::MATCH_PREFIX,
        ])]);

        $this->get('/liturgy')->assertStatus(503);
        $this->get('/contact')->assertOk();
    }

    public function test_public_site_works_when_maintenance_disabled(): void
    {
        MaintenanceModeService::disable();

        $this->get('/')->assertOk();
    }
}
