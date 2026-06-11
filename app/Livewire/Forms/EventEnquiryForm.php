<?php

namespace App\Livewire\Forms;

use App\Enums\FormType;
use App\Livewire\Concerns\HandlesChurchForm;
use App\Livewire\Concerns\ValidatesTurnstileCaptcha;
use Livewire\Attributes\Validate;
use Livewire\Component;

class EventEnquiryForm extends Component
{
    use HandlesChurchForm;
    use ValidatesTurnstileCaptcha;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:255')]
    public string $event_name = '';

    #[Validate('required|string|max:2000')]
    public string $message = '';

    protected function formType(): FormType
    {
        return FormType::EventEnquiry;
    }

    protected function validationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'event_name' => 'nullable|string|max:255',
            'message' => 'required|string|max:2000',
        ];
    }

    protected function formData(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'event_name' => $this->event_name,
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
        return view('livewire.forms.event-enquiry-form', $this->turnstileViewData());
    }
}
