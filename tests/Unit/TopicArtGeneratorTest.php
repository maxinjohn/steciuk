<?php

namespace Tests\Unit;

use App\Support\SiteTopicArt;
use App\Support\TopicArtGenerator;
use Tests\TestCase;

class TopicArtGeneratorTest extends TestCase
{
    public function test_renders_valid_svg_for_topic_and_seed(): void
    {
        $svg = TopicArtGenerator::render('event', 'youth-summer-camp', 'Youth Summer Camp');

        $this->assertStringStartsWith('<svg', $svg);
        $this->assertStringContainsString('</svg>', $svg);
        $this->assertStringContainsString('viewBox="0 0 800 500"', $svg);
        $this->assertStringContainsString('Y', $svg);
    }

    public function test_seed_produces_distinct_palette(): void
    {
        $a = TopicArtGenerator::paletteFromSeed('alpha-event');
        $b = TopicArtGenerator::paletteFromSeed('omega-retreat-weekend');

        $this->assertNotSame($a, $b);
    }

    public function test_unknown_topic_falls_back_to_default_file(): void
    {
        $svg = TopicArtGenerator::render('not-a-real-topic', 'fallback', 'Test');

        $this->assertStringContainsString('<svg', $svg);
        $this->assertSame('default', TopicArtGenerator::normalizeTopic('not-a-real-topic'));
    }

    public function test_dynamic_media_url_includes_topic_and_seed(): void
    {
        $url = SiteTopicArt::mediaUrl('youth-retreat-2026', 'Youth Retreat', 'event', 'Youth');

        $this->assertStringContainsString('/topic-art/', $url);
        $this->assertStringContainsString('/youth-fellowship/', $url);
        $this->assertStringContainsString('youth-retreat-2026', $url);
    }

    public function test_category_affects_topic_resolution(): void
    {
        $this->assertSame(
            'prayer',
            SiteTopicArt::resolve('parish-update', 'Weekly update', 'news', 'Prayer'),
        );
    }
}
