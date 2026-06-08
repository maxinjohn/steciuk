<?php

namespace App\Services;

use App\Enums\MenuLocation;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MenuCache
{
    private const TTL_SECONDS = 3600;

    public static function load(MenuLocation $location): Collection
    {
        $key = 'menu.tree.'.$location->value.'.v2';

        $tree = Cache::remember($key, self::TTL_SECONDS, fn (): array => static::buildTree($location));

        return collect($tree)->map(static fn (array $item): object => (object) [
            'label' => $item['label'],
            'url' => $item['url'],
            'target' => $item['target'],
            'is_external' => $item['is_external'],
            'children' => collect($item['children'] ?? [])->map(static fn (array $child): object => (object) $child),
        ]);
    }

    public static function forgetAll(): void
    {
        foreach (MenuLocation::cases() as $location) {
            Cache::forget('menu.tree.'.$location->value.'.v2');
        }
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
                return route('home');
            }

            return $slug ? route('pages.show', $slug) : '#';
        }

        if ($item->url) {
            if ($item->is_external || str_starts_with($item->url, 'http')) {
                return $item->url;
            }

            return url($item->url);
        }

        return '#';
    }
}
