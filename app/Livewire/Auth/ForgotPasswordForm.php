<?php

namespace App\Livewire\Auth;

use App\Services\UserPasswordService;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ForgotPasswordForm extends Component
{
    #[Validate('required|email|max:255')]
    public string $email = '';

    public bool $sent = false;

    public function sendResetLink(UserPasswordService $passwordService): void
    {
        $validated = $this->validate();

        $passwordService->requestPublicPasswordResetLink($validated['email']);

        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.auth.forgot-password-form');
    }
}
