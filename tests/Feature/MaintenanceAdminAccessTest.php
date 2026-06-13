<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\MaintenanceModeService;
use App\Support\AdminPanelConfig;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_admin_dashboard_renders_during_maintenance(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        MaintenanceModeService::enable();

        $this->actingAs($admin)
            ->get(AdminPanelConfig::url())
            ->assertOk()
            ->assertSee('Welcome', false);
    }

    public function test_admin_maintenance_settings_renders_during_maintenance(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        MaintenanceModeService::enable();

        $this->actingAs($admin)
            ->get(AdminPanelConfig::url('site-maintenance'))
            ->assertOk()
            ->assertSee('Maintenance list', false);
    }

    public function test_admin_login_livewire_bypasses_maintenance_middleware(): void
    {
        MaintenanceModeService::enable();

        $request = \Illuminate\Http\Request::create('/livewire/update', 'POST', [
            'components' => [
                [
                    'snapshot' => json_encode(['memo' => ['name' => 'app.filament.auth.login']]),
                    'calls' => [],
                ],
            ],
        ]);
        $request->headers->set('X-Livewire', '');

        $middleware = new \App\Http\Middleware\CheckSiteMaintenance;
        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertSame('ok', $response->getContent());
    }
}
