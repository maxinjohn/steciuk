<?php

namespace Tests\Unit;

use App\Support\GivingUrl;
use PHPUnit\Framework\TestCase;

class GivingUrlTest extends TestCase
{
    public function test_points_to_give_page_for_relative_and_legacy_urls(): void
    {
        $this->assertTrue(GivingUrl::pointsToGivePage('/give'));
        $this->assertTrue(GivingUrl::pointsToGivePage('https://steciuk.org/give'));
        $this->assertTrue(GivingUrl::pointsToGivePage('https://www.steciuk.org/give/'));
        $this->assertFalse(GivingUrl::pointsToGivePage('https://example.com/give'));
    }
}
