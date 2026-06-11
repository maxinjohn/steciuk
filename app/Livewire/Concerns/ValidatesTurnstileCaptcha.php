<?php

namespace App\Livewire\Concerns;

use App\Rules\TurnstileCaptcha;
use App\Services\TurnstileCaptchaService;
use Illuminate\Validation\Rule;

trait ValidatesTurnstileCaptcha
{
    public string $captchaToken = '';

    protected function turnstileValidationRules(): array
    {
        if (! app(TurnstileCaptchaService::class)->isEnabled()) {
            return [];
        }

        return [
            'captchaToken' => [
                Rule::requiredIf(fn (): bool => app(TurnstileCaptchaService::class)->isEnabled()),
                new TurnstileCaptcha,
            ],
        ];
    }

    protected function turnstileViewData(): array
    {
        $service = app(TurnstileCaptchaService::class);

        return [
            'turnstileEnabled' => $service->isEnabled(),
            'turnstileSiteKey' => $service->siteKey(),
        ];
    }
}
