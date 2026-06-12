<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\Panel;
use App\Models\User;

class PanelPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasFullPanelAccess()
            || $user->hasAdminPermission(AdminPermission::UsersViewAny);
    }

    public function view(User $user, Panel $panel): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Panel $panel): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Panel $panel): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        return ! $panel->is_system && $panel->members()->count() === 0;
    }
}
