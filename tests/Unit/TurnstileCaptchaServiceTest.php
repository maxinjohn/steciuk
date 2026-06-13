<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Services\TurnstileCaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TurnstileCaptchaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_is_disabled_when_env_turnstile_enabled_is_false(): void
    {
        config(['services.turnstile.enabled' => false]);
        Setting::set('registration_captcha_enabled', '1', 'security');

        $this->assertFalse(app(TurnstileCaptchaService::class)->isEnabled());
    }

    public function test_it_is_disabled_when_admin_setting_is_off(): void
    {
        config(['services.turnstile.enabled' => true]);
        Setting::set('registration_captcha_enabled', '0', 'security');

        $this->assertFalse(app(TurnstileCaptchaService::class)->isEnabled());
    }

    public function test_it_is_disabled_when_site_keys_are_missing(): void
    {
        config([
            'services.turnstile.enabled' => true,
            'services.turnstile.site_key' => '',
            'services.turnstile.secret_key' => '',
        ]);
        Setting::set('registration_captcha_enabled', '1', 'security');

        $this->assertFalse(app(TurnstileCaptchaService::class)->isEnabled());
    }

    public function test_it_is_enabled_when_env_and_admin_setting_allow_it(): void
    {
        config(['services.turnstile.enabled' => true]);
        Setting::set('registration_captcha_enabled', '1', 'security');

        $this->assertTrue(app(TurnstileCaptchaService::class)->isEnabled());
    }
}
