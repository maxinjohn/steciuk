<?php

namespace Tests\Feature;

use App\Livewire\Auth\ForgotPasswordForm;
use App\Livewire\Auth\LoginForm;
use App\Livewire\Auth\RegisterForm;
use App\Livewire\Auth\ResetPasswordForm;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AuthTurnstileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ReferenceDataSeeder::class);
        $this->enableTurnstile();
    }

    public function test_login_requires_captcha_when_turnstile_is_enabled(): void
    {
        Livewire::test(LoginForm::class)
            ->set('email', 'member@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors(['captchaToken']);
    }

    public function test_login_accepts_valid_captcha_token_when_turnstile_is_enabled(): void
    {
        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => true]),
        ]);

        $user = User::factory()->create([
            'email' => 'member@example.com',
            'password' => bcrypt('password'),
        ]);

        Livewire::test(LoginForm::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->set('captchaToken', 'valid-token')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('account'));
    }

    public function test_forgot_password_requires_captcha_when_turnstile_is_enabled(): void
    {
        Livewire::test(ForgotPasswordForm::class)
            ->set('email', 'member@example.com')
            ->call('sendResetLink')
            ->assertHasErrors(['captchaToken']);
    }

    public function test_reset_password_requires_captcha_when_turnstile_is_enabled(): void
    {
        Livewire::test(ResetPasswordForm::class, ['token' => 'reset-token'])
            ->set('email', 'member@example.com')
            ->set('password', 'NewPassword1!')
            ->set('password_confirmation', 'NewPassword1!')
            ->call('resetPassword')
            ->assertHasErrors(['captchaToken']);
    }

    public function test_register_requires_captcha_when_turnstile_is_enabled(): void
    {
        Livewire::test(RegisterForm::class)
            ->set('first_name', 'Parish')
            ->set('email', 'member@example.com')
            ->call('register')
            ->assertHasErrors(['captchaToken']);
    }

    private function enableTurnstile(): void
    {
        config(['services.turnstile.enabled' => true]);
        Setting::set('registration_captcha_enabled', '1', 'security');
    }
}
