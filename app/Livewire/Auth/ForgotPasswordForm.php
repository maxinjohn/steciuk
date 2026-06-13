<?php

namespace App\Livewire\Auth;

use App\Livewire\Concerns\ValidatesTurnstileCaptcha;
use App\Services\UserPasswordService;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ForgotPasswordForm extends Component
{
    use ValidatesTurnstileCaptcha;

    #[Validate('required|email|max:255')]
    public string $email = '';

    public bool $sent = false;

    public function sendResetLink(UserPasswordService $passwordService): void
    {
        $validated = $this->validateWithTurnstileReset('turnstile-forgot-password', array_merge([
            'email' => 'required|email|max:255',
        ], $this->turnstileValidationRules()));

        $passwordService->requestPublicPasswordResetLink($validated['email']);

        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.auth.forgot-password-form', $this->turnstileViewData());
    }
}
