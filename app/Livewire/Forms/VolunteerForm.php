<?php

namespace App\Livewire\Forms;

use App\Enums\FormType;
use App\Livewire\Concerns\HandlesChurchForm;
use Livewire\Attributes\Validate;
use Livewire\Component;

class VolunteerForm extends Component
{
    use HandlesChurchForm;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:30')]
    public string $phone = '';

    #[Validate('required|string|max:255')]
    public string $ministry_interest = '';

    #[Validate('nullable|string|max:2000')]
    public string $experience = '';

    protected function formType(): FormType
    {
        return FormType::Volunteer;
    }

    protected function validationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'ministry_interest' => 'required|string|max:255',
            'experience' => 'nullable|string|max:2000',
        ];
    }

    protected function formData(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'ministry_interest' => $this->ministry_interest,
            'experience' => $this->experience,
        ];
    }

    public function render()
    {
        return view('livewire.forms.volunteer-form');
    }
}
