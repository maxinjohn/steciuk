<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\MaintenanceModeService;
use App\Support\AdminPanelConfig;
use App\Support\SeedConfig;
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

        $this->get('/')->assertSee('Parish site refresh in progress.', false);
    }

    public function test_public_site_works_when_maintenance_disabled(): void
    {
        MaintenanceModeService::disable();

        $this->get('/')->assertOk();
    }
}
