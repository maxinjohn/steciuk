<?php

namespace App\Support;

use Illuminate\Support\Str;

class Seo
{
    /** URL path segments reserved for application routes (not CMS page slugs). */
    public const RESERVED_SLUGS = [
        'account',
        'admin',
        'events',
        'forgot-password',
        'gallery',
        'give',
        'login',
        'manifest.webmanifest',
        'ministries',
        'news',
        'offline',
        'register',
        'registration',
        'resources',
        'robots.txt',
        'sermons',
        'service-times',
        'sitemap.xml',
        'sw.js',
    ];

    public static function isIndexable(?string $metaRobots): bool
    {
        if ($metaRobots === null || trim($metaRobots) === '') {
            return true;
        }

        return ! str_contains(strtolower($metaRobots), 'noindex');
    }

    public static function isReservedSlug(string $slug): bool
    {
        $reserved = array_unique([
            ...self::RESERVED_SLUGS,
            AdminPanelConfig::path(),
        ]);

        return in_array(strtolower($slug), $reserved, true);
    }

    public static function truncateDescription(?string $text, int $limit = 160): string
    {
        if ($text === null || trim($text) === '') {
            return '';
        }

        return self::metaText(Str::limit(trim(strip_tags($text)), $limit, '…'));
    }

    /**
     * Normalize text for document titles and meta tags.
     * Blade @section('name', 'value') escapes once; decode before a single output escape.
     */
    public static function metaText(?string $text): string
    {
        if ($text === null) {
            return '';
        }

        $plain = trim(strip_tags($text));

        if ($plain === '') {
            return '';
        }

        do {
            $decoded = html_entity_decode($plain, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($decoded === $plain) {
                break;
            }
            $plain = $decoded;
        } while (true);

        return preg_replace('/\s+/u', ' ', $plain) ?? $plain;
    }

    /**
     * Standard document title: "Primary | STECI UK Parish" or "Primary | Section | Site".
     */
    public static function documentTitle(
        ?string $primary,
        ?string $section = null,
        ?string $siteName = null,
    ): string {
        $siteName = self::metaText($siteName) ?: 'STECI UK Parish';
        $primary = self::metaText($primary);
        $section = self::metaText($section);

        if ($primary === '') {
            return $siteName;
        }

        // CMS seo_title and detail pages are often pre-formatted with " | " segments.
        if (str_contains($primary, ' | ')) {
            return $primary;
        }

        $primaryLower = strtolower($primary);
        $siteLower = strtolower($siteName);

        if (str_contains($primaryLower, $siteLower)) {
            return $primary;
        }

        if ($section !== '' && ! str_contains($primaryLower, strtolower($section))) {
            return "{$primary} | {$section} | {$siteName}";
        }

        return "{$primary} | {$siteName}";
    }

    public static function absoluteAsset(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $relative = SitePaths::normalizeUploadRelativePath($path);

        if ($relative === null) {
            return null;
        }

        return url(SitePaths::publicStorageUrl($relative));
    }

    public static function canonicalUrl(?string $override = null): string
    {
        return $override ?: url()->current();
    }
}
