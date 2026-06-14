<?php

namespace Tests\Feature;

use App\Livewire\Auth\LoginForm;
use App\Models\Setting;
use App\Models\User;
use App\Support\AdminPanelConfig;
use App\Http\Middleware\ThrottleAdminLogin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class AdminMemberAuthIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear(ThrottleAdminLogin::key(request(), 'member@example.com'));
    }

    public function test_member_login_failures_do_not_lock_admin_login_throttle(): void
    {
        User::factory()->create([
            'email' => 'member@example.com',
            'password' => 'password',
            'role' => \App\Enums\UserRole::Member,
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            Livewire::test(LoginForm::class)
                ->set('email', 'member@example.com')
                ->set('password', 'wrong-password')
                ->call('login')
                ->assertHasErrors(['email']);
        }

        $this->assertFalse(ThrottleAdminLogin::isLocked(request(), 'member@example.com'));
    }

    public function test_admin_session_timeout_honours_saved_setting(): void
    {
        Setting::set('admin_session_lifetime_minutes', '60', 'security');

        $user = User::factory()->create(['role' => \App\Enums\UserRole::SuperAdmin]);

        $response = $this->actingAs($user)
            ->withSession(['admin_last_activity' => now()->subMinutes(61)->timestamp])
            ->get(AdminPanelConfig::url());

        $response->assertRedirect(AdminPanelConfig::url('login').'?expired=1');
        $this->assertGuest();
    }
}
