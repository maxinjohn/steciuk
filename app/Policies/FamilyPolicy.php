<?php

namespace App\Policies;

use App\Models\Family;
use App\Models\User;
use App\Services\PermissionService;

class FamilyPolicy
{
    public function viewAny(User $user): bool
    {
        return app(PermissionService::class)->canResource($user, 'users', 'viewAny');
    }

    public function view(User $user, Family $family): bool
    {
        return app(PermissionService::class)->canResource($user, 'users', 'view');
    }

    public function create(User $user): bool
    {
        return app(PermissionService::class)->canResource($user, 'users', 'create');
    }

    public function update(User $user, Family $family): bool
    {
        return app(PermissionService::class)->canResource($user, 'users', 'update');
    }

    public function delete(User $user, Family $family): bool
    {
        return app(PermissionService::class)->canResource($user, 'users', 'delete');
    }
}
