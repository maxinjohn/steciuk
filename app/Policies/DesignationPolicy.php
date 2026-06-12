<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\Designation;
use App\Models\User;

class DesignationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasFullPanelAccess()
            || $user->hasAdminPermission(AdminPermission::UsersViewAny);
    }

    public function view(User $user, Designation $designation): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Designation $designation): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Designation $designation): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        return ! $designation->is_system && $designation->users()->count() === 0;
    }
}
