<?php

namespace Tests\Unit;

use App\Support\FaithComfortVerseBuckets;
use App\Support\FaithVerseLibrary;
use Tests\TestCase;

class FaithVerseLibraryTest extends TestCase
{
    public function test_library_ships_at_least_fifty_verses_per_tab_pool(): void
    {
        $all = FaithVerseLibrary::all();
        $buckets = FaithComfortVerseBuckets::split($all);

        $this->assertGreaterThanOrEqual(250, count($all));
        $this->assertGreaterThanOrEqual(50, count(FaithVerseLibrary::globalVerses()));
        $this->assertGreaterThanOrEqual(50, count(FaithVerseLibrary::errorVerses()));
        $this->assertGreaterThanOrEqual(50, count(FaithVerseLibrary::maintenanceVerses()));
        $this->assertGreaterThanOrEqual(50, count(FaithVerseLibrary::launchVerses()));
        $this->assertGreaterThanOrEqual(50, count($buckets[FaithComfortVerseBuckets::FIELD_ALL]));
        $this->assertGreaterThanOrEqual(50, count($buckets[FaithComfortVerseBuckets::FIELD_ERROR]));
        $this->assertGreaterThanOrEqual(50, count($buckets[FaithComfortVerseBuckets::FIELD_MAINTENANCE]));
        $this->assertGreaterThanOrEqual(50, count($buckets[FaithComfortVerseBuckets::FIELD_LAUNCH]));

        foreach (FaithVerseLibrary::errorVerses() as $verse) {
            $this->assertSame('error', $verse['only_on']);
        }

        foreach (FaithVerseLibrary::maintenanceVerses() as $verse) {
            $this->assertSame('maintenance', $verse['only_on']);
        }

        foreach (FaithVerseLibrary::launchVerses() as $verse) {
            $this->assertSame('launch', $verse['only_on']);
        }
    }
}
