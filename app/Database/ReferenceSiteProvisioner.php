<?php

namespace App\Database;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\Page;
use App\Models\User;
use App\Support\SeedConfig;
use Database\Seeders\PageSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Schema;

/**
 * Ensures core site structure exists. Runs during migrate — no separate artisan commands.
 */
class ReferenceSiteProvisioner
{
    public static function ensureStructure(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        (new RoleSeeder)->run();
        static::ensureAdminUser();

        if (Schema::hasTable('pages') && ! Page::query()->where('slug', 'home')->exists()) {
            $previousMode = config('site.seed.mode');
            config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);

            try {
                (new PageSeeder)->run();
            } finally {
                config(['site.seed.mode' => $previousMode]);
            }
        }
    }

    private static function ensureAdminUser(): void
    {
        $email = strtolower(trim((string) config('site.admin_email', 'admin@steciuk.org')));

        if ($email === '') {
            return;
        }

        $user = User::query()->firstOrNew(['email' => $email]);

        $attributes = [
            'name' => $user->name ?: 'Site Administrator',
            'role' => UserRole::SuperAdmin,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ];

        if (Schema::hasColumn('users', 'account_status')) {
            $attributes['account_status'] = AccountStatus::Approved->value;
            $attributes['approved_at'] = $user->approved_at ?? now();
        }

        $user->fill($attributes);

        if (! $user->exists) {
            $user->password = 'password';
        }

        $user->save();
    }
}
