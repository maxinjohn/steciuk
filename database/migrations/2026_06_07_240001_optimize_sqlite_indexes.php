<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('pages', 'pages_home_status_index')) {
            Schema::table('pages', function (Blueprint $table): void {
                $table->index(['is_home', 'status'], 'pages_home_status_index');
            });
        }

        if (! Schema::hasIndex('content_blocks', 'content_blocks_page_visible_sort_index')) {
            Schema::table('content_blocks', function (Blueprint $table): void {
                $table->index(['page_id', 'is_visible', 'sort_order'], 'content_blocks_page_visible_sort_index');
            });
        }

        if (! Schema::hasIndex('menu_items', 'menu_items_location_visible_index')) {
            Schema::table('menu_items', function (Blueprint $table): void {
                $table->index(['menu_location', 'is_visible', 'parent_id', 'sort_order'], 'menu_items_location_visible_index');
            });
        }

        if (! Schema::hasIndex('events', 'events_status_starts_index')) {
            Schema::table('events', function (Blueprint $table): void {
                $table->index(['status', 'starts_at'], 'events_status_starts_index');
            });
        }

        if (! Schema::hasIndex('news', 'news_status_published_index')) {
            Schema::table('news', function (Blueprint $table): void {
                $table->index(['status', 'published_at'], 'news_status_published_index');
            });
        }

        if (! Schema::hasIndex('sermons', 'sermons_status_preached_index')) {
            Schema::table('sermons', function (Blueprint $table): void {
                $table->index(['status', 'preached_at'], 'sermons_status_preached_index');
            });
        }

        DB::table('pages')
            ->where('is_home', true)
            ->update([
                'show_hero' => false,
                'hero_title' => null,
                'hero_subtitle' => null,
                'content' => null,
            ]);

        $homePageId = DB::table('pages')->where('is_home', true)->value('id');

        if ($homePageId) {
            $heroContent = json_encode([
                'eyebrow' => 'St. Thomas Evangelical Church of India',
                'headline' => 'Welcome to Our UK Parish',
                'subtitle' => 'For the Word of God and for the testimony of Jesus Christ',
                'badge' => 'UK Parish',
                'stats' => [
                    ['value' => '5', 'label' => 'UK Locations'],
                    ['value' => '90+', 'label' => 'Families'],
                    ['value' => '1961', 'label' => 'STECI Founded'],
                ],
                'primary_cta_label' => 'Plan Your Visit',
                'primary_cta_url' => '/service-times',
                'secondary_cta_label' => 'Watch Online',
                'secondary_cta_url' => '/online-worship',
                'tertiary_cta_label' => 'View Events',
                'tertiary_cta_url' => '/events',
            ], JSON_THROW_ON_ERROR);

            DB::table('content_blocks')
                ->where('page_id', $homePageId)
                ->where('type', 'hero')
                ->update(['content' => $heroContent]);
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('pages', 'pages_home_status_index')) {
            Schema::table('pages', function (Blueprint $table): void {
                $table->dropIndex('pages_home_status_index');
            });
        }

        if (Schema::hasIndex('content_blocks', 'content_blocks_page_visible_sort_index')) {
            Schema::table('content_blocks', function (Blueprint $table): void {
                $table->dropIndex('content_blocks_page_visible_sort_index');
            });
        }

        if (Schema::hasIndex('menu_items', 'menu_items_location_visible_index')) {
            Schema::table('menu_items', function (Blueprint $table): void {
                $table->dropIndex('menu_items_location_visible_index');
            });
        }

        if (Schema::hasIndex('events', 'events_status_starts_index')) {
            Schema::table('events', function (Blueprint $table): void {
                $table->dropIndex('events_status_starts_index');
            });
        }

        if (Schema::hasIndex('news', 'news_status_published_index')) {
            Schema::table('news', function (Blueprint $table): void {
                $table->dropIndex('news_status_published_index');
            });
        }

        if (Schema::hasIndex('sermons', 'sermons_status_preached_index')) {
            Schema::table('sermons', function (Blueprint $table): void {
                $table->dropIndex('sermons_status_preached_index');
            });
        }
    }
};
