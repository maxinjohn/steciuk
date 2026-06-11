<?php

namespace App\Services;

use App\Enums\AdminPermission;
use App\Enums\UserRole;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Policies\ContentBlockPolicy;
use App\Policies\EventPolicy;
use App\Policies\ConversationPolicy;
use App\Policies\FormSubmissionPolicy;
use App\Policies\GalleryAlbumPolicy;
use App\Policies\GalleryPhotoPolicy;
use App\Policies\MenuItemPolicy;
use App\Policies\MinistryPolicy;
use App\Policies\NewsPolicy;
use App\Policies\PagePolicy;
use App\Policies\ResourcePolicy;
use App\Policies\SermonPolicy;
use App\Policies\ServicePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Schema;

class PermissionService
{
    private const STORAGE_KEY = 'role_permissions';

    /**
     * @var array<class-string, string>
     */
    private const POLICY_RESOURCES = [
        PagePolicy::class => 'pages',
        EventPolicy::class => 'events',
        NewsPolicy::class => 'news',
        SermonPolicy::class => 'sermons',
        MinistryPolicy::class => 'ministries',
        MenuItemPolicy::class => 'menu_items',
        ContentBlockPolicy::class => 'content_blocks',
        GalleryAlbumPolicy::class => 'gallery_albums',
        GalleryPhotoPolicy::class => 'gallery_photos',
        ResourcePolicy::class => 'parish_resources',
        ServicePolicy::class => 'services',
        FormSubmissionPolicy::class => 'form_submissions',
        ConversationPolicy::class => 'form_submissions',
        UserPolicy::class => 'users',
    ];

    public static function resourceForPolicy(string $policyClass): ?string
    {
        return self::POLICY_RESOURCES[$policyClass] ?? null;
    }

    public function can(User $user, AdminPermission|string $permission): bool
    {
        if ($user->hasFullPanelAccess()) {
            return true;
        }

        $key = $permission instanceof AdminPermission ? $permission->value : $permission;
        $roleKey = $user->roleSlug();
        $permissions = $this->rolePermissions($roleKey);

        return (bool) ($permissions[$key] ?? false);
    }

    public function canResource(User $user, string $resource, string $action): bool
    {
        $permission = AdminPermission::forResourceAction($resource, $action);

        if (! $permission) {
            return false;
        }

        return $this->can($user, $permission);
    }

    public function canAccessAdmin(User $user): bool
    {
        return $this->can($user, AdminPermission::AdminAccess);
    }

    /**
     * @return array<string, bool>
     */
    public function rolePermissions(string $role): array
    {
        $stored = $this->storedMatrix();

        if (isset($stored[$role]) && is_array($stored[$role])) {
            return array_merge($this->defaultPermissionsForRole($role), $stored[$role]);
        }

        return $this->defaultPermissionsForRole($role);
    }

    /**
     * @param  array<string, array<string, bool>>  $matrix
     */
    public function saveRolePermissions(array $matrix): void
    {
        $existing = $this->storedMatrix();

        Setting::set(self::STORAGE_KEY, array_merge($existing, $matrix), 'security');
    }

    /**
     * @return array<string, array<string, bool>>
     */
    public function allRolePermissions(): array
    {
        $result = [];

        foreach ($this->manageableRoleSlugs() as $role) {
            $result[$role] = $this->rolePermissions($role);
        }

        return $result;
    }

    /**
     * @return list<string>
     */
    public function manageableRoleSlugs(): array
    {
        if (! Schema::hasTable('roles')) {
            return [UserRole::Editor->value, UserRole::Admin->value];
        }

        return Role::query()
            ->where('grants_full_access', false)
            ->whereNotIn('slug', [UserRole::Member->value])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('slug')
            ->all() ?: [UserRole::Editor->value, UserRole::Admin->value];
    }

    /**
     * @return array<string, bool>
     */
    public function defaultPermissionsForRole(string $role): array
    {
        $all = collect(AdminPermission::cases())
            ->mapWithKeys(fn (AdminPermission $permission) => [$permission->value => false])
            ->all();

        if (Role::findBySlug($role)?->grants_full_access) {
            return collect($all)->map(fn () => true)->all();
        }

        if ($role === UserRole::Admin->value) {
            return collect($all)->map(fn () => true)->all();
        }

        if ($role === UserRole::Editor->value) {
            return array_merge($all, $this->editorDefaults());
        }

        if ($role === UserRole::Member->value) {
            return $all;
        }

        return $all;
    }

    /**
     * @return array<string, array<string, bool>>
     */
    private function storedMatrix(): array
    {
        $stored = Setting::get(self::STORAGE_KEY);
        $decoded = is_string($stored) ? json_decode($stored, true) : $stored;

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<string, bool>
     */
    private function editorDefaults(): array
    {
        $enabled = [
            AdminPermission::AdminAccess->value => true,
            AdminPermission::SettingsChurch->value => true,
        ];

        foreach (['pages', 'events', 'news', 'sermons', 'ministries', 'menu_items', 'content_blocks', 'gallery_albums', 'gallery_photos', 'parish_resources', 'services'] as $resource) {
            foreach (['viewAny', 'view', 'create', 'update', 'delete', 'restore'] as $action) {
                $enabled["{$resource}.{$action}"] = true;
            }
        }

        foreach (['viewAny', 'view', 'update', 'delete'] as $action) {
            $enabled["form_submissions.{$action}"] = true;
        }

        return $enabled;
    }
}
