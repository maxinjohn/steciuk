<?php

namespace Tests\Unit;

use App\Database\ReferenceDataMigrator;
use App\Models\Event;
use App\Models\GalleryAlbum;
use App\Models\News;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceDataMigratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_sync_is_false_after_fresh_migrate(): void
    {
        $this->assertFalse(ReferenceDataMigrator::needsSync());
    }

    public function test_needs_sync_detects_missing_gallery_and_settings(): void
    {
        GalleryAlbum::query()->delete();
        Setting::query()->where('key', 'church_name')->delete();

        $this->assertTrue(ReferenceDataMigrator::needsSync());
    }

    public function test_sync_restores_missing_reference_anchors(): void
    {
        Event::query()->where('slug', 'uk-parish-fellowship-day')->delete();
        News::query()->where('slug', 'lent-prayer-week-uk-parish')->delete();
        GalleryAlbum::query()->delete();

        $this->assertTrue(ReferenceDataMigrator::needsSync());

        ReferenceDataMigrator::sync();

        $this->assertFalse(ReferenceDataMigrator::needsSync());
        $this->assertDatabaseHas('gallery_albums', ['slug' => 'parish-worship-services']);
        $this->assertDatabaseHas('events', ['slug' => 'uk-parish-fellowship-day']);
        $this->assertDatabaseHas('news', ['slug' => 'lent-prayer-week-uk-parish']);
    }
}
