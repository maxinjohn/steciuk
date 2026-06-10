<?php

namespace Tests\Feature;

use App\Database\ReferenceSiteContentMigrator;
use App\Models\Page;
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
}
