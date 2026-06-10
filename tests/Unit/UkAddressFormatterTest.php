<?php

namespace Tests\Unit;

use App\Support\UkAddressFormatter;
use PHPUnit\Framework\TestCase;

class UkAddressFormatterTest extends TestCase
{
    public function test_it_formats_a_full_uk_address(): void
    {
        $formatted = UkAddressFormatter::format(
            line1: '1 Example Street',
            line2: 'Flat 2',
            city: 'Manchester',
            county: 'Greater Manchester',
            postcode: 'm11ae',
            country: 'United Kingdom',
        );

        $this->assertSame(
            '1 Example Street, Flat 2, Manchester, Greater Manchester, M1 1AE, United Kingdom',
            $formatted,
        );
    }

    public function test_it_builds_schema_org_postal_address(): void
    {
        $schema = UkAddressFormatter::schemaOrg(
            line1: '10 Downing Street',
            city: 'London',
            county: 'Greater London',
            postcode: 'SW1A 2AA',
            country: 'United Kingdom',
        );

        $this->assertSame([
            '@type' => 'PostalAddress',
            'streetAddress' => '10 Downing Street',
            'addressLocality' => 'London',
            'addressRegion' => 'Greater London',
            'postalCode' => 'SW1A 2AA',
            'addressCountry' => 'United Kingdom',
        ], $schema);
    }

    public function test_it_reads_structured_settings_with_legacy_fallback(): void
    {
        $this->assertSame(
            'Legacy office, United Kingdom',
            UkAddressFormatter::fromSettings([
                'contact_address_line_1' => 'Legacy office',
                'contact_country' => 'United Kingdom',
            ]),
        );

        $this->assertSame(
            'Old single-line address',
            UkAddressFormatter::fromSettings([
                'main_address' => 'Old single-line address',
            ]),
        );
    }
}
