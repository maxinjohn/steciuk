<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Role;
use App\Support\SeedConfig;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'slug' => UserRole::SuperAdmin->value,
                'name' => 'Super Admin',
                'description' => 'Full access to every admin area. Cannot be deleted.',
                'is_system' => true,
                'grants_full_access' => true,
                'sort_order' => 1,
            ],
            [
                'slug' => UserRole::Editor->value,
                'name' => 'Editor',
                'description' => 'Manage parish content — pages, worship, media, and messages.',
                'is_system' => true,
                'grants_full_access' => false,
                'sort_order' => 2,
            ],
            [
                'slug' => UserRole::Viewer->value,
                'name' => 'Viewer',
                'description' => 'Read-only access to admin content.',
                'is_system' => true,
                'grants_full_access' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($roles as $role) {
            if (SeedConfig::shouldOverwriteSettings()) {
                Role::query()->updateOrCreate(
                    ['slug' => $role['slug']],
                    $role,
                );
            } else {
                Role::query()->firstOrCreate(
                    ['slug' => $role['slug']],
                    $role,
                );
            }
        }
    }
}
