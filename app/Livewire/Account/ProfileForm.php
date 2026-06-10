<?php

namespace App\Livewire\Account;

use App\Livewire\Concerns\HandlesUkAddress;
use App\Services\SecurityLogger;
use App\Support\ParishGender;
use App\Support\ParishPronouns;
use App\Support\ParishWorshipLocations;
use App\Support\UserName;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ProfileForm extends Component
{
    use HandlesUkAddress;

    #[Validate('required|string|max:120')]
    public string $first_name = '';

    #[Validate('nullable|string|max:120')]
    public string $last_name = '';

    #[Validate('nullable|string|max:50')]
    public string $pronouns = '';

    #[Validate('nullable|string|max:30')]
    public string $gender = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('required|string|max:30')]
    public string $phone = '';

    #[Validate('required|date|before:today|after:1900-01-01')]
    public string $date_of_birth = '';

    #[Validate('nullable|string|max:255')]
    public string $preferred_worship_location = '';

    public bool $saved = false;

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $this->first_name = $user->displayFirstName();
        $this->last_name = $user->displayLastName();
        $this->pronouns = (string) ($user->pronouns ?? '');
        $this->gender = (string) ($user->gender ?? '');
        $this->email = $user->email;
        $this->phone = (string) ($user->phone ?? '');
        $this->date_of_birth = $user->date_of_birth?->format('Y-m-d') ?? '';
        $this->preferred_worship_location = (string) ($user->preferred_worship_location ?? '');
        $this->fillUkAddressFromUser($user);
    }

    public function save(): void
    {
        $user = Auth::user();

        abort_unless($user, 403);

        $this->saved = false;

        $rules = array_merge([
            'first_name' => 'required|string|max:120',
            'last_name' => 'nullable|string|max:120',
            'pronouns' => ['nullable', 'string', 'max:50', Rule::in(array_keys(ParishPronouns::options()))],
            'gender' => ['nullable', 'string', 'max:30', Rule::in(array_keys(ParishGender::options()))],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'required|string|max:30',
            'date_of_birth' => 'required|date|before:today|after:1900-01-01',
            'preferred_worship_location' => 'nullable|string|in:'.implode(',', ParishWorshipLocations::all()),
        ], $this->ukAddressValidationRules());

        $validated = $this->validate($rules);
        $normalized = UserName::normalize($validated);

        $user->update([
            'first_name' => $normalized['first_name'],
            'last_name' => $normalized['last_name'],
            'name' => $normalized['name'],
            'pronouns' => filled($validated['pronouns'] ?? null) ? trim($validated['pronouns']) : null,
            'gender' => filled($validated['gender'] ?? null) ? trim($validated['gender']) : null,
            'email' => strtolower(trim($validated['email'])),
            'phone' => trim($validated['phone']),
            'date_of_birth' => $validated['date_of_birth'],
            'preferred_worship_location' => $validated['preferred_worship_location'] ?: null,
            ...$this->ukAddressFormData(),
        ]);

        SecurityLogger::audit('profile_updated', actor: $user, subject: $user, context: [
            'portal' => SecurityLogger::detectPortal(),
        ]);

        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.account.profile-form', [
            'worshipLocations' => ParishWorshipLocations::options(),
            'pronounOptions' => ParishPronouns::options(),
            'genderOptions' => ParishGender::options(),
        ]);
    }
}
