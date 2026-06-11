<?php

namespace App\Policies;

use App\Models\Donation;
use App\Models\User;

class DonationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasFullPanelAccess();
    }

    public function view(User $user, Donation $donation): bool
    {
        return $user->hasFullPanelAccess();
    }

    public function create(User $user): bool
    {
        return $user->hasFullPanelAccess();
    }

    public function update(User $user, Donation $donation): bool
    {
        return $user->hasFullPanelAccess();
    }

    public function delete(User $user, Donation $donation): bool
    {
        return $user->hasFullPanelAccess();
    }
}
