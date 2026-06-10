<?php

namespace Tests\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

trait FakesUkAddressLookup
{
    /**
     * @param  list<array{line_1: string, line_2?: string, town_or_city?: string, county?: string}>  $addresses
     */
    protected function fakeUkAddressLookup(
        string $postcode = 'M1 1AE',
        array $addresses = [],
    ): void {
        Cache::flush();

        if ($addresses === []) {
            $addresses = [[
                'line_1' => '1 Example Street',
                'line_2' => '',
                'town_or_city' => 'Manchester',
                'county' => 'Greater Manchester',
            ]];
        }

        $elements = [];

        foreach ($addresses as $index => $address) {
            $tags = [
                'addr:street' => $address['line_1'],
                'addr:postcode' => $postcode,
            ];

            if (filled($address['line_2'] ?? null)) {
                $tags['addr:unit'] = $address['line_2'];
            }

            if (filled($address['town_or_city'] ?? null)) {
                $tags['addr:city'] = $address['town_or_city'];
            }

            if (filled($address['county'] ?? null)) {
                $tags['addr:county'] = $address['county'];
            }

            $elements[] = [
                'type' => 'node',
                'id' => 1000 + $index,
                'tags' => $tags,
            ];
        }

        Http::fake([
            'overpass-api.de/*' => Http::response([
                'elements' => $elements,
            ]),
            'api.postcodes.io/*' => Http::response([
                'result' => [
                    'postcode' => $postcode,
                    'admin_district' => 'Manchester',
                    'admin_county' => 'Greater Manchester',
                ],
            ]),
        ]);
    }
}
