<?php

namespace App\Support;

class UkPostcode
{
    public static function normalize(?string $postcode): ?string
    {
        if ($postcode === null || trim($postcode) === '') {
            return null;
        }

        $compact = strtoupper(preg_replace('/\s+/', '', trim($postcode)) ?? '');

        if ($compact === '') {
            return null;
        }

        if ($compact === 'GIR0AA') {
            return 'GIR 0AA';
        }

        if (strlen($compact) < 5) {
            return null;
        }

        return substr($compact, 0, -3).' '.substr($compact, -3);
    }

    public static function isValid(?string $postcode): bool
    {
        $normalized = static::normalize($postcode);

        if ($normalized === null) {
            return false;
        }

        return (bool) preg_match('/^(GIR 0AA|[A-Z]{1,2}\d[A-Z\d]? \d[A-Z]{2})$/', $normalized);
    }
}
