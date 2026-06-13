<?php

namespace App\Livewire\Auth;

use App\Livewire\Concerns\ValidatesTurnstileCaptcha;
use App\Services\SecurityLogger;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ResetPasswordForm extends Component
{
    use ValidatesTurnstileCaptcha;

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
        $this->validateWithTurnstileReset('turnstile-reset-password', array_merge([
            'email' => 'required|email|max:255',
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ], $this->turnstileValidationRules()));

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
            $this->resetTurnstileCaptcha('turnstile-reset-password');

            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        session()->flash('password_reset', true);

        $this->redirectRoute('login');
    }

    public function render()
    {
        return view('livewire.auth.reset-password-form', $this->turnstileViewData());
    }
}
