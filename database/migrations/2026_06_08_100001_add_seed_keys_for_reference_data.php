<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('seed_key')->nullable()->after('menu_location');
            $table->unique(['menu_location', 'seed_key']);
        });

        Schema::table('content_blocks', function (Blueprint $table) {
            $table->string('seed_key')->nullable()->after('page_id');
            $table->unique(['page_id', 'seed_key']);
        });
    }

    public function down(): void
    {
        Schema::table('content_blocks', function (Blueprint $table) {
            $table->dropUnique(['page_id', 'seed_key']);
            $table->dropColumn('seed_key');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropUnique(['menu_location', 'seed_key']);
            $table->dropColumn('seed_key');
        });
    }
};
