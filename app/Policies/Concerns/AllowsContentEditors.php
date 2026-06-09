<?php

namespace App\Policies\Concerns;

use App\Models\User;
use App\Services\PermissionService;

trait AllowsContentEditors
{
    protected function permissionResource(): string
    {
        $resource = PermissionService::resourceForPolicy(static::class);

        if (! $resource) {
            throw new \LogicException('Permission resource not mapped for '.static::class);
        }

        return $resource;
    }

    protected function allows(User $user, string $action): bool
    {
        return app(PermissionService::class)->canResource($user, $this->permissionResource(), $action);
    }

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'viewAny');
    }

    public function view(User $user, mixed $model): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, mixed $model): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, mixed $model): bool
    {
        return $this->allows($user, 'delete');
    }

    public function restore(User $user, mixed $model): bool
    {
        return $this->allows($user, 'restore');
    }

    public function forceDelete(User $user, mixed $model): bool
    {
        return $this->allows($user, 'forceDelete');
    }
}
