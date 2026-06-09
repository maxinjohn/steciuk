<?php

namespace Database\Seeders;

use App\Enums\MenuLocation;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /** @var array<string, array<string, int>> */
    private array $idsByLocationAndKey = [];

    public function run(): void
    {
        $pages = Page::query()->pluck('id', 'slug');

        $headerStructure = [
            ['label' => 'Home', 'slug' => 'home', 'seed_key' => 'home'],
            [
                'label' => 'About',
                'seed_key' => 'about',
                'children' => [
                    ['label' => 'Welcome', 'slug' => 'welcome', 'seed_key' => 'about.welcome'],
                    ['label' => 'Our Church', 'slug' => 'our-church', 'seed_key' => 'about.our-church'],
                    ['label' => 'STECI Heritage', 'slug' => 'steci-heritage', 'seed_key' => 'about.steci-heritage'],
                    ['label' => 'Mission & Vision', 'slug' => 'mission-vision', 'seed_key' => 'about.mission-vision'],
                    ['label' => 'Leadership', 'slug' => 'leadership', 'seed_key' => 'about.leadership'],
                    ['label' => 'Locations', 'slug' => 'uk-locations', 'seed_key' => 'about.uk-locations'],
                ],
            ],
            [
                'label' => 'Worship',
                'seed_key' => 'worship',
                'children' => [
                    ['label' => 'Service Times', 'slug' => 'service-times', 'seed_key' => 'worship.service-times'],
                    ['label' => 'Online Worship', 'slug' => 'online-worship', 'seed_key' => 'worship.online-worship'],
                    ['label' => 'Sermons', 'slug' => 'sermons', 'seed_key' => 'worship.sermons'],
                ],
            ],
            [
                'label' => 'Ministries',
                'seed_key' => 'ministries',
                'children' => [
                    ['label' => 'Overview', 'slug' => 'ministries', 'seed_key' => 'ministries.overview'],
                    ['label' => 'Sunday School', 'slug' => 'sunday-school', 'seed_key' => 'ministries.sunday-school'],
                    ['label' => 'Youth Fellowship', 'slug' => 'youth-fellowship', 'seed_key' => 'ministries.youth-fellowship'],
                    ['label' => "Women's Fellowship", 'slug' => 'womens-fellowship', 'seed_key' => 'ministries.womens-fellowship'],
                    ['label' => 'Choir', 'slug' => 'choir', 'seed_key' => 'ministries.choir'],
                    ['label' => 'Prayer Groups', 'slug' => 'prayer-groups', 'seed_key' => 'ministries.prayer-groups'],
                ],
            ],
            ['label' => 'Events', 'slug' => 'events', 'seed_key' => 'events'],
            ['label' => 'News', 'slug' => 'news', 'seed_key' => 'news'],
            [
                'label' => 'Resources',
                'seed_key' => 'resources',
                'children' => [
                    ['label' => 'Overview', 'slug' => 'resources', 'seed_key' => 'resources.overview'],
                    ['label' => 'Liturgy', 'slug' => 'liturgy', 'seed_key' => 'resources.liturgy'],
                    ['label' => 'Lectionary', 'slug' => 'lectionary', 'seed_key' => 'resources.lectionary'],
                    ['label' => 'Gallery', 'slug' => 'gallery', 'seed_key' => 'resources.gallery'],
                ],
            ],
            [
                'label' => 'Contact',
                'seed_key' => 'contact',
                'children' => [
                    ['label' => 'Contact Us', 'slug' => 'contact', 'seed_key' => 'contact.contact-us'],
                    ['label' => 'Prayer Request', 'slug' => 'prayer-request', 'seed_key' => 'contact.prayer-request'],
                    ['label' => 'New Member', 'slug' => 'new-member', 'seed_key' => 'contact.new-member'],
                ],
            ],
        ];

        $footerStructure = [
            ['label' => 'Home', 'slug' => 'home', 'seed_key' => 'home'],
            ['label' => 'Welcome', 'slug' => 'welcome', 'seed_key' => 'welcome'],
            ['label' => 'Service Times', 'slug' => 'service-times', 'seed_key' => 'service-times'],
            ['label' => 'Events', 'slug' => 'events', 'seed_key' => 'events'],
            ['label' => 'News', 'slug' => 'news', 'seed_key' => 'news'],
            ['label' => 'Contact', 'slug' => 'contact', 'seed_key' => 'contact'],
            ['label' => 'Safeguarding', 'slug' => 'safeguarding', 'seed_key' => 'safeguarding'],
            ['label' => 'Privacy Policy', 'slug' => 'privacy-policy', 'seed_key' => 'privacy-policy'],
            ['label' => 'Terms of Use', 'slug' => 'terms-of-use', 'seed_key' => 'terms-of-use'],
        ];

        $this->seedMenu($headerStructure, MenuLocation::Header, $pages);
        $this->pruneLegacyDuplicates(MenuLocation::Header);

        $this->seedMenu($headerStructure, MenuLocation::Mobile, $pages);
        $this->pruneLegacyDuplicates(MenuLocation::Mobile);

        $this->seedMenu($footerStructure, MenuLocation::Footer, $pages);
        $this->pruneLegacyDuplicates(MenuLocation::Footer);
    }

    /**
     * Remove pre-seed_key duplicates while keeping prod-only custom links.
     */
    private function pruneLegacyDuplicates(MenuLocation $location): void
    {
        $seeded = MenuItem::query()
            ->where('menu_location', $location)
            ->whereNotNull('seed_key')
            ->get(['id', 'label', 'url', 'page_id', 'parent_id', 'seed_key']);

        $legacyRoots = MenuItem::query()
            ->where('menu_location', $location)
            ->whereNull('seed_key')
            ->whereNull('parent_id')
            ->get();

        foreach ($legacyRoots as $legacy) {
            if ($this->hasSeededCounterpart($legacy, $seeded)) {
                $this->deleteMenuSubtree($legacy);
            }
        }

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
            if ($seed->label !== $legacy->label) {
                return false;
            }

            if ($seed->url !== $legacy->url) {
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
                    'url' => isset($item['slug']) ? '/'.$item['slug'] : null,
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
}
