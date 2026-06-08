<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@steciuk.org'],
            [
                'name' => 'Site Administrator',
                'password' => 'password',
                'role' => UserRole::SuperAdmin,
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'editor@steciuk.org'],
            [
                'name' => 'Content Editor',
                'password' => 'password',
                'role' => UserRole::Editor,
                'email_verified_at' => now(),
            ],
        );
    }
}
