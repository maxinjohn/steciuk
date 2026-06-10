<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\AllowsContentEditors;

class PagePolicy
{
    use AllowsContentEditors;

    public function create(User $user): bool
    {
        return $user->hasFullPanelAccess();
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->hasFullPanelAccess();
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->hasFullPanelAccess();
    }

    public function restore(User $user, mixed $model): bool
    {
        return $user->hasFullPanelAccess();
    }

    public function forceDelete(User $user, mixed $model): bool
    {
        return $user->hasFullPanelAccess();
    }
}
