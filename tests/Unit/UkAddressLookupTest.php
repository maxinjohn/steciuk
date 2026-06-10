<?php

namespace Tests\Unit;

use App\Services\UkAddressLookup;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UkAddressLookupTest extends TestCase
{
    public function test_it_returns_selectable_addresses_from_openstreetmap(): void
    {
        Cache::flush();

        Http::fake([
            'overpass-api.de/*' => Http::response([
                'elements' => [
                    [
                        'type' => 'node',
                        'id' => 5816650867,
                        'tags' => [
                            'addr:city' => 'Manchester',
                            'addr:housenumber' => '113',
                            'addr:postcode' => 'M1 1AE',
                            'addr:street' => 'Newton Street',
                        ],
                    ],
                    [
                        'type' => 'node',
                        'id' => 5816650868,
                        'tags' => [
                            'addr:city' => 'Manchester',
                            'addr:housenumber' => '115',
                            'addr:postcode' => 'M1 1AE',
                            'addr:street' => 'Newton Street',
                        ],
                    ],
                ],
            ]),
            'api.postcodes.io/*' => Http::response([
                'result' => [
                    'postcode' => 'M1 1AE',
                    'admin_district' => 'Manchester',
                    'admin_county' => 'Greater Manchester',
                ],
            ]),
        ]);

        $result = app(UkAddressLookup::class)->lookup('M1 1AE');

        $this->assertNotNull($result);
        $this->assertCount(2, $result['addresses']);
        $this->assertSame('113 Newton Street', $result['addresses'][0]['line_1']);
        $this->assertSame('115 Newton Street', $result['addresses'][1]['line_1']);
    }

    public function test_it_falls_back_to_postcodes_io_when_openstreetmap_has_no_addresses(): void
    {
        Cache::flush();

        Http::fake([
            'overpass-api.de/*' => Http::response([
                'elements' => [],
            ]),
            'api.postcodes.io/*' => Http::response([
                'result' => [
                    'postcode' => 'M1 1AE',
                    'admin_district' => 'Manchester',
                    'admin_county' => 'Greater Manchester',
                ],
            ]),
        ]);

        $result = app(UkAddressLookup::class)->lookup('M1 1AE');

        $this->assertNotNull($result);
        $this->assertSame('Manchester', $result['city']);
        $this->assertSame([], $result['addresses']);
    }
}
