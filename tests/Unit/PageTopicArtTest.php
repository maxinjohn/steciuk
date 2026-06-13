<?php

namespace Tests\Unit;

use App\Models\Page;
use App\Support\PageTopicArt;
use App\Support\SiteTopicArt;
use Tests\TestCase;

class PageTopicArtTest extends TestCase
{
    public function test_cms_page_slug_maps_to_topic_art(): void
    {
        $this->assertSame('choir', PageTopicArt::resolveTopic('choir', 'Parish Choir', 'page'));
        $this->assertSame('sunday-school', PageTopicArt::resolveTopic('sunday-school', 'Sunday School', 'page'));
    }

    public function test_new_page_resolves_topic_from_title_and_content(): void
    {
        $topic = PageTopicArt::resolveTopic(
            'family-retreat-weekend',
            'Family Retreat Weekend',
            'page',
            '<p>A parish fellowship day with prayer, worship, and community meal for all ages.</p>',
        );

        $this->assertSame('community-fellowship', $topic);
    }

    public function test_media_url_uses_dynamic_topic_art_route(): void
    {
        $url = PageTopicArt::mediaUrl('choir', 'Parish Choir', 'ministry');

        $this->assertStringContainsString('/topic-art/choir/', $url);
    }

    public function test_page_save_normalizes_image_style_without_upload(): void
    {
        $page = new Page([
            'title' => 'Youth Fellowship',
            'slug' => 'youth-fellowship',
            'hero_style' => 'image',
            'featured_image' => null,
        ]);

        PageTopicArt::syncHeroStyleForTopicArt($page);

        $this->assertSame('gradient', $page->hero_style);
    }

    public function test_content_hint_improves_site_topic_art_resolution(): void
    {
        $this->assertSame(
            'prayer-groups',
            SiteTopicArt::resolve('weekly-update', 'Weekly Update', 'news', null, 'Join our monthly prayer meeting online.'),
        );
    }

    public function test_content_hint_for_page_merges_hero_and_body(): void
    {
        $hint = PageTopicArt::contentHint(
            '<p>Monthly Holy Communion across Manchester and Leicester.</p>',
            'Word, worship, and witness',
            'Parish worship information',
        );

        $this->assertStringContainsString('Holy Communion', $hint);
        $this->assertStringContainsString('Word, worship', $hint);
        $this->assertSame(
            'communion',
            SiteTopicArt::resolve('parish-worship-info', 'Parish Worship Info', 'page', null, $hint),
        );
    }

    public function test_card_media_url_passes_content_for_new_event(): void
    {
        $url = cardMediaUrl(
            null,
            'family-picnic-day',
            'Family Picnic Day',
            'event',
            'Fellowship',
            'A parish fellowship meal and picnic for all ages after worship.',
        );

        $this->assertStringContainsString('/topic-art/community-fellowship/', $url);
        $this->assertStringContainsString('c=', $url);
    }
}
