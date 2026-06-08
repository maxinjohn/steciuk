<?php

namespace Database\Seeders;

use App\Enums\MenuLocation;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        MenuItem::query()->delete();

        $pages = Page::query()->pluck('id', 'slug');

        $headerStructure = [
            ['label' => 'Home', 'slug' => 'home'],
            [
                'label' => 'About',
                'children' => [
                    ['label' => 'Welcome', 'slug' => 'welcome'],
                    ['label' => 'Our Church', 'slug' => 'our-church'],
                    ['label' => 'STECI Heritage', 'slug' => 'steci-heritage'],
                    ['label' => 'Mission & Vision', 'slug' => 'mission-vision'],
                    ['label' => 'Leadership', 'slug' => 'leadership'],
                    ['label' => 'Locations', 'slug' => 'uk-locations'],
                ],
            ],
            [
                'label' => 'Worship',
                'children' => [
                    ['label' => 'Service Times', 'slug' => 'service-times'],
                    ['label' => 'Online Worship', 'slug' => 'online-worship'],
                    ['label' => 'Sermons', 'slug' => 'sermons'],
                ],
            ],
            [
                'label' => 'Ministries',
                'children' => [
                    ['label' => 'Overview', 'slug' => 'ministries'],
                    ['label' => 'Sunday School', 'slug' => 'sunday-school'],
                    ['label' => 'Youth Fellowship', 'slug' => 'youth-fellowship'],
                    ['label' => "Women's Fellowship", 'slug' => 'womens-fellowship'],
                    ['label' => 'Choir', 'slug' => 'choir'],
                    ['label' => 'Prayer Groups', 'slug' => 'prayer-groups'],
                ],
            ],
            ['label' => 'Events', 'slug' => 'events'],
            ['label' => 'News', 'slug' => 'news'],
            [
                'label' => 'Resources',
                'children' => [
                    ['label' => 'Overview', 'slug' => 'resources'],
                    ['label' => 'Liturgy', 'slug' => 'liturgy'],
                    ['label' => 'Lectionary', 'slug' => 'lectionary'],
                    ['label' => 'Gallery', 'slug' => 'gallery'],
                ],
            ],
            [
                'label' => 'Contact',
                'children' => [
                    ['label' => 'Contact Us', 'slug' => 'contact'],
                    ['label' => 'Prayer Request', 'slug' => 'prayer-request'],
                    ['label' => 'New Member', 'slug' => 'new-member'],
                ],
            ],
        ];

        $footerStructure = [
            ['label' => 'Home', 'slug' => 'home'],
            ['label' => 'Welcome', 'slug' => 'welcome'],
            ['label' => 'Service Times', 'slug' => 'service-times'],
            ['label' => 'Events', 'slug' => 'events'],
            ['label' => 'News', 'slug' => 'news'],
            ['label' => 'Contact', 'slug' => 'contact'],
            ['label' => 'Safeguarding', 'slug' => 'safeguarding'],
            ['label' => 'Privacy Policy', 'slug' => 'privacy-policy'],
            ['label' => 'Terms of Use', 'slug' => 'terms-of-use'],
        ];

        $this->seedMenu($headerStructure, MenuLocation::Header, $pages);
        $this->seedMenu($headerStructure, MenuLocation::Mobile, $pages);
        $this->seedMenu($footerStructure, MenuLocation::Footer, $pages);
    }

    /**
     * @param  array<int, array<string, mixed>>  $structure
     * @param  \Illuminate\Support\Collection<string, int>  $pages
     */
    private function seedMenu(array $structure, MenuLocation $location, $pages, ?int $parentId = null, int &$sortOrder = 0): void
    {
        foreach ($structure as $item) {
            $sortOrder++;

            $menuItem = MenuItem::query()->create([
                'label' => $item['label'],
                'url' => isset($item['slug']) ? '/'.$item['slug'] : null,
                'page_id' => isset($item['slug']) ? ($pages[$item['slug']] ?? null) : null,
                'parent_id' => $parentId,
                'menu_location' => $location,
                'target' => '_self',
                'sort_order' => $sortOrder,
                'is_visible' => true,
                'is_external' => false,
            ]);

            if (! empty($item['children'])) {
                $this->seedMenu($item['children'], $location, $pages, $menuItem->id, $sortOrder);
            }
        }
    }
}
