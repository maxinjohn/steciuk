<?php

namespace App\Database;

use App\Models\ContentBlock;
use App\Models\Page;
use App\Models\Setting;
use App\Services\SiteCache;
use App\Support\ReferenceSiteContent;
use Illuminate\Support\Facades\Schema;

class ReferenceSiteContentMigrator
{
    public static function apply(): void
    {
        static::applySettings();
        static::applyPages();
        static::applyHomeContentBlocks();

        SiteCache::forgetAfterReferenceDataChange();
    }

    private static function applySettings(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        foreach (ReferenceSiteContent::settings() as $key => $data) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $data['value'], 'group' => $data['group']],
            );
        }
    }

    private static function applyPages(): void
    {
        if (! Schema::hasTable('pages')) {
            return;
        }

        foreach (ReferenceSiteContent::pageBodies() as $slug => $content) {
            Page::query()->where('slug', $slug)->update(['content' => $content]);
        }

        foreach (ReferenceSiteContent::pageFields() as $slug => $fields) {
            Page::query()->where('slug', $slug)->update($fields);
        }
    }

    private static function applyHomeContentBlocks(): void
    {
        if (! Schema::hasTable('content_blocks') || ! Schema::hasTable('pages')) {
            return;
        }

        $homeId = Page::query()->where('slug', 'home')->value('id');

        if (! $homeId) {
            return;
        }

        foreach (ReferenceSiteContent::homeContentBlockPatches() as $seedKey => $patch) {
            $block = ContentBlock::query()
                ->where('page_id', $homeId)
                ->where('seed_key', $seedKey)
                ->first();

            if (! $block) {
                continue;
            }

            $content = array_merge($block->content ?? [], $patch);
            $block->update(['content' => $content]);
        }
    }
}
