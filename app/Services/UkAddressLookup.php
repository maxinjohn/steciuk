<?php

namespace App\Services;

use App\Support\UkPostcode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class UkAddressLookup
{
    /**
     * @return array{
     *     postcode: string,
     *     city: string,
     *     county: string,
     *     addresses: list<array{id: string, label: string, line_1: string, line_2: string, city: string, county: string}>
     * }|null
     */
    public function lookup(string $postcode): ?array
    {
        $normalized = UkPostcode::normalize($postcode);

        if ($normalized === null) {
            return null;
        }

        $result = $this->lookupViaOpenStreetMap($normalized);

        if ($result !== null) {
            return $result;
        }

        return $this->lookupViaPostcodesIo($normalized);
    }

    /**
     * @return array{
     *     postcode: string,
     *     city: string,
     *     county: string,
     *     addresses: list<array{id: string, label: string, line_1: string, line_2: string, city: string, county: string}>
     * }|null
     */
    private function lookupViaOpenStreetMap(string $postcode): ?array
    {
        $compact = str_replace(' ', '', $postcode);
        $cacheKey = 'uk-address-osm:'.strtoupper($compact);

        $elements = Cache::remember($cacheKey, now()->addDay(), function () use ($postcode, $compact): array {
            $query = sprintf(
                '[out:json][timeout:15];(nwr["addr:postcode"=%s];nwr["addr:postcode"=%s];);out tags;',
                json_encode($postcode, JSON_THROW_ON_ERROR),
                json_encode($compact, JSON_THROW_ON_ERROR),
            );

            try {
                $response = Http::timeout(12)
                    ->withHeaders([
                        'User-Agent' => $this->openStreetMapUserAgent(),
                    ])
                    ->asForm()
                    ->post((string) config('services.openstreetmap.overpass_url'), [
                        'data' => $query,
                    ]);
            } catch (\Throwable) {
                return [];
            }

            if (! $response->successful()) {
                return [];
            }

            $elements = $response->json('elements');

            return is_array($elements) ? $elements : [];
        });

        if ($elements === []) {
            return null;
        }

        $area = app(UkPostcodeLookup::class)->lookup($postcode);
        $defaultCity = (string) ($area['city'] ?? '');
        $defaultCounty = (string) ($area['county'] ?? '');

        $addresses = [];

        foreach ($elements as $index => $element) {
            if (! is_array($element)) {
                continue;
            }

            $parsed = $this->parseOpenStreetMapElement($element, $defaultCity, $defaultCounty);

            if ($parsed === null) {
                continue;
            }

            $addresses[] = $this->makeAddressOption('osm-'.$index, $parsed);
        }

        if ($addresses === []) {
            return null;
        }

        usort($addresses, fn (array $left, array $right): int => strnatcasecmp($left['label'], $right['label']));

        return [
            'postcode' => $area['postcode'] ?? $postcode,
            'city' => $addresses[0]['city'] !== '' ? $addresses[0]['city'] : $defaultCity,
            'county' => $addresses[0]['county'] !== '' ? $addresses[0]['county'] : $defaultCounty,
            'addresses' => array_values($addresses),
        ];
    }

    /**
     * @param  array<string, mixed>  $element
     * @return array{line_1: string, line_2: string, city: string, county: string}|null
     */
    private function parseOpenStreetMapElement(array $element, string $defaultCity, string $defaultCounty): ?array
    {
        $tags = $element['tags'] ?? [];

        if (! is_array($tags)) {
            return null;
        }

        $housenumber = trim((string) ($tags['addr:housenumber'] ?? ''));
        $street = trim((string) ($tags['addr:street'] ?? $tags['addr:place'] ?? $tags['addr:road'] ?? ''));
        $line2Parts = array_filter([
            trim((string) ($tags['addr:unit'] ?? '')),
            trim((string) ($tags['addr:flat'] ?? '')),
            trim((string) ($tags['addr:subunit'] ?? '')),
        ]);

        if ($street === '' && $housenumber === '') {
            $name = trim((string) ($tags['name'] ?? ''));

            if ($name === '') {
                return null;
            }

            $street = $name;
        }

        $line1 = trim($housenumber !== '' ? "{$housenumber} {$street}" : $street);
        $line2 = implode(', ', $line2Parts);
        $city = trim((string) ($tags['addr:city'] ?? $tags['addr:town'] ?? $tags['addr:suburb'] ?? $defaultCity));
        $county = trim((string) ($tags['addr:county'] ?? $tags['addr:state'] ?? $defaultCounty));

        return $this->normalizeAddressParts($line1, $line2, $city, $county);
    }

    /**
     * @return array{
     *     postcode: string,
     *     city: string,
     *     county: string,
     *     addresses: list<array{id: string, label: string, line_1: string, line_2: string, city: string, county: string}>
     * }|null
     */
    private function lookupViaPostcodesIo(string $postcode): ?array
    {
        $area = app(UkPostcodeLookup::class)->lookup($postcode);

        if ($area === null) {
            return null;
        }

        return [
            'postcode' => $area['postcode'],
            'city' => $area['city'],
            'county' => $area['county'],
            'addresses' => [],
        ];
    }

    /**
     * @return array{line_1: string, line_2: string, city: string, county: string}|null
     */
    private function normalizeAddressParts(
        string $line1,
        string $line2,
        string $city,
        string $county,
    ): ?array {
        $line1 = trim($line1);
        $line2 = trim($line2);
        $city = $this->cleanLocality($city);
        $county = $this->cleanLocality($county);

        if ($line1 === '') {
            return null;
        }

        return [
            'line_1' => $line1,
            'line_2' => $line2,
            'city' => $city,
            'county' => $county,
        ];
    }

    /**
     * @param  array{line_1: string, line_2: string, city: string, county: string}  $parts
     * @return array{id: string, label: string, line_1: string, line_2: string, city: string, county: string}
     */
    private function makeAddressOption(string $id, array $parts): array
    {
        $labelParts = array_filter([
            $parts['line_1'],
            $parts['line_2'] !== '' ? $parts['line_2'] : null,
            $parts['city'] !== '' ? $parts['city'] : null,
            $parts['county'] !== '' ? $parts['county'] : null,
        ]);

        return [
            'id' => $id,
            'label' => implode(', ', $labelParts),
            'line_1' => $parts['line_1'],
            'line_2' => $parts['line_2'],
            'city' => $parts['city'],
            'county' => $parts['county'],
        ];
    }

    private function openStreetMapUserAgent(): string
    {
        $appName = (string) config('app.name', 'Steciuk');
        $appUrl = (string) config('app.url', 'https://steciuk.org');

        return "{$appName}/1.0 (+{$appUrl}; UK address lookup)";
    }

    private function cleanLocality(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $value = preg_replace('/\s*,?\s*UK$/i', '', $value) ?? $value;
        $value = preg_replace('/\s*,?\s*United Kingdom$/i', '', $value) ?? $value;

        return trim($value);
    }
}
