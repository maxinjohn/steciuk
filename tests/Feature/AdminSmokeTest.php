<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\AdminPanelConfig;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $this->admin = User::factory()->create([
            'email' => 'admin-smoke@steciuk.org',
            'role' => UserRole::SuperAdmin,
        ]);
    }

    private User $admin;

    /**
     * @return list<string>
     */
    private function adminPaths(): array
    {
        return [
            AdminPanelConfig::url(),
            AdminPanelConfig::url('church-settings'),
            AdminPanelConfig::url('site-content-settings'),
            AdminPanelConfig::url('mail-settings'),
            AdminPanelConfig::url('roles'),
            AdminPanelConfig::url('role-permissions'),
            AdminPanelConfig::url('pages'),
            AdminPanelConfig::url('menu-items'),
            AdminPanelConfig::url('news'),
            AdminPanelConfig::url('resources'),
            AdminPanelConfig::url('services'),
            AdminPanelConfig::url('sermons'),
            AdminPanelConfig::url('events'),
            AdminPanelConfig::url('ministries'),
            AdminPanelConfig::url('gallery-albums'),
            AdminPanelConfig::url('gallery-photos'),
            AdminPanelConfig::url('form-submissions'),
            AdminPanelConfig::url('users'),
            AdminPanelConfig::url('families'),
            AdminPanelConfig::url('families/create'),
            AdminPanelConfig::url('donations'),
            AdminPanelConfig::url('security-audit-logs'),
        ];
    }

    public function test_super_admin_can_open_all_admin_sections(): void
    {
        foreach ($this->adminPaths() as $path) {
            $response = $this->actingAs($this->admin)->get($path);

            $response->assertOk("Expected admin 200 for {$path}, got {$response->status()}");
        }
    }

    public function test_editor_can_access_content_sections(): void
    {
        $editor = User::factory()->create(['role' => UserRole::Editor]);

        $paths = [
            AdminPanelConfig::url(),
            AdminPanelConfig::url('pages'),
            AdminPanelConfig::url('church-settings'),
            AdminPanelConfig::url('form-submissions'),
        ];

        foreach ($paths as $path) {
            $this->actingAs($editor)->get($path)->assertOk();
        }

        $this->actingAs($editor)->get(AdminPanelConfig::url('pages/create'))->assertForbidden();
        $this->actingAs($editor)->get(AdminPanelConfig::url('pages/1/edit'))->assertForbidden();
    }

    public function test_editor_cannot_access_role_permissions(): void
    {
        $editor = User::factory()->create(['role' => UserRole::Editor]);

        $this->actingAs($editor)->get(AdminPanelConfig::url('role-permissions'))->assertForbidden();
    }

    public function test_super_admin_can_open_admin_create_forms(): void
    {
        $createPaths = [
            'pages/create',
            'events/create',
            'news/create',
            'sermons/create',
            'services/create',
            'ministries/create',
            'menu-items/create',
            'content-blocks/create',
            'gallery-albums/create',
            'gallery-photos/create',
            'resources/create',
            'roles/create',
            'users/create',
            'families/create',
            'donations/create',
        ];

        foreach ($createPaths as $suffix) {
            $path = AdminPanelConfig::url($suffix);
            $response = $this->actingAs($this->admin)->get($path);

            $response->assertOk("Expected admin create 200 for {$path}, got {$response->status()}");
        }
    }

    public function test_super_admin_can_open_seeded_record_edit_forms(): void
    {
        $editPaths = [
            'pages/1/edit',
            'events/1/edit',
            'news/1/edit',
            'sermons/1/edit',
            'services/1/edit',
            'ministries/1/edit',
            'menu-items/1/edit',
            'gallery-albums/1/edit',
            'gallery-photos/1/edit',
            'resources/1/edit',
            'roles/1/edit',
        ];

        foreach ($editPaths as $suffix) {
            $path = AdminPanelConfig::url($suffix);
            $response = $this->actingAs($this->admin)->get($path);

            $response->assertOk("Expected admin edit 200 for {$path}, got {$response->status()}");
        }
    }
}
