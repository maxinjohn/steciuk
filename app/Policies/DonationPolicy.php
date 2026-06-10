<?php

namespace App\Policies;

use App\Models\Donation;
use App\Models\User;
use App\Services\PermissionService;

class DonationPolicy
{
    public function viewAny(User $user): bool
    {
        return app(PermissionService::class)->canResource($user, 'users', 'viewAny');
    }

    public function view(User $user, Donation $donation): bool
    {
        return app(PermissionService::class)->canResource($user, 'users', 'view');
    }

    public function create(User $user): bool
    {
        return app(PermissionService::class)->canResource($user, 'users', 'create');
    }

    public function update(User $user, Donation $donation): bool
    {
        return app(PermissionService::class)->canResource($user, 'users', 'update');
    }

    public function delete(User $user, Donation $donation): bool
    {
        return app(PermissionService::class)->canResource($user, 'users', 'delete');
    }
}
