<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\SeedConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SiteEnsureAdminCommand extends Command
{
    protected $signature = 'site:ensure-admin
                            {--force : Run without confirmation in production}
                            {--reset-password : Reset the admin password to the bootstrap default}';

    protected $description = 'Ensure the primary admin account exists and can sign in';

    public function handle(): int
    {
        if (! Schema::hasTable('users')) {
            $this->components->error('The users table is missing. Run: php artisan migrate --force');

            return self::FAILURE;
        }

        $email = strtolower(trim((string) config('site.admin_email', 'admin@steciuk.org')));

        if ($email === '') {
            $this->components->error('Set ADMIN_EMAIL in .env before running site:ensure-admin.');

            return self::FAILURE;
        }

        $user = User::query()->firstOrNew(['email' => $email]);

        $user->fill([
            'name' => $user->name ?: 'Site Administrator',
            'role' => UserRole::SuperAdmin,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        $shouldResetPassword = $this->option('reset-password')
            || ! $user->exists
            || SeedConfig::shouldOverwritePasswords();

        if ($shouldResetPassword) {
            $user->password = 'password';
        }

        $user->save();

        if ($shouldResetPassword) {
            $this->components->warn("Admin password set to the bootstrap default for {$email}. Change it after signing in.");
        }

        $this->components->info("Admin account ready: {$email}");

        return self::SUCCESS;
    }
}
