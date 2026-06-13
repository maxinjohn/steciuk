<?php

namespace App\Services;

use App\Enums\MenuLocation;
use App\Models\MenuItem;
use App\Models\Page;
use App\Support\GivingUrl;
use App\Support\SafeUrl;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MenuCache
{
    private const TTL_SECONDS = 3600;

    private const ALL_TREES_CACHE_KEY = 'menu.trees.all.v5';

    public static function load(MenuLocation $location): Collection
    {
        return self::loadAll()[$location->value];
    }

    /**
     * @return array<string, Collection<int, object>>
     */
    public static function loadAll(): array
    {
        $cached = Cache::get(self::ALL_TREES_CACHE_KEY);

        if (! static::isValidPackedTrees($cached)) {
            if ($cached !== null) {
                Cache::forget(self::ALL_TREES_CACHE_KEY);
            }

            foreach (MenuLocation::cases() as $location) {
                Cache::forget('menu.tree.'.$location->value.'.v2');
            }
        }

        $trees = Cache::remember(self::ALL_TREES_CACHE_KEY, self::TTL_SECONDS, function (): array {
            $packed = [];

            foreach (MenuLocation::cases() as $location) {
                $packed[$location->value] = static::buildTree($location);
            }

            return $packed;
        });

        if (! static::isValidPackedTrees($trees)) {
            Cache::forget(self::ALL_TREES_CACHE_KEY);

            return static::loadAllFresh();
        }

        return collect($trees)
            ->map(fn (array $tree): Collection => static::hydrateTree($tree))
            ->all();
    }

    /**
     * @return array<string, Collection<int, object>>
     */
    private static function loadAllFresh(): array
    {
        $packed = [];

        foreach (MenuLocation::cases() as $location) {
            $packed[$location->value] = static::buildTree($location);
        }

        Cache::put(self::ALL_TREES_CACHE_KEY, $packed, self::TTL_SECONDS);

        return collect($packed)
            ->map(fn (array $tree): Collection => static::hydrateTree($tree))
            ->all();
    }

    /**
     * @param  mixed  $trees
     */
    private static function isValidPackedTrees(mixed $trees): bool
    {
        if (! is_array($trees)) {
            return false;
        }

        foreach (MenuLocation::cases() as $location) {
            $tree = $trees[$location->value] ?? null;

            if (! is_array($tree)) {
                return false;
            }

            foreach ($tree as $item) {
                if (! is_array($item) || ! array_key_exists('label', $item)) {
                    return false;
                }
            }
        }

        return true;
    }

    public static function forgetAll(): void
    {
        Cache::forget(self::ALL_TREES_CACHE_KEY);
        Cache::forget('menu.trees.all.v3');

        foreach (MenuLocation::cases() as $location) {
            Cache::forget('menu.tree.'.$location->value.'.v2');
        }
    }

    /**
     * @param  list<array<string, mixed>>  $tree
     * @return Collection<int, object>
     */
    private static function hydrateTree(array $tree): Collection
    {
        return collect($tree)->map(static fn (array $item): object => (object) [
            'label' => $item['label'],
            'url' => static::publicUrl($item['url'], (bool) ($item['is_external'] ?? false)),
            'target' => $item['target'],
            'is_external' => $item['is_external'],
            'children' => collect($item['children'] ?? [])->map(static fn (array $child): object => (object) [
                'label' => $child['label'],
                'url' => static::publicUrl($child['url'], (bool) ($child['is_external'] ?? false)),
                'target' => $child['target'],
                'is_external' => $child['is_external'],
            ]),
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function buildTree(MenuLocation $location): array
    {
        $items = MenuItem::query()
            ->select(['id', 'label', 'url', 'page_id', 'parent_id', 'sort_order', 'is_visible', 'is_external', 'target', 'menu_location'])
            ->where('menu_location', $location)
            ->where('is_visible', true)
            ->whereNull('parent_id')
            ->with([
                'children' => fn ($q) => $q
                    ->select(['id', 'label', 'url', 'page_id', 'parent_id', 'sort_order', 'is_visible', 'is_external', 'target', 'menu_location'])
                    ->where('is_visible', true)
                    ->orderBy('sort_order')
                    ->with('page:id,slug'),
                'page:id,slug',
            ])
            ->orderBy('sort_order')
            ->get();

        return $items->map(fn (MenuItem $item): array => static::packItem($item))->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private static function packItem(MenuItem $item): array
    {
        return [
            'label' => $item->label,
            'url' => static::resolveUrl($item),
            'target' => $item->target ?: ($item->is_external ? '_blank' : null),
            'is_external' => $item->is_external,
            'children' => $item->children
                ->map(fn (MenuItem $child): array => [
                    'label' => $child->label,
                    'url' => static::resolveUrl($child),
                    'target' => $child->target ?: ($child->is_external ? '_blank' : null),
                    'is_external' => $child->is_external,
                ])
                ->values()
                ->all(),
        ];
    }

    private static function resolveUrl(MenuItem $item): string
    {
        if ($item->page_id) {
            $slug = $item->relationLoaded('page') ? $item->page?->slug : Page::query()->whereKey($item->page_id)->value('slug');

            if ($slug === 'home') {
                return '/';
            }

            if ($slug === 'give') {
                return '/give';
            }

            return $slug ? '/'.$slug : '#';
        }

        if ($item->url) {
            if (GivingUrl::pointsToGivePage($item->url)) {
                return '/give';
            }

            if ($item->is_external || str_starts_with($item->url, 'http')) {
                return $item->url;
            }

            return str_starts_with($item->url, '/') ? $item->url : '/'.ltrim($item->url, '/');
        }

        return '#';
    }

    private static function publicUrl(string $url, bool $isExternal): string
    {
        if ($url === '#') {
            return $url;
        }

        if ($isExternal || str_starts_with($url, 'http') || str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:')) {
            if (! $isExternal && str_starts_with($url, 'http')) {
                $path = parse_url($url, PHP_URL_PATH) ?: '/';
                $query = parse_url($url, PHP_URL_QUERY);

                return SafeUrl::resolve($query ? $path.'?'.$query : $path);
            }

            return $url;
        }

        return SafeUrl::resolve($url);
    }
}
