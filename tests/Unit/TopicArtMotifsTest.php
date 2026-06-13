<?php

namespace Tests\Unit;

use App\Support\SiteTopicArt;
use App\Support\TopicArtGenerator;
use App\Support\TopicArtMotifs;
use Tests\TestCase;

class TopicArtMotifsTest extends TestCase
{
    public function test_cms_slugs_resolve_to_distinct_topics(): void
    {
        $this->assertSame('online-worship', SiteTopicArt::resolve('online-worship', 'Online Worship', 'page'));
        $this->assertSame('choir', SiteTopicArt::resolve('choir', 'Parish Choir', 'page'));
        $this->assertSame('welcome', SiteTopicArt::resolve('welcome', 'Welcome', 'page'));
        $this->assertSame('leadership', SiteTopicArt::resolve('leadership', 'Leadership', 'page'));
        $this->assertNotSame(
            SiteTopicArt::resolve('choir', 'Parish Choir', 'page'),
            SiteTopicArt::resolve('online-worship', 'Online Worship', 'page'),
        );
    }

    public function test_different_pages_produce_different_svg_output(): void
    {
        $choir = TopicArtGenerator::render('choir', 'choir-parish-choir', 'Parish Choir', 'Worship through music');
        $online = TopicArtGenerator::render('online-worship', 'online-worship-online-worship', 'Online Worship', 'Join us from wherever you are live stream');

        $this->assertNotSame($choir, $online);
        $this->assertStringContainsString('<ellipse', $choir);
        $this->assertStringContainsString('rx="16"', $online);
    }

    public function test_cms_pages_use_content_specific_motifs(): void
    {
        $pages = [
            ['our-church', 'Our Church', 'M-58 10 L0 -56 L58 10z'],
            ['service-times', 'Service Times', 'r="52"'],
            ['online-worship', 'Online Worship', 'rx="16"'],
            ['steci-heritage', 'STECI Heritage', 'M-34 28h68v20'],
            ['liturgy', 'Liturgy', 'M0 -68 L0 -52M-8 -60 L8 -60'],
            ['gallery', 'Gallery', 'rx="6"'],
            ['give', 'Give', 'rx="38"'],
            ['safeguarding', 'Safeguarding', 'M-18 8 L-4 22 L22 -8'],
            ['womens-fellowship', "Women's Fellowship", 'cy="-22"'],
            ['prayer-request', 'Prayer Request', 'M0 28 C-8 20'],
            ['contact', 'Contact', 'M-36 50 C-36 58'],
            ['login', 'Sign In', 'M42 -12 L52 -2'],
            ['register', 'Create Your Parish Account', 'M-48 48 L-48 8'],
            ['new-member', 'Join the Parish', 'M-48 48 L-48 8'],
        ];

        foreach ($pages as [$slug, $title, $needle]) {
            $topic = SiteTopicArt::resolve($slug, $title, 'page');
            $svg = TopicArtGenerator::render($topic, $slug.'-'.$title, $title, 'Parish page content');

            $this->assertStringContainsString(
                $needle,
                $svg,
                "Expected motif marker [{$needle}] for {$slug} (topic: {$topic})",
            );
        }
    }

    public function test_home_headline_gets_church_motif(): void
    {
        $svg = TopicArtGenerator::render(
            'home',
            'home-word-worship-witness',
            'Word · Worship · Witness',
            'For the Word of God and for the testimony of Jesus Christ',
        );

        $this->assertStringContainsString('M-58 10 L0 -56 L58 10z', $svg);
        $this->assertStringContainsString('M0 -104 L0 -86M-10 -95 L10 -95', $svg);
        $this->assertStringContainsString('M-16 64 Q0 48 16 64', $svg);
    }

    public function test_motif_layer_is_unique_per_seed(): void
    {
        $a = TopicArtMotifs::render('event', 'alpha', 'Alpha Event', 'A gathering');
        $b = TopicArtMotifs::render('event', 'beta', 'Beta Event', 'Another gathering');

        $this->assertNotSame($a, $b);
    }

    public function test_content_hash_changes_media_url_seed(): void
    {
        $without = SiteTopicArt::seed('choir', 'Choir', null);
        $with = SiteTopicArt::seed('choir', 'Choir', 'Leading worship through hymns each month.');

        $this->assertNotSame($without, $with);
    }
}
