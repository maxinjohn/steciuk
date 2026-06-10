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
        // Reference copy is not reverted on rollback.
    }
};
