<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Support\ReferenceSiteContent;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ReferenceSiteContent::services() as $service) {
            Service::query()->updateOrCreate(
                ['location' => $service['location']],
                $service,
            );
        }
    }
}
