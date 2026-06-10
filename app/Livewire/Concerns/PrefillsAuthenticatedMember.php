<?php

namespace App\Livewire\Concerns;

trait PrefillsAuthenticatedMember
{
    protected function prefillFromAuthenticatedUser(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        if (property_exists($this, 'name')) {
            $this->name = $user->displayFullName();
        }

        if (property_exists($this, 'email')) {
            $this->email = $user->email;
        }

        if (property_exists($this, 'phone')) {
            $this->phone = (string) ($user->phone ?? '');
        }

        if (property_exists($this, 'location') && filled($user->preferred_worship_location)) {
            $this->location = (string) $user->preferred_worship_location;
        }

        if (method_exists($this, 'fillUkAddressFromUser')) {
            $this->fillUkAddressFromUser($user);
        }
    }
}
