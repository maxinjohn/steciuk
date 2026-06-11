<?php

namespace Tests\Feature;

use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FullSiteSmokeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return list<string>
     */
    private function publicPaths(): array
    {
        return [
            '/',
            '/events',
            '/news',
            '/sermons',
            '/ministries',
            '/gallery',
            '/resources',
            '/service-times',
            '/welcome',
            '/our-church',
            '/steci-heritage',
            '/mission-vision',
            '/leadership',
            '/uk-locations',
            '/service-times',
            '/online-worship',
            '/contact',
            '/prayer-request',
            '/register',
            '/login',
            '/registration/pending',
            '/safeguarding',
            '/privacy-policy',
            '/terms-of-use',
            '/sitemap.xml',
            '/robots.txt',
            '/manifest.webmanifest',
        ];
    }

    public function test_all_core_public_routes_return_success_after_seed(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        foreach ($this->publicPaths() as $path) {
            $response = $this->get($path);

            $response->assertOk("Expected 200 for {$path}, got {$response->status()}");
        }
    }

    public function test_gallery_album_and_ministry_show_pages_load(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $this->get(route('gallery.show', 'parish-worship-services'))->assertOk();
        $this->get(route('ministries.show', 'sunday-school'))->assertOk();
    }

    public function test_legacy_new_member_url_redirects_to_register(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $this->get('/new-member')
            ->assertRedirect(route('register'));
    }

    public function test_unknown_slug_returns_404_not_500(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $this->get('/this-page-does-not-exist-at-all')->assertNotFound();
    }
}
