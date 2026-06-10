<?php

namespace App\Services;

use App\Support\UkPostcode;
use Illuminate\Support\Facades\Http;

class UkPostcodeLookup
{
    /**
     * @return array{postcode: string, city: string, county: string, latitude: float|null, longitude: float|null}|null
     */
    public function lookup(string $postcode): ?array
    {
        $normalized = UkPostcode::normalize($postcode);

        if ($normalized === null) {
            return null;
        }

        try {
            $response = Http::timeout(5)
                ->acceptJson()
                ->get('https://api.postcodes.io/postcodes/'.rawurlencode($normalized));
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $result = $response->json('result');

        if (! is_array($result)) {
            return null;
        }

        $city = (string) ($result['admin_district'] ?? $result['parish'] ?? $result['admin_ward'] ?? '');
        $county = (string) ($result['admin_county'] ?? $result['region'] ?? '');

        if ($county === '' || str_starts_with($county, 'S99999999')) {
            $county = (string) ($result['nuts'] ?? $result['region'] ?? '');
        }

        return [
            'postcode' => (string) ($result['postcode'] ?? $normalized),
            'city' => $city,
            'county' => $county,
            'latitude' => isset($result['latitude']) ? (float) $result['latitude'] : null,
            'longitude' => isset($result['longitude']) ? (float) $result['longitude'] : null,
        ];
    }

    /**
     * @return array{latitude: float, longitude: float}|null
     */
    public function coordinates(string $postcode): ?array
    {
        $lookup = $this->lookup($postcode);

        if ($lookup === null || $lookup['latitude'] === null || $lookup['longitude'] === null) {
            return null;
        }

        return [
            'latitude' => $lookup['latitude'],
            'longitude' => $lookup['longitude'],
        ];
    }
}
