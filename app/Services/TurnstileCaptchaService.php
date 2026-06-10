<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class TurnstileCaptchaService
{
    public function isEnabled(): bool
    {
        if (! (bool) config('services.turnstile.enabled', true)) {
            return false;
        }

        return Setting::get('registration_captcha_enabled', '1') !== '0';
    }

    public function siteKey(): string
    {
        return (string) config('services.turnstile.site_key');
    }

    public function verify(?string $token, ?string $remoteIp = null): bool
    {
        if (! $this->isEnabled()) {
            return true;
        }

        if (blank($token)) {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => config('services.turnstile.secret_key'),
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ]);
        } catch (\Throwable) {
            return false;
        }

        if (! $response->successful()) {
            return false;
        }

        return (bool) $response->json('success');
    }
}
