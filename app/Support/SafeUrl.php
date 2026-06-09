<?php

namespace App\Support;

class SafeUrl
{
    private const BLOCKED_SCHEMES = [
        'javascript',
        'data',
        'vbscript',
        'file',
        'blob',
    ];

    public static function isSafe(?string $url): bool
    {
        if ($url === null || trim($url) === '') {
            return false;
        }

        $url = trim($url);

        if (str_starts_with($url, '//')) {
            return true;
        }

        if (str_starts_with($url, '/')) {
            return ! str_starts_with($url, '//');
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if ($scheme === '' || in_array($scheme, self::BLOCKED_SCHEMES, true)) {
            return false;
        }

        return in_array($scheme, ['http', 'https', 'mailto', 'tel'], true);
    }

    public static function forHref(?string $url, string $fallback = '#'): string
    {
        if ($url === null || trim($url) === '') {
            return $fallback;
        }

        $url = trim($url);

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return $url;
        }

        if (self::isSafe($url)) {
            return $url;
        }

        return $fallback;
    }

    public static function resolve(?string $url): string
    {
        $safe = self::forHref($url, '#');

        if ($safe === '#') {
            return $safe;
        }

        if (str_starts_with($safe, 'http')) {
            return $safe;
        }

        if (str_starts_with($safe, '/')) {
            return url($safe);
        }

        return url($safe);
    }
}
