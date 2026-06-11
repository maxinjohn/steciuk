<?php

namespace App\Livewire\Forms;

use App\Enums\FormType;
use App\Livewire\Concerns\HandlesChurchForm;
use App\Livewire\Concerns\HandlesUkAddress;
use App\Livewire\Concerns\PrefillsAuthenticatedMember;
use App\Livewire\Concerns\ValidatesTurnstileCaptcha;
use App\Support\ParishWorshipLocations;
use Livewire\Attributes\Validate;
use Livewire\Component;

class NewMemberForm extends Component
{
    use HandlesChurchForm;
    use HandlesUkAddress;
    use PrefillsAuthenticatedMember;
    use ValidatesTurnstileCaptcha;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('required|string|max:30')]
    public string $phone = '';

    #[Validate('nullable|date|before:today|after:1900-01-01')]
    public string $date_of_birth = '';

    #[Validate('nullable|string|max:255')]
    public string $location = '';

    #[Validate('nullable|string|max:1000')]
    public string $notes = '';

    public function mount(): void
    {
        $this->prefillFromAuthenticatedUser();

        if (auth()->user()?->date_of_birth) {
            $this->date_of_birth = auth()->user()->date_of_birth->format('Y-m-d');
        }
    }

    protected function formType(): FormType
    {
        return FormType::NewMember;
    }

    protected function validationRules(): array
    {
        return array_merge([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
            'date_of_birth' => 'nullable|date|before:today|after:1900-01-01',
            'location' => 'nullable|string|in:'.implode(',', ParishWorshipLocations::all()),
            'notes' => 'nullable|string|max:1000',
        ], $this->ukAddressValidationRules(required: false));
    }

    protected function formData(): array
    {
        return array_merge([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth,
            'preferred_worship_location' => $this->location,
            'notes' => $this->notes,
        ], $this->ukAddressFormData());
    }

    protected function captchaValidationRules(): array
    {
        return $this->turnstileValidationRules();
    }

    protected function resetFormFields(): void
    {
        $this->reset(['website', 'captchaToken']);
    }

    public function render()
    {
        return view('livewire.forms.new-member-form', array_merge(
            [
                'worshipLocations' => ParishWorshipLocations::options(),
            ],
            $this->turnstileViewData(),
        ));
    }
}
