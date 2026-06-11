<?php

namespace App\Console\Commands;

use App\Support\SitePaths;
use Illuminate\Console\Command;

class SiteDoctorCommand extends Command
{
    protected $signature = 'site:doctor';

    protected $description = 'Check production readiness for storage, database, and public uploads';

    public function handle(): int
    {
        $checks = SitePaths::productionChecks();
        $failed = 0;

        $this->components->info('Production readiness checks');

        foreach ($checks as $check) {
            if ($check['status'] === 'ok') {
                $this->components->twoColumnDetail($check['label'], $check['detail']);

                continue;
            }

            $failed++;
            $this->components->error($check['label'].': '.$check['detail']);
        }

        $this->newLine();

        if ($failed > 0) {
            $this->components->warn('Fix the failed checks above, then run:');
            $this->line('  php artisan site:ensure-paths --link');
            $this->line('  php artisan config:cache');

            return self::FAILURE;
        }

        $this->components->info('All checks passed.');

        return self::SUCCESS;
    }
}
