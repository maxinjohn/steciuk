<?php

namespace App\Providers;

use App\Console\Commands\OptimizeSqliteCommand;
use App\Console\Commands\RepairSqliteCommand;
use App\Console\Commands\SiteBootstrapCommand;
use App\Console\Commands\SiteBootstrapIfEmptyCommand;
use App\Console\Commands\SiteDoctorCommand;
use App\Console\Commands\SiteEnsureAdminCommand;
use App\Console\Commands\SiteEnsurePathsCommand;
use App\Console\Commands\SiteEnsureRolesCommand;
use App\Console\Commands\SiteSyncReferenceDataCommand;
use Illuminate\Support\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            OptimizeSqliteCommand::class,
            RepairSqliteCommand::class,
            SiteBootstrapCommand::class,
            SiteBootstrapIfEmptyCommand::class,
            SiteDoctorCommand::class,
            SiteEnsureAdminCommand::class,
            SiteEnsurePathsCommand::class,
            SiteEnsureRolesCommand::class,
            SiteSyncReferenceDataCommand::class,
        ]);
    }
}
