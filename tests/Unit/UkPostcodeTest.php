<?php

namespace Tests\Unit;

use App\Support\UkPostcode;
use PHPUnit\Framework\TestCase;

class UkPostcodeTest extends TestCase
{
    public function test_it_normalizes_common_uk_postcodes(): void
    {
        $this->assertSame('SW1A 1AA', UkPostcode::normalize('sw1a1aa'));
        $this->assertSame('M1 1AE', UkPostcode::normalize('M11AE'));
        $this->assertSame('GIR 0AA', UkPostcode::normalize('gir0aa'));
    }

    public function test_it_validates_uk_postcodes(): void
    {
        $this->assertTrue(UkPostcode::isValid('SW1A 1AA'));
        $this->assertTrue(UkPostcode::isValid('M1 1AE'));
        $this->assertFalse(UkPostcode::isValid('NOT A POSTCODE'));
    }
}
