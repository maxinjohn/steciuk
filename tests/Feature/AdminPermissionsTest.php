<?php

namespace Tests\Feature;

use App\Enums\AdminPermission;
use App\Enums\UserRole;
use App\Models\Page;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_can_access_admin_with_read_only_permissions(): void
    {
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);
        $service = app(PermissionService::class);

        $this->assertTrue($service->canAccessAdmin($viewer));
        $this->assertTrue($service->can($viewer, AdminPermission::PagesViewAny));
        $this->assertFalse($service->can($viewer, AdminPermission::PagesCreate));
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

    public function test_page_mutations_require_super_admin(): void
    {
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $page = Page::factory()->create();

        $this->assertFalse($editor->can('create', Page::class));
        $this->assertFalse($editor->can('update', $page));
        $this->assertFalse($editor->can('delete', $page));

        $this->assertFalse($viewer->can('update', $page));
        $this->assertTrue($viewer->can('view', $page));

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

        \App\Models\Setting::forgetCache();

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
