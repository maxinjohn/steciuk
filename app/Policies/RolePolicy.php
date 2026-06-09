<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->hasAdminPermission(AdminPermission::SettingsPermissions);
    }

    public function view(User $user, Role $role): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Role $role): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Role $role): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        return ! $role->is_system && $role->users()->count() === 0;
    }
}
