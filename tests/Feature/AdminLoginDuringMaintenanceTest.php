<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Auth\Login;
use App\Models\User;
use App\Services\MaintenanceModeService;
use App\Support\AdminPanelConfig;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminLoginDuringMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);
        $this->clearAdminLoginRateLimiters('admin@steciuk.org');
        MaintenanceModeService::enable();
    }

    public function test_admin_login_page_loads_during_maintenance(): void
    {
        $this->get(AdminPanelConfig::url('login'))
            ->assertOk()
            ->assertSee('Sign in', false);
    }

    public function test_admin_can_authenticate_during_maintenance(): void
    {
        $this->fillAdminLoginForm(
            Livewire::test(Login::class),
            'admin@steciuk.org',
            'password',
        )
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticated();
    }

    public function test_admin_dashboard_loads_after_login_during_maintenance(): void
    {
        $admin = User::query()->where('email', 'admin@steciuk.org')->firstOrFail();

        $this->actingAs($admin)
            ->get(AdminPanelConfig::url())
            ->assertOk()
            ->assertSee('Welcome', false);
    }

    public function test_maintenance_off_command_restores_public_site(): void
    {
        $this->artisan('site:maintenance off')->assertSuccessful();

        $this->assertFalse(MaintenanceModeService::isEnabled());
        $this->get('/')->assertOk();
    }
}
