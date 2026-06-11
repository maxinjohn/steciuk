<?php

namespace App\Livewire\Forms;

use App\Enums\FormType;
use App\Livewire\Concerns\HandlesChurchForm;
use App\Livewire\Concerns\PrefillsAuthenticatedMember;
use App\Livewire\Concerns\ValidatesTurnstileCaptcha;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ContactForm extends Component
{
    use HandlesChurchForm;
    use PrefillsAuthenticatedMember;
    use ValidatesTurnstileCaptcha;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:30')]
    public string $phone = '';

    #[Validate('required|string|max:2000')]
    public string $message = '';

    public function mount(): void
    {
        $this->prefillFromAuthenticatedUser();
    }

    protected function formType(): FormType
    {
        return FormType::Contact;
    }

    protected function validationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'message' => 'required|string|max:2000',
        ];
    }

    protected function formData(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'message' => $this->message,
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
        return view('livewire.forms.contact-form', $this->turnstileViewData());
    }
}
