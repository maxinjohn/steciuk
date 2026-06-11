<?php

namespace App\Livewire\Forms;

use App\Enums\FormType;
use App\Livewire\Concerns\HandlesChurchForm;
use App\Livewire\Concerns\PrefillsAuthenticatedMember;
use App\Livewire\Concerns\ValidatesTurnstileCaptcha;
use Livewire\Attributes\Validate;
use Livewire\Component;

class PrayerRequestForm extends Component
{
    use HandlesChurchForm;
    use PrefillsAuthenticatedMember;
    use ValidatesTurnstileCaptcha;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|email|max:255')]
    public string $email = '';

    #[Validate('required|string|max:3000')]
    public string $request = '';

    #[Validate('boolean')]
    public bool $confidential = true;

    public function mount(): void
    {
        $this->prefillFromAuthenticatedUser();
    }

    protected function formType(): FormType
    {
        return FormType::PrayerRequest;
    }

    protected function validationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'request' => 'required|string|max:3000',
            'confidential' => 'boolean',
        ];
    }

    protected function formData(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'request' => $this->request,
            'confidential' => $this->confidential ? 'Yes' : 'No',
        ];
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
        return view('livewire.forms.prayer-request-form', $this->turnstileViewData());
    }
}
