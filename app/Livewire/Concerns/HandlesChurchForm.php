<?php

namespace App\Livewire\Concerns;

use App\Enums\FormType;
use App\Models\FormSubmission;
use App\Models\Setting;
use App\Models\User;
use App\Services\MailConfigService;
use App\Services\ParishConversationService;
use App\Services\SecurityLogger;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Validate;

trait HandlesChurchForm
{
    #[Validate('nullable|string|max:0')]
    public string $website = '';

    public bool $submitted = false;

    protected function formType(): FormType
    {
        return FormType::Contact;
    }

    protected function validationRules(): array
    {
        return [];
    }

    protected function formData(): array
    {
        return [];
    }

    public function submit(): void
    {
        if ($this->website !== '') {
            SecurityLogger::warning('honeypot_triggered', null, [
                'type' => $this->formType()->value,
                'form' => str($this->formType()->value)->headline()->toString(),
                'portal' => SecurityLogger::detectPortal(),
                'ip' => request()->ip(),
            ]);

            return;
        }

        $key = 'form:'.$this->formType()->value.':'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            SecurityLogger::warning('form_rate_limited', null, [
                'type' => $this->formType()->value,
                'form' => str($this->formType()->value)->headline()->toString(),
                'portal' => SecurityLogger::detectPortal(),
                'ip' => request()->ip(),
            ]);

            $this->addError('form', 'Too many submissions. Please try again later.');

            return;
        }

        $rules = array_merge(
            $this->validationRules(),
            $this->captchaValidationRules(),
            ['website' => 'nullable|string|max:0'],
        );

        $captchaRules = $this->captchaValidationRules();
        $elementId = method_exists($this, 'turnstileElementId') ? $this->turnstileElementId() : null;

        if (
            $captchaRules !== []
            && $elementId !== null
            && method_exists($this, 'validateWithTurnstileReset')
        ) {
            $this->validateWithTurnstileReset($elementId, $rules);
        } else {
            $this->validate($rules);
        }

        RateLimiter::hit($key, 3600);

        $submission = FormSubmission::query()->create([
            'form_type' => $this->formType(),
            'data' => $this->formData(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $user = auth()->user();

        app(ParishConversationService::class)->startFromFormSubmission(
            $submission,
            $this->formType(),
            $this->formData(),
            $user instanceof User ? $user : null,
        );

        SecurityLogger::audit('form_submission', context: [
            'type' => $this->formType()->value,
            'form' => str($this->formType()->value)->headline()->toString(),
            'portal' => SecurityLogger::detectPortal(),
            'ip' => request()->ip(),
        ]);

        $this->submitted = true;
        $this->resetFormFields();
    }

    /**
     * @return array<string, mixed>
     */
    protected function captchaValidationRules(): array
    {
        return [];
    }

    protected function resetFormFields(): void
    {
        $this->reset(['website']);
    }

    protected function notifyAdmin(): void
    {
        $email = Setting::get('contact_email') ?: config('site.admin_email') ?: config('mail.from.address');

        if (! $email) {
            return;
        }

        try {
            MailConfigService::applyFromSettings();

            MailConfigService::deliverPlainTextMessage(
                $email,
                'New '.$this->formType()->value.' submission',
                "New {$this->formType()->value} form submission from steciuk.org\n\n".
                collect($this->formData())->map(fn ($v, $k) => ucfirst(str_replace('_', ' ', $k)).": {$v}")->implode("\n"),
            );
        } catch (\Throwable) {
            // Logged mail failures should not block form submission
        }
    }
}
