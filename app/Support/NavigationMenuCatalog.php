<?php

namespace App\Support;

final class NavigationMenuCatalog
{
    /**
     * @return array<string, string>
     */
    public static function parentLabels(): array
    {
        return [
            '' => 'Top level (no submenu)',
            'about' => 'About',
            'worship' => 'Worship',
            'ministries' => 'Ministries',
            'resources' => 'Resources',
            'contact' => 'Contact',
        ];
    }

    /**
     * @return list<MenuLocation::Header|MenuLocation::Mobile>
     */
    public static function primaryLocations(): array
    {
        return [
            \App\Enums\MenuLocation::Header,
            \App\Enums\MenuLocation::Mobile,
        ];
    }

    /**
     * @return array<string, array{seed_key: string, parent_seed_key: string|null}>
     */
    public static function referencePagePlacements(): array
    {
        $placements = [];

        foreach (ReferenceSiteContent::menus()['header'] ?? [] as $item) {
            self::collectPagePlacements($item, null, $placements);
        }

        return $placements;
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, array{seed_key: string, parent_seed_key: string|null}>  $placements
     */
    private static function collectPagePlacements(array $item, ?string $parentSeedKey, array &$placements): void
    {
        if (! empty($item['slug'])) {
            $placements[(string) $item['slug']] = [
                'seed_key' => (string) $item['seed_key'],
                'parent_seed_key' => $parentSeedKey,
            ];
        }

        foreach ($item['children'] ?? [] as $child) {
            self::collectPagePlacements($child, (string) $item['seed_key'], $placements);
        }
    }

    public static function referenceSeedKeyForPageSlug(string $slug): ?string
    {
        return self::referencePagePlacements()[$slug]['seed_key'] ?? null;
    }

    public static function referenceParentSeedKeyForPageSlug(string $slug): ?string
    {
        $placement = self::referencePagePlacements()[$slug] ?? null;

        return $placement['parent_seed_key'] ?? null;
    }

    public static function pageIsInReferenceMenu(string $slug): bool
    {
        return isset(self::referencePagePlacements()[$slug]);
    }

    public static function ministrySeedKey(string $slug): string
    {
        return 'ministry.'.$slug;
    }
}
