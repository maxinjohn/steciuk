<?php

namespace App\Livewire\Auth;

use App\Enums\AccountStatus;
use App\Models\User;
use App\Services\SecurityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Component;

class LoginForm extends Component
{
    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $this->validate();

        $email = strtolower(trim($this->email));
        $key = 'member-login:'.hash('sha256', $email).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Too many sign-in attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        if (! Auth::attempt(['email' => $email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($key, 900);

            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->isActive() || ! $user->familyIsActive()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => $user->memberAccessBlockReason() ?? 'Your parish account is not active. Please contact the parish office for help.',
            ]);
        }

        if ($user->isMember()) {
            $status = $user->accountStatus();

            if ($status === AccountStatus::Pending) {
                Auth::logout();

                throw ValidationException::withMessages([
                    'email' => 'Your parish account is awaiting approval. We will email you once a member of the leadership team has reviewed your registration.',
                ]);
            }

            if ($status === AccountStatus::Rejected) {
                Auth::logout();

                throw ValidationException::withMessages([
                    'email' => 'Your registration was not approved. Please contact the parish office if you need assistance.',
                ]);
            }

            if (! $user->canSignInToMemberPortal()) {
                Auth::logout();

                throw ValidationException::withMessages([
                    'email' => $user->householdMemberPortalMessage(),
                ]);
            }
        }

        RateLimiter::clear($key);
        session()->regenerate();

        SecurityLogger::audit('member_login', actor: $user, context: [
            'portal' => 'member portal',
            'ip' => request()->ip(),
        ]);

        $this->redirectIntended(route('account'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login-form');
    }
}
