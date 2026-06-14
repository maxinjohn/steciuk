<?php

namespace App\Support;

class FutureSiteConfig
{
    public static function enabled(): bool
    {
        return PublicUiContent::experienceToggles()['enabled'];
    }

    public static function speculationEnabled(): bool
    {
        $toggles = PublicUiContent::experienceToggles();

        return $toggles['enabled'] && $toggles['speculation_rules'];
    }

    public static function readingProgressEnabled(): bool
    {
        $toggles = PublicUiContent::experienceToggles();

        return $toggles['enabled'] && $toggles['reading_progress'];
    }

    /**
     * High-intent same-origin routes for Speculation Rules prefetch (Chrome 109+, growing support).
     *
     * @return list<string>
     */
    public static function speculationPrefetchPaths(): array
    {
        $paths = config('site.future.speculation_paths', [
            '/service-times',
            '/prayer-request',
            '/give',
            '/events',
            '/sermons',
            '/contact',
        ]);

        if (! is_array($paths)) {
            return [];
        }

        return array_values(array_filter(array_map(static function ($path): ?string {
            if (! is_string($path) || trim($path) === '') {
                return null;
            }

            $path = '/'.ltrim(trim($path), '/');

            return $path !== '/' ? $path : null;
        }, $paths)));
    }

    /**
     * @return list<string>
     */
    public static function speculationPrerenderPaths(): array
    {
        $paths = config('site.future.speculation_prerender_paths', [
            '/service-times',
        ]);

        if (! is_array($paths)) {
            return [];
        }

        return array_values(array_filter(array_map(static function ($path): ?string {
            if (! is_string($path) || trim($path) === '') {
                return null;
            }

            return '/'.ltrim(trim($path), '/');
        }, $paths)));
    }

    public static function readingProgressForRequest(?\Illuminate\Http\Request $request = null): bool
    {
        if (! self::readingProgressEnabled()) {
            return false;
        }

        $request ??= request();

        if ($request === null || AdminPanelConfig::isAdminRequest($request)) {
            return false;
        }

        return $request->routeIs(
            'events.show',
            'news.show',
            'ministries.show',
            'gallery.show',
            'pages.show',
        );
    }
}
