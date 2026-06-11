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
                'slug' => UserRole::Admin->value,
                'name' => 'Admin',
                'description' => 'Parish administrator with full admin access. Cannot manage the Super Admin account.',
                'is_system' => true,
                'grants_full_access' => false,
                'sort_order' => 2,
            ],
            [
                'slug' => UserRole::Vicar->value,
                'name' => 'Vicar',
                'description' => 'Predefined parish vicar role. Permissions can be edited; the role name stays fixed.',
                'is_system' => true,
                'grants_full_access' => false,
                'sort_order' => 3,
            ],
            [
                'slug' => UserRole::Editor->value,
                'name' => 'Editor',
                'description' => 'Manage parish content — pages, worship, media, and messages.',
                'is_system' => true,
                'grants_full_access' => false,
                'sort_order' => 4,
            ],
            [
                'slug' => UserRole::Member->value,
                'name' => 'Member',
                'description' => 'Registered parish member with a personal account on the public website.',
                'is_system' => true,
                'grants_full_access' => false,
                'sort_order' => 5,
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
