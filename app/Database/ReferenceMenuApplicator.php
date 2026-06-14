<?php

namespace App\Database;

use App\Enums\MenuLocation;
use App\Models\MenuItem;
use App\Models\Page;
use App\Services\NavigationMenuSync;
use App\Support\ReferenceSiteContent;
use Illuminate\Support\Facades\Schema;

class ReferenceMenuApplicator
{
    /** @var array<string, array<string, int>> */
    private array $idsByLocationAndKey = [];

    public static function apply(): void
    {
        if (! Schema::hasTable('menu_items') || ! Schema::hasTable('pages')) {
            return;
        }

        (new self)->run();
    }

    private function run(): void
    {
        $pages = Page::query()->pluck('id', 'slug');

        foreach (ReferenceSiteContent::menus() as $location => $structure) {
            $menuLocation = MenuLocation::from($location);
            $this->idsByLocationAndKey = [];
            $sortOrder = 0;

            $this->seedMenu($structure, $menuLocation, $pages, null, $sortOrder);
            $this->pruneOrphanedSeeds($menuLocation, $structure);
            $this->pruneLegacyDuplicates($menuLocation);
        }

        NavigationMenuSync::applyAll();
    }

    /**
     * @param  array<int, array<string, mixed>>  $structure
     * @param  \Illuminate\Support\Collection<string, int>  $pages
     */
    private function seedMenu(array $structure, MenuLocation $location, $pages, ?string $parentSeedKey = null, int &$sortOrder = 0): void
    {
        foreach ($structure as $item) {
            $sortOrder++;
            $seedKey = $item['seed_key'];
            $locationKey = $location->value;

            $parentId = $parentSeedKey
                ? ($this->idsByLocationAndKey[$locationKey][$parentSeedKey] ?? null)
                : null;

            $menuItem = MenuItem::query()->updateOrCreate(
                [
                    'menu_location' => $location,
                    'seed_key' => $seedKey,
                ],
                [
                    'label' => $item['label'],
                    'url' => $item['url'] ?? (isset($item['slug']) ? '/'.$item['slug'] : null),
                    'page_id' => isset($item['slug']) ? ($pages[$item['slug']] ?? null) : null,
                    'parent_id' => $parentId,
                    'target' => '_self',
                    'sort_order' => $sortOrder,
                    'is_visible' => true,
                    'is_external' => false,
                ],
            );

            $this->idsByLocationAndKey[$locationKey][$seedKey] = $menuItem->id;

            if (! empty($item['children'])) {
                $this->seedMenu($item['children'], $location, $pages, $seedKey, $sortOrder);
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $structure
     */
    private function pruneOrphanedSeeds(MenuLocation $location, array $structure): void
    {
        $validKeys = $this->collectSeedKeys($structure);

        MenuItem::query()
            ->where('menu_location', $location)
            ->whereNotNull('seed_key')
            ->whereNotIn('seed_key', $validKeys)
            ->get()
            ->each(fn (MenuItem $item) => $this->deleteMenuSubtree($item));
    }

    /**
     * @param  array<int, array<string, mixed>>  $structure
     * @return list<string>
     */
    private function collectSeedKeys(array $structure): array
    {
        $keys = [];

        foreach ($structure as $item) {
            $keys[] = $item['seed_key'];

            if (! empty($item['children'])) {
                $keys = array_merge($keys, $this->collectSeedKeys($item['children']));
            }
        }

        return $keys;
    }

    private function pruneLegacyDuplicates(MenuLocation $location): void
    {
        $seeded = MenuItem::query()
            ->where('menu_location', $location)
            ->whereNotNull('seed_key')
            ->get(['id', 'label', 'url', 'page_id', 'parent_id', 'seed_key']);

        MenuItem::query()
            ->where('menu_location', $location)
            ->whereNull('seed_key')
            ->whereNull('parent_id')
            ->get()
            ->each(function (MenuItem $legacy) use ($seeded): void {
                if ($this->hasSeededCounterpart($legacy, $seeded)) {
                    $this->deleteMenuSubtree($legacy);
                }
            });

        MenuItem::query()
            ->where('menu_location', $location)
            ->whereNull('seed_key')
            ->whereNotNull('parent_id')
            ->get()
            ->each(function (MenuItem $legacy) use ($seeded): void {
                if ($this->hasSeededCounterpart($legacy, $seeded)) {
                    $legacy->delete();
                }
            });
    }

    /** @param \Illuminate\Support\Collection<int, MenuItem> $seeded */
    private function hasSeededCounterpart(MenuItem $legacy, $seeded): bool
    {
        $legacyParent = $legacy->parent_id ? MenuItem::query()->find($legacy->parent_id) : null;

        return $seeded->contains(function (MenuItem $seed) use ($legacy, $legacyParent): bool {
            if ($seed->label !== $legacy->label || $seed->url !== $legacy->url) {
                return false;
            }

            if ($seed->page_id && $legacy->page_id && (int) $seed->page_id !== (int) $legacy->page_id) {
                return false;
            }

            if ($legacy->parent_id === null) {
                return $seed->parent_id === null;
            }

            if (! $legacyParent) {
                return false;
            }

            $seedParent = $seeded->firstWhere('id', $seed->parent_id);

            return $seedParent && $seedParent->label === $legacyParent->label;
        });
    }

    private function deleteMenuSubtree(MenuItem $item): void
    {
        $item->children()->each(fn (MenuItem $child) => $this->deleteMenuSubtree($child));
        $item->delete();
    }
}
