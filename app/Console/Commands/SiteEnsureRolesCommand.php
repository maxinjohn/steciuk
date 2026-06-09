<?php

namespace App\Console\Commands;

use App\Models\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SiteEnsureRolesCommand extends Command
{
    protected $signature = 'site:ensure-roles
                            {--force : Run without confirmation in production}';

    protected $description = 'Ensure system roles exist after migrations (fixes empty roles table on existing installs)';

    public function handle(): int
    {
        if (! Schema::hasTable('roles')) {
            $this->components->error('The roles table is missing. Run: php artisan migrate --force');

            return self::FAILURE;
        }

        if (Role::query()->exists()) {
            $this->components->info('System roles already present.');

            return self::SUCCESS;
        }

        $this->components->warn('Roles table is empty — seeding built-in roles.');

        $this->callSilent('db:seed', [
            '--class' => RoleSeeder::class,
            '--force' => $this->option('force') || app()->environment('production'),
        ]);

        \App\Models\Setting::forgetCache();

        $this->components->info('Built-in roles created: Super Admin, Editor, Viewer.');

        return self::SUCCESS;
    }
}
