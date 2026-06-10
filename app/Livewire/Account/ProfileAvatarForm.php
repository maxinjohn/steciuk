<?php

namespace App\Livewire\Account;

use App\Services\SecurityLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ProfileAvatarForm extends Component
{
    use WithFileUploads;

    public ?TemporaryUploadedFile $photo = null;

    public bool $saved = false;

    public bool $removed = false;

    public function updatedPhoto(): void
    {
        $this->saved = false;
        $this->removed = false;
        $this->validateOnly('photo', $this->photoRules());
    }

    public function uploadPhoto(): void
    {
        $user = Auth::user();

        abort_unless($user, 403);

        $this->validate(['photo' => $this->photoRules()['photo']]);

        $user->clearMediaCollection('profile_photo');
        $user->addMedia($this->photo->getRealPath())
            ->usingFileName('profile-'.$user->id.'.'.$this->photo->getClientOriginalExtension())
            ->toMediaCollection('profile_photo');

        $this->reset('photo');
        $this->saved = true;
        $this->removed = false;

        SecurityLogger::audit('profile_photo_updated', actor: $user, subject: $user, context: [
            'change' => 'uploaded a new photo',
            'portal' => SecurityLogger::detectPortal(),
        ]);

        $this->dispatch('avatar-updated', url: $user->fresh()->avatarUrl());
    }

    public function removePhoto(): void
    {
        $user = Auth::user();

        abort_unless($user, 403);

        $user->clearMediaCollection('profile_photo');

        $this->reset('photo');
        $this->removed = true;
        $this->saved = false;

        SecurityLogger::audit('profile_photo_updated', actor: $user, subject: $user, context: [
            'change' => 'removed their photo',
            'portal' => SecurityLogger::detectPortal(),
        ]);

        $this->dispatch('avatar-updated', url: $user->fresh()->avatarUrl());
    }

    /**
     * @return array<string, mixed>
     */
    private function photoRules(): array
    {
        return [
            'photo' => 'required|image|mimes:jpeg,jpg,png,webp,gif|max:3072',
        ];
    }

    public function render()
    {
        return view('livewire.account.profile-avatar-form');
    }
}
