<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Http\Middleware\CheckSiteMaintenance;
use App\Models\Setting;
use App\Models\User;
use App\Support\AdminPanelConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AdminPanelConfigTest extends TestCase
{
    use RefreshDatabase;
    public function test_filament_core_livewire_names_are_recognised(): void
    {
        $this->assertTrue(AdminPanelConfig::isFilamentLivewireName('filament.livewire.sidebar'));
        $this->assertTrue(AdminPanelConfig::isFilamentLivewireName('app.filament.pages.dashboard'));
        $this->assertFalse(AdminPanelConfig::isFilamentLivewireName('app.livewire.forms.contact-form'));
    }

    public function test_should_track_admin_session_for_filament_sidebar_livewire(): void
    {
        $request = Request::create('/livewire/update', 'POST', [
            'components' => [
                [
                    'snapshot' => json_encode(['memo' => ['name' => 'filament.livewire.sidebar']]),
                    'calls' => [],
                ],
            ],
        ]);
        $request->headers->set('X-Livewire', '');

        $this->actingAs(\App\Models\User::factory()->make());

        $this->assertTrue(AdminPanelConfig::shouldTrackAdminSession($request));
    }

    public function test_referer_is_admin_panel_without_trailing_slash(): void
    {
        $this->assertTrue(AdminPanelConfig::refererIsAdminPanel('http://localhost/admin'));
        $this->assertTrue(AdminPanelConfig::refererIsAdminPanel('http://localhost/admin/site-maintenance'));
        $this->assertFalse(AdminPanelConfig::refererIsAdminPanel('http://localhost/'));
    }

    public function test_livewire_from_admin_referer_bypasses_maintenance_without_auth(): void
    {
        Setting::set('maintenance_mode_enabled', '1', 'general');

        $request = Request::create('/livewire/update', 'POST', [
            'components' => [
                [
                    'snapshot' => json_encode(['memo' => ['name' => 'filament.livewire.sidebar']]),
                    'calls' => [],
                ],
            ],
        ]);
        $request->headers->set('X-Livewire', '');
        $request->headers->set('Referer', 'http://localhost/admin');

        $middleware = new CheckSiteMaintenance;
        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertSame('ok', $response->getContent());
    }

    public function test_maintenance_traffic_bypass_does_not_skip_public_pages_for_admins(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $request = Request::create('/', 'GET');
        $this->actingAs($admin);

        $this->assertFalse(AdminPanelConfig::shouldBypassMaintenanceTraffic($request));
        $this->assertTrue(AdminPanelConfig::shouldBypassAdminTraffic($request));
    }

    public function test_is_admin_livewire_request_detects_filament_login_snapshot(): void
    {
        $request = Request::create('/livewire/update', 'POST', [
            'components' => [
                [
                    'snapshot' => json_encode(['memo' => ['name' => 'app.filament.auth.login']]),
                    'calls' => [],
                ],
            ],
        ]);

        $this->assertTrue(AdminPanelConfig::isAdminLivewireRequest($request));
        $this->assertTrue(AdminPanelConfig::shouldBypassAdminTraffic($request));
    }

    public function test_should_track_admin_login_livewire_without_auth(): void
    {
        $request = Request::create('/livewire/update', 'POST', [
            'components' => [
                [
                    'snapshot' => json_encode(['memo' => ['name' => 'app.filament.auth.login']]),
                    'calls' => [],
                ],
            ],
        ]);
        $request->headers->set('X-Livewire', '');

        $this->assertTrue(AdminPanelConfig::shouldTrackAdminSession($request));
    }
}
