<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\Setting;
use App\Services\ParishEmailService;
use Database\Seeders\DesignationSeeder;
use Database\Seeders\PanelSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SiteEnsureRolesCommand extends Command
{
    protected $signature = 'site:ensure-roles
                            {--force : Run without confirmation in production}';

    protected $description = 'Ensure built-in roles, designations, and panels exist (adds Vicar on older installs)';

    public function handle(): int
    {
        if (! Schema::hasTable('roles')) {
            $this->components->error('The roles table is missing. Run: php artisan migrate --force');

            return self::FAILURE;
        }

        $force = $this->option('force') || app()->environment('production');

        $this->components->info('Ensuring built-in roles…');
        $this->callSilent('db:seed', [
            '--class' => RoleSeeder::class,
            '--force' => $force,
        ]);

        if (Schema::hasTable('designations')) {
            $this->components->info('Ensuring default designations…');
            $this->callSilent('db:seed', [
                '--class' => DesignationSeeder::class,
                '--force' => $force,
            ]);
        }

        if (Schema::hasTable('panels')) {
            $this->components->info('Ensuring default panels…');
            $this->callSilent('db:seed', [
                '--class' => PanelSeeder::class,
                '--force' => $force,
            ]);
        }

        if (Schema::hasTable('settings')) {
            $this->components->info('Ensuring default parish email templates…');
            app(ParishEmailService::class)->seedDefaultsIfMissing();
        }

        Setting::forgetCache();

        if (! Role::query()->where('slug', UserRole::Vicar->value)->exists()) {
            $this->components->error('Vicar role is still missing. Check database permissions and run migrate again.');

            return self::FAILURE;
        }

        $this->components->info('Built-in roles ready: Super Admin, Admin, Vicar, Editor, Member.');

        return self::SUCCESS;
    }
}
