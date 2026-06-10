<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\AdminPanelConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AdminSessionTimeoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ReferenceDataSeeder::class);
        Config::set('security.session_lifetime_admin', 1);
    }

    public function test_expired_admin_session_redirects_to_login_with_expired_flag(): void
    {
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $response = $this->actingAs($user)
            ->withSession(['admin_last_activity' => now()->subMinutes(5)->timestamp])
            ->get(AdminPanelConfig::url());

        $response->assertRedirect(AdminPanelConfig::url('login').'?expired=1');
        $this->assertGuest();
    }

    public function test_expired_admin_livewire_session_returns_json_reload(): void
    {
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $response = $this->actingAs($user)
            ->withSession(['admin_last_activity' => now()->subMinutes(5)->timestamp])
            ->withHeaders(['X-Livewire' => ''])
            ->post('/livewire/update', [
                '_token' => csrf_token(),
                'components' => [
                    [
                        'snapshot' => json_encode(['memo' => ['name' => 'app.filament.pages.dashboard']]),
                        'calls' => [],
                    ],
                ],
            ]);

        $response->assertStatus(419);
        $response->assertJson([
            'message' => 'Page expired.',
            'reload' => true,
        ]);
        $this->assertGuest();
    }

    public function test_admin_request_sets_admin_last_activity(): void
    {
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $response = $this->actingAs($user)->get(AdminPanelConfig::url());

        $response->assertSessionHas('admin_last_activity');
    }

    public function test_middleware_skips_expiry_check_when_activity_missing(): void
    {
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $response = $this->actingAs($user)->get(AdminPanelConfig::url());

        $response->assertOk();
        $response->assertSessionHas('admin_last_activity');
    }
}
