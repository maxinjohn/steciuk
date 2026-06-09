<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class SiteCache
{
    public static function forgetSitemap(): void
    {
        Cache::forget('sitemap.xml.v1');
    }

    public static function forgetPageContext(?string $slug): void
    {
        if ($slug) {
            PageContext::forget($slug);
        }
    }

    public static function forgetPublicContent(?string $pageSlug = null): void
    {
        HomePageData::forget();
        ServiceLocations::forget();
        static::forgetSitemap();
        static::forgetPageContext($pageSlug);
    }

    /** Clear menus, home page, and sitemap after bootstrap/sync or container boot. */
    public static function forgetAfterReferenceDataChange(): void
    {
        MenuCache::forgetAll();
        static::forgetPublicContent();
    }
}
