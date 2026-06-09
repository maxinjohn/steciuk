<?php

namespace Tests\Feature;

use App\Enums\AdminPermission;
use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use App\Services\PermissionService;
use App\Support\AdminPanelConfig;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomRolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_custom_role_can_be_created_with_permissions(): void
    {
        $role = Role::query()->create([
            'slug' => 'communications',
            'name' => 'Communications Lead',
            'description' => 'News and announcements only.',
            'is_system' => false,
            'grants_full_access' => false,
            'sort_order' => 50,
        ]);

        app(PermissionService::class)->saveRolePermissions([
            'communications' => [
                AdminPermission::AdminAccess->value => true,
                AdminPermission::NewsViewAny->value => true,
                AdminPermission::NewsCreate->value => true,
            ],
        ]);

        \App\Models\Setting::forgetCache();

        $user = User::factory()->create(['role' => $role->slug]);
        $service = app(PermissionService::class);

        $this->assertTrue($service->canAccessAdmin($user));
        $this->assertTrue($service->can($user, AdminPermission::NewsCreate));
        $this->assertFalse($service->can($user, AdminPermission::PagesCreate));
    }

    public function test_system_role_name_can_be_renamed(): void
    {
        $editor = Role::query()->where('slug', UserRole::Editor->value)->firstOrFail();
        $editor->update(['name' => 'Content Curator']);

        $this->assertSame('Content Curator', Role::labelForSlug(UserRole::Editor->value));
    }

    public function test_roles_admin_screen_is_available_to_super_admin(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin->value]);

        $this->actingAs($admin)
            ->get(AdminPanelConfig::url('roles'))
            ->assertOk()
            ->assertSee('Super Admin', false)
            ->assertSee('Editor', false);
    }
}
