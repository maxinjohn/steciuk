<?php

namespace App\Console\Commands;

use App\Services\LaunchModeService;
use App\Services\MaintenanceModeService;
use Illuminate\Console\Command;

class MaintenanceModeCommand extends Command
{
    protected $signature = 'site:maintenance {action? : on, off, or status}';

    protected $description = 'Enable, disable, or check public maintenance mode';

    public function handle(): int
    {
        $action = strtolower((string) ($this->argument('action') ?: 'status'));

        return match ($action) {
            'on', 'enable' => $this->enable(),
            'off', 'disable' => $this->disable(),
            'status' => $this->status(),
            default => $this->invalidAction($action),
        };
    }

    private function enable(): int
    {
        MaintenanceModeService::enable();
        $this->components->info('Maintenance mode is now ON. Admin login remains available at /admin/login');

        return self::SUCCESS;
    }

    private function disable(): int
    {
        MaintenanceModeService::disable();
        $this->components->info('Maintenance mode is now OFF. The public site is live again.');

        return self::SUCCESS;
    }

    private function status(): int
    {
        $maintenance = MaintenanceModeService::isEnabled() ? 'ON' : 'OFF';
        $launch = LaunchModeService::isEnabled()
            ? (LaunchModeService::isLaunched() ? 'ON (launched)' : 'ON (countdown active)')
            : 'OFF';

        $this->line("Maintenance mode: {$maintenance}");
        $this->line("Launch countdown: {$launch}");

        return self::SUCCESS;
    }

    private function invalidAction(string $action): int
    {
        $this->components->error("Unknown action [{$action}]. Use on, off, or status.");

        return self::FAILURE;
    }
}
