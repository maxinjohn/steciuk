<?php

namespace Tests\Feature;

use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicListingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_listing_pages_show_content(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $this->get(route('ministries.index'))
            ->assertOk()
            ->assertSee('Sunday School', false);

        $this->get(route('gallery.index'))
            ->assertOk()
            ->assertSee('Parish Worship Services', false);

        $this->get(route('resources.index'))
            ->assertOk()
            ->assertSee('Liturgy', false);

        $this->get(route('events.index'))
            ->assertOk();

        $this->get(route('news.index'))
            ->assertOk();

        $this->get(route('sermons.index'))
            ->assertOk();
    }

    public function test_leadership_page_shows_directory(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $this->get('/leadership')
            ->assertOk()
            ->assertSee('UK Parish Vicar', false)
            ->assertSee('leadership-grid', false);
    }

    public function test_listing_pages_include_scripture_ribbon(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $this->get(route('ministries.index'))
            ->assertOk()
            ->assertSee('scripture-ribbon', false)
            ->assertDontSee('<h2>Ministries of the UK Parish</h2>', false);
    }

    public function test_gallery_album_uses_placeholder_images(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $this->get(route('gallery.show', 'parish-worship-services'))
            ->assertOk()
            ->assertSee('images/gallery/placeholder-worship.svg', false);
    }

    public function test_bootstrap_if_empty_runs_on_fresh_database(): void
    {
        $this->artisan('site:bootstrap-if-empty', ['--force' => true])
            ->assertSuccessful();

        $this->assertDatabaseHas('pages', ['slug' => 'home']);
        $this->assertDatabaseHas('menu_items', ['seed_key' => 'home']);
    }

    public function test_bootstrap_if_empty_skips_when_pages_exist(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $pageCount = \App\Models\Page::query()->count();

        $this->artisan('site:bootstrap-if-empty', ['--force' => true])
            ->assertSuccessful();

        $this->assertSame($pageCount, \App\Models\Page::query()->count());
    }
}
