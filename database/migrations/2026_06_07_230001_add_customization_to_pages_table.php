<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->text('custom_css')->nullable()->after('content');
            $table->text('custom_js')->nullable()->after('custom_css');
            $table->string('accent_color')->default('gold')->after('template');
            $table->string('layout_variant')->default('standard')->after('accent_color');
            $table->string('hero_style')->default('gradient')->after('layout_variant');
            $table->boolean('show_hero')->default(true)->after('hero_style');
            $table->string('meta_robots')->nullable()->after('seo_description');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn([
                'custom_css',
                'custom_js',
                'accent_color',
                'layout_variant',
                'hero_style',
                'show_hero',
                'meta_robots',
            ]);
        });
    }
};
