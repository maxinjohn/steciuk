<?php

namespace Database\Seeders;

use App\Models\Panel;
use App\Support\SeedConfig;
use Illuminate\Database\Seeder;

class PanelSeeder extends Seeder
{
    public function run(): void
    {
        if (! Panel::tableExists()) {
            return;
        }

        $panels = [
            [
                'slug' => 'parish-committee',
                'name' => 'Parish Committee',
                'description' => 'Core parish leadership and governance panel.',
                'sort_order' => 1,
            ],
            [
                'slug' => 'parish-admin-panel',
                'name' => 'Parish Admin Panel',
                'description' => 'Administrative panel supporting parish operations.',
                'sort_order' => 2,
            ],
            [
                'slug' => 'choir-members',
                'name' => 'Choir Members',
                'description' => 'Parish choir and music ministry members.',
                'sort_order' => 3,
            ],
        ];

        foreach ($panels as $panel) {
            $payload = array_merge($panel, ['is_system' => true]);

            if (SeedConfig::shouldOverwriteSettings()) {
                Panel::query()->updateOrCreate(['slug' => $panel['slug']], $payload);
            } else {
                Panel::query()->firstOrCreate(['slug' => $panel['slug']], $payload);
            }
        }
    }
}
