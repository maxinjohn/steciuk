<?php

namespace Tests\Feature;

use App\Enums\PublishStatus;
use App\Models\Event;
use App\Models\GalleryAlbum;
use App\Models\Ministry;
use App\Models\News;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_includes_published_pages_and_content(): void
    {
        $page = Page::factory()->create([
            'title' => 'About Us',
            'slug' => 'about-us',
            'status' => PublishStatus::Published,
            'is_home' => false,
        ]);

        $event = Event::factory()->create([
            'title' => 'Parish Picnic',
            'slug' => 'parish-picnic',
            'status' => PublishStatus::Published,
        ]);

        $news = News::factory()->create([
            'title' => 'Parish Update',
            'slug' => 'parish-update',
            'status' => PublishStatus::Published,
        ]);

        $ministry = Ministry::factory()->create([
            'name' => 'Youth Ministry',
            'slug' => 'youth-ministry',
            'status' => 'published',
        ]);

        $album = GalleryAlbum::factory()->create([
            'title' => 'Easter Service',
            'slug' => 'easter-service',
            'status' => 'published',
        ]);

        $response = $this->get(route('sitemap'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $xml = $response->getContent();

        $this->assertStringContainsString(route('home'), $xml);
        $this->assertStringContainsString(route('pages.show', $page->slug), $xml);
        $this->assertStringContainsString(route('events.show', $event->slug), $xml);
        $this->assertStringContainsString(route('news.show', $news->slug), $xml);
        $this->assertStringContainsString(route('ministries.show', $ministry->slug), $xml);
        $this->assertStringContainsString(route('gallery.show', $album->slug), $xml);
        $this->assertStringContainsString(route('services.index'), $xml);
    }

    public function test_sitemap_excludes_noindex_pages(): void
    {
        Page::factory()->create([
            'title' => 'Private Draft',
            'slug' => 'private-draft',
            'status' => PublishStatus::Published,
            'meta_robots' => 'noindex, nofollow',
        ]);

        $response = $this->get(route('sitemap'));

        $response->assertOk();
        $this->assertStringNotContainsString(route('pages.show', 'private-draft'), $response->getContent());
    }

    public function test_robots_txt_is_dynamic_and_points_to_sitemap(): void
    {
        $response = $this->get(route('robots'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

        $body = $response->getContent();

        $this->assertStringContainsString('User-agent: *', $body);
        $this->assertStringContainsString('Disallow: /admin', $body);
        $this->assertStringContainsString('Sitemap: '.route('sitemap'), $body);
        $this->assertStringContainsString('Host: '.rtrim(config('app.url'), '/'), $body);
    }
}
