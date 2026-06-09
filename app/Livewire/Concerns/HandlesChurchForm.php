<?php

namespace App\Livewire\Concerns;

use App\Enums\FormType;
use App\Services\MailConfigService;
use App\Models\FormSubmission;
use App\Models\Setting;
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
            \App\Services\SecurityLogger::warning('honeypot_triggered', null, [
                'type' => $this->formType()->value,
                'ip' => request()->ip(),
            ]);

            return;
        }

        $key = 'form:'.$this->formType()->value.':'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            \App\Services\SecurityLogger::warning('form_rate_limited', null, [
                'type' => $this->formType()->value,
                'ip' => request()->ip(),
            ]);

            $this->addError('form', 'Too many submissions. Please try again later.');

            return;
        }

        RateLimiter::hit($key, 3600);

        $rules = array_merge($this->validationRules(), [
            'website' => 'nullable|string|max:0',
        ]);

        $this->validate($rules);

        FormSubmission::query()->create([
            'form_type' => $this->formType(),
            'data' => $this->formData(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        \App\Services\SecurityLogger::info('form_submission', null, [
            'type' => $this->formType()->value,
            'ip' => request()->ip(),
        ]);

        $this->notifyAdmin();

        $this->submitted = true;
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
