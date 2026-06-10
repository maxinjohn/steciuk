<?php

namespace Tests\Feature;

use App\Enums\AdminPermission;
use App\Enums\UserRole;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_has_full_panel_permissions(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $service = app(PermissionService::class);

        $this->assertTrue($service->canAccessAdmin($admin));
        $this->assertTrue($service->can($admin, AdminPermission::SettingsPermissions));
        $this->assertTrue($service->can($admin, AdminPermission::UsersCreate));
        $this->assertTrue($service->can($admin, AdminPermission::PagesCreate));
    }

    public function test_admin_cannot_change_own_role(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin);

        $admin->role = UserRole::Member->value;
        $admin->save();

        $admin->refresh();

        $this->assertSame(UserRole::Admin->value, $admin->roleSlug());
        $this->assertFalse($admin->canChangeRoleOf($admin));
    }

    public function test_admin_cannot_assign_super_admin_role(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $member = User::factory()->create(['role' => UserRole::Member]);

        $this->actingAs($admin);

        $member->role = UserRole::SuperAdmin->value;
        $member->save();

        $member->refresh();

        $this->assertSame(UserRole::Member->value, $member->roleSlug());
    }

    public function test_super_admin_can_assign_super_admin_role(): void
    {
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $member = User::factory()->create(['role' => UserRole::Member]);

        $this->actingAs($superAdmin);

        $member->role = UserRole::SuperAdmin->value;
        $member->save();

        $member->refresh();

        $this->assertSame(UserRole::SuperAdmin->value, $member->roleSlug());
    }

    public function test_admin_cannot_manage_super_admin_account(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->assertFalse($admin->can('update', $superAdmin));
        $this->assertFalse($admin->can('delete', $superAdmin));
        $this->assertTrue($admin->can('view', $superAdmin));
    }

    public function test_editor_defaults_allow_content_management(): void
    {
        $editor = User::factory()->create(['role' => UserRole::Editor]);

        $service = app(PermissionService::class);

        $this->assertTrue($service->can($editor, AdminPermission::PagesCreate));
        $this->assertTrue($service->can($editor, AdminPermission::SettingsChurch));
        $this->assertFalse($service->can($editor, AdminPermission::UsersCreate));
        $this->assertFalse($service->can($editor, AdminPermission::SettingsPermissions));
    }

    public function test_page_mutations_require_elevated_access(): void
    {
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $page = Page::factory()->create();

        $this->assertFalse($editor->can('create', Page::class));
        $this->assertFalse($editor->can('update', $page));
        $this->assertFalse($editor->can('delete', $page));

        $this->assertTrue($admin->can('update', $page));
        $this->assertTrue($admin->can('delete', $page));

        $this->assertTrue($superAdmin->can('update', $page));
        $this->assertTrue($superAdmin->can('delete', $page));
    }

    public function test_super_admin_bypasses_permission_checks(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->assertTrue(app(PermissionService::class)->can($admin, AdminPermission::SettingsPermissions));
    }

    public function test_custom_role_permissions_can_remove_editor_create_access(): void
    {
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $permissions = app(PermissionService::class)->rolePermissions(UserRole::Editor->value);
        $permissions[AdminPermission::PagesCreate->value] = false;

        app(PermissionService::class)->saveRolePermissions([
            UserRole::Editor->value => $permissions,
        ]);

        Setting::forgetCache();

        $this->assertFalse(app(PermissionService::class)->can($editor, AdminPermission::PagesCreate));
    }

    public function test_page_policy_respects_custom_permissions(): void
    {
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $page = Page::factory()->create();

        $this->assertTrue($editor->can('viewAny', Page::class));
        $this->assertTrue($editor->can('view', $page));
        $this->assertFalse($editor->can('update', $page));
    }
}
