<?php

namespace Tests\Feature;

use App\Database\ReferenceSiteContentMigrator;
use App\Enums\MenuLocation;
use App\Models\ContentBlock;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Service;
use App\Models\Setting;
use App\Support\ReferenceSiteContent;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceSiteContentMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_migrator_updates_reference_settings_and_pages(): void
    {
        Setting::set('gospel_reminder_reference', 'Revelation 19:10', 'general');
        Page::query()->where('slug', 'our-church')->update(['content' => '<p>Outdated copy</p>']);

        ReferenceSiteContentMigrator::apply();

        $this->assertSame('Revelation 1:9', Setting::get('gospel_reminder_reference'));
        $this->assertStringContainsString('Nicene Creed', (string) Page::query()->where('slug', 'our-church')->value('content'));
        $this->assertSame(
            ReferenceSiteContent::pageFields()['home']['seo_description'],
            Page::query()->where('slug', 'home')->value('seo_description'),
        );
    }

    public function test_migrator_updates_reference_services(): void
    {
        Service::query()->where('location', 'Manchester')->update([
            'description' => 'Outdated description',
            'frequency' => 'Outdated frequency',
        ]);

        ReferenceSiteContentMigrator::apply();

        $manchester = Service::query()->where('location', 'Manchester')->first();

        $this->assertNotNull($manchester);
        $this->assertSame('Monthly worship service', $manchester->frequency);
        $this->assertStringContainsString('Greater Manchester', (string) $manchester->description);
        $this->assertSame(5, Service::query()->where('status', 'active')->count());
        $this->assertStringContainsString('admin@steciuk.org', (string) Service::query()->where('location', 'Manchester')->value('service_time'));
    }

    public function test_migrator_updates_all_reference_pages_settings_and_home_blocks(): void
    {
        Setting::set('charity_number', '0000000', 'contact');
        Page::query()->where('slug', 'service-times')->update(['content' => '<p>Outdated</p>']);
        Page::query()->where('slug', 'contact')->update(['content' => '<p>Outdated</p>']);

        $homeId = Page::query()->where('slug', 'home')->value('id');
        ContentBlock::query()
            ->where('page_id', $homeId)
            ->where('seed_key', 'hero')
            ->update(['content' => ['headline' => 'Old headline']]);

        ReferenceSiteContentMigrator::apply();

        $this->assertSame(ReferenceSiteContent::CHARITY_NUMBER, Setting::get('charity_number'));
        $this->assertStringContainsString('Charity Commission', (string) Page::query()->where('slug', 'service-times')->value('content'));
        $this->assertStringContainsString('eauk.org', (string) Page::query()->where('slug', 'contact')->value('content'));

        $hero = ContentBlock::query()
            ->where('page_id', $homeId)
            ->where('seed_key', 'hero')
            ->value('content');

        $this->assertSame('Word · Worship · Witness', $hero['headline'] ?? null);
    }

    public function test_migrator_provisions_home_page_when_missing(): void
    {
        Page::query()->forceDelete();
        ContentBlock::query()->delete();

        ReferenceSiteContentMigrator::apply();

        $this->assertTrue(Page::query()->where('slug', 'home')->where('is_home', true)->exists());
        $this->assertSame('admin@steciuk.org', Setting::get('contact_email'));
    }

    public function test_migrator_ensures_footer_quick_links(): void
    {
        MenuItem::query()->where('menu_location', MenuLocation::Footer)->delete();

        ReferenceSiteContentMigrator::apply();

        $this->assertGreaterThanOrEqual(
            5,
            MenuItem::query()->where('menu_location', MenuLocation::Footer)->where('is_visible', true)->count(),
        );
        $this->assertTrue(
            MenuItem::query()
                ->where('menu_location', MenuLocation::Footer)
                ->where('seed_key', 'service-times')
                ->exists(),
        );
    }
}
