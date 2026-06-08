<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            SettingsSeeder::class,
            ServiceSeeder::class,
            MinistrySeeder::class,
            LeadershipSeeder::class,
            PageSeeder::class,
            MenuSeeder::class,
            EventSeeder::class,
            NewsSeeder::class,
            SermonSeeder::class,
            ResourceSeeder::class,
            GallerySeeder::class,
        ]);
    }
}
