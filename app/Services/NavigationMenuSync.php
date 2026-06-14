<?php

namespace App\Services;

use App\Enums\MenuLocation;
use App\Models\MenuItem;
use App\Models\Ministry;
use App\Models\Page;
use App\Support\NavigationMenuCatalog;

class NavigationMenuSync
{
    public static function applyAll(): void
    {
        Page::query()->each(static fn (Page $page) => self::syncPage($page));
        Ministry::query()->each(static fn (Ministry $ministry) => self::syncMinistry($ministry));

        MenuCache::forgetAll();
    }

    public static function backfillPageDefaults(): void
    {
        foreach (NavigationMenuCatalog::referencePagePlacements() as $slug => $placement) {
            Page::query()
                ->where('slug', $slug)
                ->where('show_in_menu', false)
                ->whereNull('menu_parent_seed_key')
                ->update([
                    'show_in_menu' => true,
                    'menu_parent_seed_key' => $placement['parent_seed_key'],
                ]);
        }
    }

    public static function syncPage(Page $page): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasColumn('pages', 'show_in_menu')) {
            return;
        }

        if ($page->is_home) {
            return;
        }

        $seedKey = NavigationMenuCatalog::referenceSeedKeyForPageSlug($page->slug)
            ?? 'page.'.$page->slug;

        foreach (NavigationMenuCatalog::primaryLocations() as $location) {
            if (! $page->show_in_menu) {
                self::hideMenuItem($location, $seedKey, $page->id);

                continue;
            }

            self::upsertMenuItem(
                location: $location,
                seedKey: $seedKey,
                label: $page->menu_label ?: $page->title,
                url: '/'.$page->slug,
                pageId: $page->id,
                parentSeedKey: $page->menu_parent_seed_key,
                sortOrder: $page->menu_sort_order ?? $page->sort_order ?? 0,
            );
        }

        MenuCache::forgetAll();
    }

    public static function syncMinistry(Ministry $ministry): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasColumn('ministries', 'show_in_menu')) {
            return;
        }

        if (Page::query()->where('slug', $ministry->slug)->exists()) {
            self::removeMinistryMenuItems($ministry);

            return;
        }

        $seedKey = NavigationMenuCatalog::ministrySeedKey($ministry->slug);

        foreach (NavigationMenuCatalog::primaryLocations() as $location) {
            if (! $ministry->show_in_menu || $ministry->status !== 'active') {
                self::hideMenuItem($location, $seedKey);

                continue;
            }

            self::upsertMenuItem(
                location: $location,
                seedKey: $seedKey,
                label: $ministry->menu_label ?: $ministry->name,
                url: '/ministries/'.$ministry->slug,
                pageId: null,
                parentSeedKey: $ministry->menu_parent_seed_key ?: 'ministries',
                sortOrder: $ministry->menu_sort_order ?? $ministry->sort_order ?? 0,
            );
        }

        MenuCache::forgetAll();
    }

    public static function removePage(Page $page): void
    {
        $seedKey = NavigationMenuCatalog::referenceSeedKeyForPageSlug($page->slug)
            ?? 'page.'.$page->slug;

        foreach (NavigationMenuCatalog::primaryLocations() as $location) {
            self::hideMenuItem($location, $seedKey, $page->id);
        }

        MenuCache::forgetAll();
    }

    public static function removeMinistry(Ministry $ministry): void
    {
        self::removeMinistryMenuItems($ministry);
        MenuCache::forgetAll();
    }

    private static function removeMinistryMenuItems(Ministry $ministry): void
    {
        $seedKey = NavigationMenuCatalog::ministrySeedKey($ministry->slug);

        foreach (NavigationMenuCatalog::primaryLocations() as $location) {
            self::hideMenuItem($location, $seedKey);
        }
    }

    private static function upsertMenuItem(
        MenuLocation $location,
        string $seedKey,
        string $label,
        string $url,
        ?int $pageId,
        ?string $parentSeedKey,
        int $sortOrder,
    ): void {
        MenuItem::query()->updateOrCreate(
            [
                'menu_location' => $location,
                'seed_key' => $seedKey,
            ],
            [
                'label' => $label,
                'url' => $url,
                'page_id' => $pageId,
                'parent_id' => self::resolveParentId($location, $parentSeedKey),
                'sort_order' => $sortOrder,
                'is_visible' => true,
                'is_external' => false,
                'target' => '_self',
            ],
        );
    }

    private static function hideMenuItem(MenuLocation $location, string $seedKey, ?int $pageId = null): void
    {
        MenuItem::query()
            ->where('menu_location', $location)
            ->where(function ($query) use ($seedKey, $pageId): void {
                $query->where('seed_key', $seedKey);

                if ($pageId !== null) {
                    $query->orWhere('page_id', $pageId);
                }
            })
            ->update(['is_visible' => false]);
    }

    private static function resolveParentId(MenuLocation $location, ?string $parentSeedKey): ?int
    {
        if ($parentSeedKey === null || $parentSeedKey === '') {
            return null;
        }

        return MenuItem::query()
            ->where('menu_location', $location)
            ->where('seed_key', $parentSeedKey)
            ->value('id');
    }
}
