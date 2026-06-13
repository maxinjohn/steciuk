<?php

namespace Tests\Unit;

use App\Support\FaithCopyLibrary;
use Tests\TestCase;

class FaithCopyLibraryTest extends TestCase
{
    public function test_library_ships_rotating_footer_and_comfort_lists(): void
    {
        $this->assertGreaterThanOrEqual(20, count(FaithCopyLibrary::sanctuaryRibbons()));
        $this->assertGreaterThanOrEqual(15, count(FaithCopyLibrary::comfortHeaders()));
        $this->assertSame('In Christ\'s peace', FaithCopyLibrary::sanctuaryRibbons()[0]['kicker']);
        $this->assertSame('For every believer', FaithCopyLibrary::comfortHeaders()[0]['kicker']);
    }
}
