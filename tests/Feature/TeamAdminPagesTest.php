<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use App\Support\AdminPanelConfig;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Pages\RolePermissions;
use Tests\TestCase;

class TeamAdminPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_team_admin_routes_load_for_super_admin(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        foreach ([
            AdminPanelConfig::url('roles'),
            AdminPanelConfig::url('roles/create'),
            AdminPanelConfig::url('roles/2/edit'),
            AdminPanelConfig::url('users'),
            AdminPanelConfig::url('users/create'),
            AdminPanelConfig::url('role-permissions'),
        ] as $path) {
            $this->actingAs($admin)->get($path)->assertOk("Expected 200 for {$path}");
        }
    }

    public function test_site_ensure_roles_seeds_empty_roles_table(): void
    {
        Role::query()->delete();

        $this->assertSame(0, Role::query()->count());

        $this->artisan('site:ensure-roles', ['--force' => true])
            ->assertSuccessful();

        $this->assertGreaterThanOrEqual(3, Role::query()->count());
        $this->assertDatabaseHas('roles', ['slug' => UserRole::SuperAdmin->value]);
    }

    public function test_users_list_works_when_roles_table_is_empty(): void
    {
        Role::query()->delete();

        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->actingAs($admin)
            ->get(AdminPanelConfig::url('users'))
            ->assertOk()
            ->assertSee('Site Administrator', false);

        Livewire::actingAs($admin)->test(ListUsers::class)->assertOk();
    }

    public function test_roles_livewire_pages_work_after_ensure_roles(): void
    {
        Role::query()->delete();
        $this->artisan('site:ensure-roles', ['--force' => true])->assertSuccessful();

        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $editorRole = Role::query()->where('slug', UserRole::Editor->value)->firstOrFail();

        Livewire::actingAs($admin)->test(ListRoles::class)->assertOk();
        Livewire::actingAs($admin)->test(EditRole::class, ['record' => $editorRole->getRouteKey()])->assertOk();
        Livewire::actingAs($admin)->test(RolePermissions::class)->assertOk();
    }

    public function test_role_label_fallback_when_roles_table_missing(): void
    {
        Schema::dropIfExists('roles');

        $this->assertSame('Editor', Role::labelForSlug(UserRole::Editor->value));
        $this->assertSame(Role::legacyOptions(), Role::options());
    }
}
