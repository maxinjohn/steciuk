<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\AllowsContentEditors;

class PagePolicy
{
    use AllowsContentEditors;

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, mixed $model): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, mixed $model): bool
    {
        return $user->isSuperAdmin();
    }
}
