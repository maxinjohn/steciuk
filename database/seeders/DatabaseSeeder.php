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
            $this->command?->warn('Manual seeding skipped (SEED_MODE=off). Reference data is applied by php artisan migrate.');

            return;
        }

        $this->call(ReferenceDataSeeder::class);
    }
}
