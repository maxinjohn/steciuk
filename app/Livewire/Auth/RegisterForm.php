<?php

namespace App\Livewire\Auth;

use App\Livewire\Concerns\HandlesUkAddress;
use App\Rules\TurnstileCaptcha;
use App\Services\MemberRegistrationService;
use App\Services\SecurityLogger;
use App\Services\TurnstileCaptchaService;
use App\Support\GdprConfig;
use App\Support\ParishGender;
use App\Support\ParishPronouns;
use App\Support\ParishWorshipLocations;
use App\Support\UserName;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Validate;
use Livewire\Component;

class RegisterForm extends Component
{
    use HandlesUkAddress;

    #[Validate('nullable|string|max:120')]
    public string $first_name = '';

    #[Validate('nullable|string|max:120')]
    public string $last_name = '';

    /** @deprecated Backward compatibility for tests and legacy forms */
    #[Validate('nullable|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:50')]
    public string $pronouns = '';

    #[Validate('nullable|string|max:30')]
    public string $gender = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    #[Validate('required|string|max:30')]
    public string $phone = '';

    #[Validate('required|date|before:today|after:1900-01-01')]
    public string $date_of_birth = '';

    #[Validate('nullable|string|max:255')]
    public string $preferred_worship_location = '';

    #[Validate('nullable|string|max:0')]
    public string $website = '';

    public string $captchaToken = '';

    public bool $accept_privacy = false;

    public bool $accept_terms = false;

    public bool $marketing_consent = false;

    public function register(MemberRegistrationService $registrationService): void
    {
        if ($this->website !== '') {
            SecurityLogger::warning('honeypot_triggered', null, [
                'type' => 'register',
                'ip' => request()->ip(),
            ]);

            return;
        }

        $rules = array_merge([
            'first_name' => 'required_without:name|string|max:120',
            'last_name' => 'nullable|string|max:120',
            'name' => 'required_without:first_name|string|max:255',
            'pronouns' => ['required', 'string', 'max:50', Rule::in(array_keys(ParishPronouns::requiredOptions()))],
            'gender' => ['required', 'string', 'max:30', Rule::in(array_keys(ParishGender::requiredOptions()))],
            'email' => 'required|email|max:255',
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => 'required|string|max:30',
            'date_of_birth' => 'required|date|before:today|after:1900-01-01',
            'preferred_worship_location' => 'nullable|string|in:'.implode(',', ParishWorshipLocations::all()),
            'website' => 'nullable|string|max:0',
            'captchaToken' => [
                Rule::requiredIf(fn (): bool => app(TurnstileCaptchaService::class)->isEnabled()),
                new TurnstileCaptcha,
            ],
            'accept_privacy' => 'accepted',
            'accept_terms' => 'accepted',
            'marketing_consent' => 'boolean',
        ], $this->ukAddressValidationRules());

        $validated = $this->validate($rules);

        $key = 'register:'.request()->ip();
        $maxAttempts = 10;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);
            $this->addError(
                'form',
                $minutes <= 1
                    ? 'Too many completed registration attempts from this connection. Please try again in about a minute.'
                    : "Too many completed registration attempts from this connection. Please try again in about {$minutes} minutes.",
            );

            return;
        }

        RateLimiter::hit($key, 900);
        $person = UserName::normalize([
            'first_name' => $validated['first_name'] ?? '',
            'last_name' => $validated['last_name'] ?? '',
            'name' => $validated['name'] ?? '',
        ]);

        $registrationService->assertEmailAvailable($validated['email']);

        $registrationService->register(
            primary: array_merge($this->ukAddressFormData(), [
                'first_name' => $person['first_name'],
                'last_name' => $person['last_name'],
                'name' => $person['name'],
                'pronouns' => trim($validated['pronouns']),
                'gender' => trim($validated['gender']),
                'email' => $validated['email'],
                'password' => $validated['password'],
                'phone' => trim($validated['phone']),
                'date_of_birth' => $validated['date_of_birth'],
                'preferred_worship_location' => $validated['preferred_worship_location'] ?: null,
            ]),
            consents: [
                'marketing_consent' => $this->marketing_consent,
            ],
        );

        session()->flash('registration_submitted', true);

        $this->redirectRoute('registration.pending', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register-form', [
            'worshipLocations' => ParishWorshipLocations::options(),
            'pronounOptions' => ParishPronouns::requiredOptions(),
            'genderOptions' => ParishGender::requiredOptions(),
            'turnstileSiteKey' => app(TurnstileCaptchaService::class)->siteKey(),
            'turnstileEnabled' => app(TurnstileCaptchaService::class)->isEnabled(),
            'privacyPolicyUrl' => GdprConfig::privacyPolicyUrl(),
            'termsUrl' => GdprConfig::termsUrl(),
        ]);
    }
}
