<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('services', 'services_status_sort_index')) {
            Schema::table('services', function (Blueprint $table): void {
                $table->index(['status', 'sort_order'], 'services_status_sort_index');
            });
        }

        if (! Schema::hasIndex('gallery_photos', 'gallery_photos_album_sort_index')) {
            Schema::table('gallery_photos', function (Blueprint $table): void {
                $table->index(['gallery_album_id', 'sort_order'], 'gallery_photos_album_sort_index');
            });
        }

        if (! Schema::hasIndex('ministries', 'ministries_status_sort_index')) {
            Schema::table('ministries', function (Blueprint $table): void {
                $table->index(['status', 'sort_order'], 'ministries_status_sort_index');
            });
        }

        if (! Schema::hasIndex('gallery_albums', 'gallery_albums_status_sort_index')) {
            Schema::table('gallery_albums', function (Blueprint $table): void {
                $table->index(['status', 'sort_order'], 'gallery_albums_status_sort_index');
            });
        }

        if (! Schema::hasIndex('resources', 'resources_status_sort_index')) {
            Schema::table('resources', function (Blueprint $table): void {
                $table->index(['status', 'sort_order'], 'resources_status_sort_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('services', 'services_status_sort_index')) {
            Schema::table('services', function (Blueprint $table): void {
                $table->dropIndex('services_status_sort_index');
            });
        }

        if (Schema::hasIndex('gallery_photos', 'gallery_photos_album_sort_index')) {
            Schema::table('gallery_photos', function (Blueprint $table): void {
                $table->dropIndex('gallery_photos_album_sort_index');
            });
        }

        if (Schema::hasIndex('ministries', 'ministries_status_sort_index')) {
            Schema::table('ministries', function (Blueprint $table): void {
                $table->dropIndex('ministries_status_sort_index');
            });
        }

        if (Schema::hasIndex('gallery_albums', 'gallery_albums_status_sort_index')) {
            Schema::table('gallery_albums', function (Blueprint $table): void {
                $table->dropIndex('gallery_albums_status_sort_index');
            });
        }

        if (Schema::hasIndex('resources', 'resources_status_sort_index')) {
            Schema::table('resources', function (Blueprint $table): void {
                $table->dropIndex('resources_status_sort_index');
            });
        }
    }
};
