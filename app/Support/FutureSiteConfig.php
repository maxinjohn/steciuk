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
        return false;
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

    /**
     * Prefetch targets for the active request (never includes the current URL).
     *
     * @return list<string>
     */
    public static function speculationPrefetchPathsForRequest(?\Illuminate\Http\Request $request = null): array
    {
        return self::excludeCurrentPath(self::speculationPrefetchPaths(), $request);
    }

    /**
     * Prerender targets for the active request (never includes the current URL).
     *
     * @return list<string>
     */
    public static function speculationPrerenderPathsForRequest(?\Illuminate\Http\Request $request = null): array
    {
        return self::excludeCurrentPath(self::speculationPrerenderPaths(), $request);
    }

    /**
     * Interaction-gated Speculation Rules (no list prefetch on load).
     *
     * @return array<string, mixed>
     */
    public static function speculationRulesPayload(?\Illuminate\Http\Request $request = null): array
    {
        if (! self::speculationEnabled()) {
            return [];
        }

        $paths = self::speculationPrefetchPathsForRequest($request);

        if ($paths === []) {
            return [];
        }

        $prefetch = [];

        foreach ($paths as $path) {
            $prefetch[] = [
                'source' => 'document',
                'where' => [
                    'href_matches' => self::hrefMatchPattern($path),
                ],
                'eagerness' => 'conservative',
            ];
        }

        return ['prefetch' => $prefetch];
    }

    private static function hrefMatchPattern(string $path): string
    {
        return url(self::normalizePath($path));
    }

    /**
     * @param  list<string>  $paths
     * @return list<string>
     */
    private static function excludeCurrentPath(array $paths, ?\Illuminate\Http\Request $request = null): array
    {
        $current = self::normalizeRequestPath($request);

        if ($current === null) {
            return $paths;
        }

        return array_values(array_filter(
            $paths,
            static fn (string $path): bool => self::normalizePath($path) !== $current,
        ));
    }

    private static function normalizeRequestPath(?\Illuminate\Http\Request $request = null): ?string
    {
        $request ??= request();

        if ($request === null) {
            return null;
        }

        return self::normalizePath('/'.trim($request->path(), '/'));
    }

    private static function normalizePath(string $path): string
    {
        $path = '/'.trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
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
