<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\SeedConfig;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUser(
            email: 'admin@steciuk.org',
            name: 'Site Administrator',
            role: UserRole::SuperAdmin,
        );

        $this->seedUser(
            email: 'editor@steciuk.org',
            name: 'Content Editor',
            role: UserRole::Editor,
        );
    }

    private function seedUser(string $email, string $name, UserRole $role): void
    {
        $user = User::query()->firstOrNew(['email' => $email]);

        $user->fill([
            'name' => $name,
            'role' => $role,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        if (! $user->exists || SeedConfig::shouldOverwritePasswords()) {
            $user->password = 'password';
        }

        $user->save();
    }
}
