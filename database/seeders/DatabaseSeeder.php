<?php

namespace Database\Seeders;

use App\Support\SeedConfig;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (! SeedConfig::isActive()) {
            $this->command?->warn('Seeding skipped (SEED_MODE=off). Run php artisan migrate to apply reference content.');

            return;
        }

        $this->call(ReferenceDataSeeder::class);
    }
}
