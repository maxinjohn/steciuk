<?php

namespace App\Livewire\Auth;

use App\Services\SecurityLogger;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ResetPasswordForm extends Component
{
    public string $token = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = (string) request()->query('email', '');
    }

    public function resetPassword(): void
    {
        $this->validate([
            'email' => 'required|email|max:255',
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset(
            [
                'email' => strtolower(trim($this->email)),
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token,
            ],
            function ($user, string $password): void {
                $user->forceFill(['password' => $password])->save();

                event(new PasswordReset($user));

                SecurityLogger::audit('password_changed', actor: $user, subject: $user, context: [
                    'portal' => 'member portal',
                    'via' => 'password_reset_link',
                ]);
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        session()->flash('password_reset', true);

        $this->redirectRoute('login');
    }

    public function render()
    {
        return view('livewire.auth.reset-password-form');
    }
}
