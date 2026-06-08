<?php

namespace App\Policies\Concerns;

use App\Enums\UserRole;
use App\Models\User;

trait AllowsContentEditors
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::SuperAdmin, UserRole::Editor);
    }

    public function view(User $user, mixed $model): bool
    {
        return $user->hasRole(UserRole::SuperAdmin, UserRole::Editor);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::SuperAdmin, UserRole::Editor);
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->hasRole(UserRole::SuperAdmin, UserRole::Editor);
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->hasRole(UserRole::SuperAdmin, UserRole::Editor);
    }

    public function restore(User $user, mixed $model): bool
    {
        return $user->hasRole(UserRole::SuperAdmin, UserRole::Editor);
    }

    public function forceDelete(User $user, mixed $model): bool
    {
        return $user->isSuperAdmin();
    }
}
