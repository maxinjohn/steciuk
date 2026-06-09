<?php

namespace App\Policies;

use App\Models\User;
use App\Services\PermissionService;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return app(PermissionService::class)->canResource($user, 'users', 'viewAny');
    }

    public function view(User $user, User $model): bool
    {
        return app(PermissionService::class)->canResource($user, 'users', 'view');
    }

    public function create(User $user): bool
    {
        return app(PermissionService::class)->canResource($user, 'users', 'create');
    }

    public function update(User $user, User $model): bool
    {
        return app(PermissionService::class)->canResource($user, 'users', 'update');
    }

    public function delete(User $user, User $model): bool
    {
        return app(PermissionService::class)->canResource($user, 'users', 'delete');
    }

    public function restore(User $user, User $model): bool
    {
        return app(PermissionService::class)->canResource($user, 'users', 'restore');
    }

    public function forceDelete(User $user, User $model): bool
    {
        return app(PermissionService::class)->canResource($user, 'users', 'forceDelete');
    }
}
