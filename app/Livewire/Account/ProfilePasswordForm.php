<?php

namespace App\Livewire\Account;

use App\Services\SecurityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class ProfilePasswordForm extends Component
{
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $saved = false;

    public function updatePassword(): void
    {
        $user = Auth::user();

        abort_unless($user, 403);

        $validated = $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        SecurityLogger::audit('password_changed', actor: $user, subject: $user, context: [
            'portal' => SecurityLogger::detectPortal(),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.account.profile-password-form');
    }
}
