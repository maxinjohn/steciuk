<?php

namespace Tests\Unit;

use App\Models\GalleryAlbum;
use App\Support\SiteTopicArt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteTopicArtTest extends TestCase
{
    use RefreshDatabase;
    public function test_resolves_ministry_slugs_to_topic_art(): void
    {
        $this->assertSame('sunday-school', SiteTopicArt::resolve('sunday-school', 'Sunday School', 'ministry'));
        $this->assertSame('choir', SiteTopicArt::resolve('choir', 'Choir', 'ministry'));
        $this->assertStringContainsString('/images/topics/choir.svg', SiteTopicArt::url('choir'));
    }

    public function test_resolves_keywords_from_titles(): void
    {
        $this->assertSame('prayer', SiteTopicArt::resolve('lent-prayer-week', 'Lent prayer week', 'event'));
        $this->assertSame('worship', SiteTopicArt::resolve('monthly-worship', 'Monthly worship service', 'event'));
        $this->assertSame('communion', SiteTopicArt::resolve('good-friday', 'Good Friday Service', 'event'));
        $this->assertSame('sunday-school', SiteTopicArt::resolve('vbs-2026', 'Vacation Bible School', 'event'));
        $this->assertSame('community-fellowship', SiteTopicArt::resolve('fellowship-days', 'Fellowship Days', 'event'));
        $this->assertSame('prayer-groups', SiteTopicArt::resolve('monthly-prayer', 'Monthly Prayer Meeting', 'event'));
    }

    public function test_auth_slugs_resolve_to_member_topics(): void
    {
        $this->assertSame('login', SiteTopicArt::resolve('login', 'Sign In', 'page'));
        $this->assertSame('register', SiteTopicArt::resolve('register', 'Create Your Parish Account', 'page'));
        $this->assertSame('new-member', SiteTopicArt::resolve('new-member', 'Join the Parish', 'page'));
        $this->assertSame('contact', SiteTopicArt::resolve('contact', 'Contact', 'page'));
        $this->assertSame('prayer-request', SiteTopicArt::resolve('prayer-request', 'Prayer Request', 'page'));
    }

    public function test_register_title_does_not_resolve_to_pastoral_care(): void
    {
        $this->assertSame(
            'register',
            SiteTopicArt::resolve('register', 'Create Your Parish Account', 'page', null, 'Register as a member of the parish'),
        );
    }

    public function test_gallery_cover_url_uses_topic_art_when_no_photo(): void
    {
        $album = GalleryAlbum::factory()->create([
            'cover_image' => null,
            'title' => 'Worship in Manchester',
            'slug' => 'worship-manchester',
        ]);

        $url = galleryCoverUrl(null, 'worship', $album);

        $this->assertStringContainsString('/topic-art/', $url);
        $this->assertTrue(galleryCoverIsTopicArt(null, $album));
    }

    public function test_card_media_url_uses_topic_art_when_no_upload(): void
    {
        $url = cardMediaUrl(null, 'manchester', 'Manchester Worship Service', 'service');

        $this->assertStringContainsString('/topic-art/worship-location/manchester', $url);

        $ministryUrl = cardMediaUrl(null, 'youth-fellowship', 'Youth Fellowship', 'ministry');

        $this->assertStringContainsString('/topic-art/youth-fellowship/youth-fellowship', $ministryUrl);
    }
}
