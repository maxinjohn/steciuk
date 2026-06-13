<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Support\FaithContent;
use App\Support\FaithVerseLibrary;
use App\Support\ReferenceSiteContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaithContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_random_verse_uses_admin_pool_when_configured(): void
    {
        Setting::set('faith_sanctuary_verses', json_encode([
            ['text' => 'Alpha verse', 'ref' => 'Test 1:1'],
            ['text' => 'Beta verse', 'ref' => 'Test 2:2'],
        ]), 'faith');

        $seen = [];

        for ($i = 0; $i < 12; $i++) {
            $verse = FaithContent::randomVerse();
            $seen[$verse['text']] = true;
        }

        $this->assertArrayHasKey('Alpha verse', $seen);
        $this->assertArrayHasKey('Beta verse', $seen);
    }

    public function test_random_verse_prefers_scoped_pool_for_context(): void
    {
        Setting::set('faith_sanctuary_verses', json_encode([
            ['text' => 'Global verse', 'ref' => 'Gen 1:1'],
            ['text' => 'Maintenance only', 'ref' => 'Maint 1:1', 'only_on' => 'maintenance'],
        ]), 'faith');

        $seen = [];

        for ($i = 0; $i < 16; $i++) {
            $verse = FaithContent::randomVerse('maintenance');
            $seen[$verse['text']] = true;
        }

        $this->assertArrayHasKey('Maintenance only', $seen);
        $this->assertArrayNotHasKey('Global verse', $seen);
    }

    public function test_random_verse_falls_back_to_global_pool_when_no_scope_match(): void
    {
        Setting::set('faith_sanctuary_verses', json_encode([
            ['text' => 'Global verse', 'ref' => 'Gen 1:1'],
        ]), 'faith');

        $verse = FaithContent::randomVerse('launch');

        $this->assertSame('Global verse', $verse['text']);
    }

    public function test_sanctuary_verses_excludes_scoped_entries(): void
    {
        Setting::set('faith_sanctuary_verses', json_encode([
            ['text' => 'Global verse', 'ref' => 'Gen 1:1'],
            ['text' => 'Error only', 'ref' => 'Err 1:1', 'only_on' => 'error'],
        ]), 'faith');

        $verses = FaithContent::sanctuaryVerses();

        $this->assertCount(1, $verses);
        $this->assertSame('Global verse', $verses[0]['text']);
    }

    public function test_random_verse_falls_back_to_reference_defaults(): void
    {
        Setting::forget('faith_sanctuary_verses');

        $verse = FaithContent::randomVerse();

        $this->assertNotSame('', $verse['text']);
        $this->assertNotSame('', $verse['ref']);
        $this->assertContains($verse['text'], collect(ReferenceSiteContent::faithSanctuaryVerses())->pluck('text')->all());
    }

    public function test_reference_defaults_include_scoped_pools_for_each_gate(): void
    {
        Setting::forget('faith_sanctuary_verses');

        $maintenanceSeen = [];
        $launchSeen = [];
        $errorSeen = [];

        for ($i = 0; $i < 24; $i++) {
            $maintenanceSeen[FaithContent::randomVerse('maintenance')['text']] = true;
            $launchSeen[FaithContent::randomVerse('launch')['text']] = true;
            $errorSeen[FaithContent::randomVerse('error')['text']] = true;
        }

        $this->assertGreaterThan(1, count($maintenanceSeen));
        $this->assertGreaterThan(1, count($launchSeen));
        $this->assertGreaterThan(1, count($errorSeen));
    }

    public function test_sanctuary_ribbons_and_comfort_headers_use_prefilled_lists(): void
    {
        Setting::forget('faith_sanctuary_ribbons');
        Setting::forget('faith_comfort_headers');

        $this->assertGreaterThanOrEqual(20, count(FaithContent::sanctuaryRibbons()));
        $this->assertGreaterThanOrEqual(15, count(FaithContent::comfortHeaders()));
        $this->assertNotSame('', FaithContent::sanctuaryRibbon()['kicker']);
        $this->assertNotSame('', FaithContent::comfortHeader()['heading']);
    }
}
