<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pages') || ! Schema::hasColumn('pages', 'show_in_menu')) {
            return;
        }

        \App\Services\NavigationMenuSync::backfillPageDefaults();
        \App\Services\NavigationMenuSync::applyAll();
    }

    public function down(): void
    {
        // Menu visibility is managed by admin settings after backfill.
    }
};
