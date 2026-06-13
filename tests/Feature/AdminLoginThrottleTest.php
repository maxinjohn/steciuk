<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Http\Middleware\ThrottleAdminLogin;
use App\Models\User;
use App\Support\AdminPanelConfig;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use App\Filament\Auth\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class AdminLoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear(ThrottleAdminLogin::key(request(), 'admin@steciuk.org'));
        $this->clearAdminLoginRateLimiters('admin@steciuk.org');

        config([
            'site.seed.mode' => SeedConfig::MODE_BOOTSTRAP,
            'security.max_login_attempts' => 3,
            'security.login_decay_minutes' => 15,
        ]);

        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_admin_login_locks_out_after_max_failed_attempts(): void
    {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->fillAdminLoginForm(
                Livewire::test(Login::class),
                'admin@steciuk.org',
                'wrong-password-'.$attempt,
            )
                ->call('authenticate')
                ->assertHasFormErrors(['email']);
        }

        $this->assertTrue(ThrottleAdminLogin::isLocked(request(), 'admin@steciuk.org'));

        $this->fillAdminLoginForm(
            Livewire::test(Login::class),
            'admin@steciuk.org',
            'password',
        )
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->get(AdminPanelConfig::url('login'))
            ->assertOk()
            ->assertSee('Too many login attempts', false);
    }

    public function test_successful_admin_login_clears_rate_limiter(): void
    {
        $user = User::factory()->create([
            'email' => 'cleared@steciuk.org',
            'password' => 'password',
            'role' => UserRole::SuperAdmin,
        ]);

        RateLimiter::clear(ThrottleAdminLogin::key(request(), 'cleared@steciuk.org'));

        for ($attempt = 0; $attempt < 2; $attempt++) {
            $this->fillAdminLoginForm(
                Livewire::test(Login::class),
                'cleared@steciuk.org',
                'wrong-password',
            )
                ->call('authenticate')
                ->assertHasFormErrors(['email']);
        }

        $this->fillAdminLoginForm(
            Livewire::test(Login::class),
            'cleared@steciuk.org',
            'password',
        )
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($user);
        $this->assertFalse(ThrottleAdminLogin::isLocked(request(), 'cleared@steciuk.org'));
    }
}
