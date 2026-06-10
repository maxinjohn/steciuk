<?php

use App\Database\ReferenceSiteContentMigrator;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        ReferenceSiteContentMigrator::apply();
    }

    public function down(): void
    {
        // Reference menus are not reverted on rollback.
    }
};
