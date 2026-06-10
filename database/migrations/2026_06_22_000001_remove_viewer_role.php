<?php

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        User::query()
            ->where('role', 'viewer')
            ->update(['role' => UserRole::Member->value]);

        if (Schema::hasTable('roles')) {
            Role::query()->where('slug', 'viewer')->delete();
        }

        $stored = Setting::get('role_permissions');
        $matrix = is_string($stored) ? json_decode($stored, true) : $stored;

        if (is_array($matrix) && array_key_exists('viewer', $matrix)) {
            unset($matrix['viewer']);
            Setting::set('role_permissions', $matrix, 'security');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        Role::query()->firstOrCreate(
            ['slug' => 'viewer'],
            [
                'name' => 'Viewer',
                'description' => 'Read-only access to admin content.',
                'is_system' => true,
                'grants_full_access' => false,
                'sort_order' => 4,
            ],
        );
    }
};
