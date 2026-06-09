<?php

namespace App\Support;

use Illuminate\Support\Str;

class Seo
{
    /** URL path segments reserved for application routes (not CMS page slugs). */
    public const RESERVED_SLUGS = [
        'admin',
        'events',
        'gallery',
        'manifest.webmanifest',
        'ministries',
        'news',
        'offline',
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

        return Str::limit(trim(strip_tags($text)), $limit, '…');
    }

    public static function absoluteAsset(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/'.ltrim($path, '/'));
    }

    public static function canonicalUrl(?string $override = null): string
    {
        return $override ?: url()->current();
    }
}
