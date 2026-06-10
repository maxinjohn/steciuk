<?php

use App\Enums\UserRole;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! Role::tableExists()) {
            return;
        }

        Role::query()->updateOrCreate(
            ['slug' => UserRole::Admin->value],
            [
                'name' => 'Admin',
                'description' => 'Parish administrator with access to the admin panel, team management, and content.',
                'is_system' => true,
                'grants_full_access' => false,
                'sort_order' => 2,
            ],
        );

        Role::query()->updateOrCreate(
            ['slug' => UserRole::Member->value],
            [
                'name' => 'Member',
                'description' => 'Registered parish member with a personal account on the public website.',
                'is_system' => true,
                'grants_full_access' => false,
                'sort_order' => 5,
            ],
        );

        Role::query()
            ->where('slug', UserRole::Editor->value)
            ->update(['sort_order' => 3]);

        Role::query()
            ->where('slug', UserRole::Member->value)
            ->update(['sort_order' => 4]);

        Role::query()
            ->where('slug', UserRole::SuperAdmin->value)
            ->update(['sort_order' => 1]);
    }

    public function down(): void
    {
        if (! Role::tableExists()) {
            return;
        }

        Role::query()->whereIn('slug', [
            UserRole::Admin->value,
            UserRole::Member->value,
        ])->delete();
    }
};
