<?php

namespace App\Rules;

use App\Services\TurnstileCaptchaService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TurnstileCaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $service = app(TurnstileCaptchaService::class);

        if (! $service->isEnabled()) {
            return;
        }

        if (! $service->verify(is_string($value) ? $value : null, request()->ip())) {
            $fail('Please complete the security check to continue.');
        }
    }
}
