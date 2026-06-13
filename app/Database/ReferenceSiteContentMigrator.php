<?php

namespace App\Database;

use App\Models\ContentBlock;
use App\Models\Page;
use App\Models\Service;
use App\Models\Setting;
use App\Services\SiteCache;
use App\Enums\ContentBlockType;
use App\Support\ReferenceSiteContent;
use App\Support\SiteBrandingAssets;
use Illuminate\Support\Facades\Schema;

class ReferenceSiteContentMigrator
{
    public static function apply(): void
    {
        ReferenceSiteProvisioner::ensureStructure();

        static::applySettings();
        static::applyPages();
        static::applyPageHeroPresentation();
        static::applyHomeContentBlocks();
        static::applyServices();
        ReferenceMenuApplicator::apply();

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

        SiteBrandingAssets::syncDefaultLogoSetting();
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

    private static function applyPageHeroPresentation(): void
    {
        if (! Schema::hasTable('pages')) {
            return;
        }

        Page::query()
            ->where('is_home', false)
            ->whereIn('slug', [
                'welcome', 'our-church', 'steci-heritage', 'mission-vision', 'leadership', 'uk-locations',
                'service-times', 'online-worship', 'sermons', 'ministries', 'sunday-school', 'youth-fellowship',
                'womens-fellowship', 'choir', 'prayer-groups', 'events', 'news', 'resources', 'liturgy',
                'lectionary', 'gallery', 'contact', 'prayer-request', 'new-member', 'membership', 'privacy-policy',
            ])
            ->update([
                'show_hero' => true,
                'hero_style' => 'gradient',
            ]);
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

        ContentBlock::query()
            ->where('page_id', $homeId)
            ->where('seed_key', 'news')
            ->update([
                'type' => ContentBlockType::NewsList,
                'content' => [
                    'heading' => 'Latest News',
                    'subheading' => 'Gospel-centred news from across our five worship locations',
                    'limit' => 3,
                    'link_url' => '/news',
                    'link_label' => 'Read All News',
                ],
            ]);
    }

    private static function applyServices(): void
    {
        if (! Schema::hasTable('services')) {
            return;
        }

        foreach (ReferenceSiteContent::services() as $service) {
            Service::query()->updateOrCreate(
                ['location' => $service['location']],
                $service,
            );
        }
    }
}
