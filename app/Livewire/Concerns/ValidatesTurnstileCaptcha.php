<?php

namespace App\Livewire\Concerns;

use App\Enums\FormType;
use App\Rules\TurnstileCaptcha;
use App\Services\TurnstileCaptchaService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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

    /**
     * @param  array<string, mixed>  $rules
     * @param  array<string, string>  $messages
     * @param  array<string, string>  $attributes
     * @return array<string, mixed>
     */
    protected function validateWithTurnstileReset(string $elementId, array $rules, array $messages = [], array $attributes = []): array
    {
        try {
            return $this->validate($rules, $messages, $attributes);
        } catch (ValidationException $exception) {
            $this->resetTurnstileCaptcha($elementId);

            throw $exception;
        }
    }

    protected function turnstileViewData(): array
    {
        $service = app(TurnstileCaptchaService::class);

        return [
            'turnstileEnabled' => $service->isEnabled(),
            'turnstileSiteKey' => $service->siteKey(),
        ];
    }

    protected function resetTurnstileCaptcha(string $elementId): void
    {
        if (! app(TurnstileCaptchaService::class)->isEnabled()) {
            return;
        }

        $this->captchaToken = '';
        $this->dispatch('turnstile-reset', elementId: $elementId);
    }

    protected function turnstileElementId(): ?string
    {
        if (! method_exists($this, 'formType')) {
            return null;
        }

        $type = $this->formType();

        if (! $type instanceof FormType) {
            return null;
        }

        return match ($type) {
            FormType::Contact => 'turnstile-contact',
            FormType::PrayerRequest => 'turnstile-prayer',
            FormType::NewMember => 'turnstile-new-member',
            FormType::EventEnquiry => 'turnstile-event-enquiry',
            FormType::Volunteer => 'turnstile-volunteer',
        };
    }
}
