<?php

use Database\Seeders\DesignationSeeder;
use Database\Seeders\PanelSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('roles')) {
            (new RoleSeeder)->run();
        }

        if (Schema::hasTable('designations')) {
            (new DesignationSeeder)->run();
        }

        if (Schema::hasTable('panels')) {
            (new PanelSeeder)->run();
        }
    }

    public function down(): void
    {
        // Reference data is kept on rollback.
    }
};
