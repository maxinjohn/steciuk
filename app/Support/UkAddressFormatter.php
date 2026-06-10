<?php

namespace App\Support;

class UkAddressFormatter
{
    /**
     * @param  array<string, mixed>  $settings
     */
    public static function fromSettings(array $settings): ?string
    {
        $line1 = self::string($settings, 'contact_address_line_1');
        $line2 = self::string($settings, 'contact_address_line_2');
        $city = self::string($settings, 'contact_city');
        $county = self::string($settings, 'contact_county');
        $postcode = self::string($settings, 'contact_postcode');

        if ($line1 || $line2 || $city || $county || $postcode) {
            $formatted = self::format(
                line1: $line1,
                line2: $line2,
                city: $city,
                county: $county,
                postcode: $postcode,
                country: self::string($settings, 'contact_country') ?: 'United Kingdom',
            );

            if ($formatted !== '') {
                return $formatted;
            }
        }

        $legacy = self::string($settings, 'main_address');

        return $legacy !== '' ? $legacy : null;
    }

    /**
     * @return array<string, string>|null
     */
    public static function schemaOrgFromSettings(array $settings): ?array
    {
        return self::schemaOrg(
            line1: self::string($settings, 'contact_address_line_1'),
            line2: self::string($settings, 'contact_address_line_2'),
            city: self::string($settings, 'contact_city'),
            county: self::string($settings, 'contact_county'),
            postcode: self::string($settings, 'contact_postcode'),
            country: self::string($settings, 'contact_country') ?: 'United Kingdom',
        );
    }

    public static function format(
        ?string $line1 = null,
        ?string $line2 = null,
        ?string $city = null,
        ?string $county = null,
        ?string $postcode = null,
        ?string $country = 'United Kingdom',
    ): string {
        $normalizedPostcode = filled($postcode)
            ? (UkPostcode::normalize($postcode) ?? strtoupper(trim((string) $postcode)))
            : null;

        $locality = collect([trim((string) $city), trim((string) $county)])
            ->filter(fn (string $part): bool => $part !== '')
            ->implode(', ');

        $parts = array_filter([
            filled($line1) ? trim((string) $line1) : null,
            filled($line2) ? trim((string) $line2) : null,
            $locality !== '' ? $locality : null,
            $normalizedPostcode,
            filled($country) ? trim((string) $country) : null,
        ]);

        return implode(', ', $parts);
    }

    /**
     * @return array<string, string>|null
     */
    public static function schemaOrg(
        ?string $line1 = null,
        ?string $line2 = null,
        ?string $city = null,
        ?string $county = null,
        ?string $postcode = null,
        ?string $country = 'United Kingdom',
    ): ?array {
        $street = collect([trim((string) $line1), trim((string) $line2)])
            ->filter(fn (string $part): bool => $part !== '')
            ->implode(', ');

        $normalizedPostcode = filled($postcode)
            ? (UkPostcode::normalize($postcode) ?? strtoupper(trim((string) $postcode)))
            : null;

        $address = array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $street !== '' ? $street : null,
            'addressLocality' => filled($city) ? trim((string) $city) : null,
            'addressRegion' => filled($county) ? trim((string) $county) : null,
            'postalCode' => $normalizedPostcode,
            'addressCountry' => filled($country) ? trim((string) $country) : 'United Kingdom',
        ], fn ($value) => filled($value));

        return count($address) > 1 ? $address : null;
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    private static function string(array $settings, string $key): ?string
    {
        $value = $settings[$key] ?? null;

        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }
}
