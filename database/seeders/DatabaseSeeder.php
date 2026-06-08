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
            $this->command?->warn('Seeding skipped (SEED_MODE=off). Use site:bootstrap or site:sync-reference-data.');

            return;
        }

        $this->call(ReferenceDataSeeder::class);
    }
}
