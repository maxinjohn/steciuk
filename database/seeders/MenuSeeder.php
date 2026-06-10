<?php

namespace Database\Seeders;

use App\Database\ReferenceMenuApplicator;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        ReferenceMenuApplicator::apply();
    }
}
