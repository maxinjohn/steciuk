<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Auth\Login;
use App\Http\Middleware\ThrottleAdminLogin;
use App\Models\User;
use App\Support\AdminPanelConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear(ThrottleAdminLogin::key(request(), 'admin@steciuk.org'));

        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_admin_can_sign_in_with_default_credentials(): void
    {
        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'admin@steciuk.org',
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticated();
    }

    public function test_admin_login_normalizes_email_case_and_whitespace(): void
    {
        Livewire::test(Login::class)
            ->fillForm([
                'email' => '  ADMIN@STECIUK.ORG ',
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs(
            User::query()->where('email', 'admin@steciuk.org')->firstOrFail(),
        );
    }

    public function test_admin_login_shows_clear_message_for_wrong_password(): void
    {
        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'admin@steciuk.org',
                'password' => 'not-the-password',
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);
    }

    public function test_admin_login_denies_accounts_without_panel_access_without_lockout_message(): void
    {
        $blocked = User::factory()->create([
            'email' => 'blocked@steciuk.org',
            'password' => 'password',
            'role' => UserRole::Editor,
        ]);

        \App\Models\Setting::set('role_permissions', [
            UserRole::Editor->value => [
                'admin.access' => false,
            ],
        ], 'security');

        for ($attempt = 0; $attempt < 3; $attempt++) {
            Livewire::test(Login::class)
                ->fillForm([
                    'email' => $blocked->email,
                    'password' => 'password',
                ])
                ->call('authenticate')
                ->assertHasFormErrors(['email'])
                ->assertSee('does not have permission to access the parish admin panel', false);
        }

        $this->assertFalse(ThrottleAdminLogin::isLocked(request(), $blocked->email));
    }

    public function test_site_ensure_admin_creates_or_repairs_primary_admin(): void
    {
        User::query()->where('email', 'admin@steciuk.org')->delete();

        $this->artisan('site:ensure-admin', ['--force' => true, '--reset-password' => true])
            ->assertSuccessful();

        $admin = User::query()->where('email', 'admin@steciuk.org')->firstOrFail();

        $this->assertTrue($admin->isSuperAdmin());
        $this->assertTrue(Hash::check('password', $admin->password));
    }
}
