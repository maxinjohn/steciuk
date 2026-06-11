<?php

namespace Database\Seeders;

use App\Models\Designation;
use App\Models\Panel;
use App\Support\SeedConfig;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    public function run(): void
    {
        if (! Designation::tableExists()) {
            return;
        }

        $designations = [
            ['slug' => 'vicar', 'name' => 'Vicar', 'description' => 'Parish vicar or minister.', 'sort_order' => 1],
            ['slug' => 'assistant-vicar', 'name' => 'Assistant Vicar', 'description' => 'Assistant or associate vicar.', 'sort_order' => 2],
            ['slug' => 'president', 'name' => 'President', 'description' => 'Parish committee or organisation president.', 'sort_order' => 3],
            ['slug' => 'treasurer', 'name' => 'Treasurer', 'description' => 'Parish treasurer or finance lead.', 'sort_order' => 4],
            ['slug' => 'cashier', 'name' => 'Cashier', 'description' => 'Giving or finance cashier.', 'sort_order' => 5],
            ['slug' => 'secretary', 'name' => 'Secretary', 'description' => 'Parish or committee secretary.', 'sort_order' => 6],
            ['slug' => 'churchwarden', 'name' => 'Churchwarden', 'description' => 'Elected churchwarden.', 'sort_order' => 7],
        ];

        foreach ($designations as $designation) {
            $payload = array_merge($designation, ['is_system' => true]);

            if (SeedConfig::shouldOverwriteSettings()) {
                Designation::query()->updateOrCreate(['slug' => $designation['slug']], $payload);
            } else {
                Designation::query()->firstOrCreate(['slug' => $designation['slug']], $payload);
            }
        }
    }
}
