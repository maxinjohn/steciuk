<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pages', 'show_in_menu')) {
            Schema::table('pages', function (Blueprint $table): void {
                $table->boolean('show_in_menu')->default(false)->after('is_home');
                $table->string('menu_parent_seed_key')->nullable()->after('show_in_menu');
                $table->string('menu_label')->nullable()->after('menu_parent_seed_key');
                $table->unsignedInteger('menu_sort_order')->nullable()->after('menu_label');
            });
        }

        if (! Schema::hasColumn('ministries', 'show_in_menu')) {
            Schema::table('ministries', function (Blueprint $table): void {
                $table->boolean('show_in_menu')->default(false)->after('status');
                $table->string('menu_parent_seed_key')->nullable()->after('show_in_menu');
                $table->string('menu_label')->nullable()->after('menu_parent_seed_key');
                $table->unsignedInteger('menu_sort_order')->nullable()->after('menu_label');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pages', 'show_in_menu')) {
            Schema::table('pages', function (Blueprint $table): void {
                $table->dropColumn(['show_in_menu', 'menu_parent_seed_key', 'menu_label', 'menu_sort_order']);
            });
        }

        if (Schema::hasColumn('ministries', 'show_in_menu')) {
            Schema::table('ministries', function (Blueprint $table): void {
                $table->dropColumn(['show_in_menu', 'menu_parent_seed_key', 'menu_label', 'menu_sort_order']);
            });
        }
    }
};
