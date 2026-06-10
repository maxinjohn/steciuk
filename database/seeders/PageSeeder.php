<?php

namespace Database\Seeders;

use App\Enums\ContentBlockType;
use App\Enums\PublishStatus;
use App\Models\ContentBlock;
use App\Models\Page;
use App\Models\User;
use App\Support\ReferenceSiteContent;
use App\Support\SeedConfig;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->where('email', 'admin@steciuk.org')->value('id');

        if (! $adminId) {
            throw new \RuntimeException('Admin user must be seeded before pages.');
        }

        foreach ($this->pages() as $pageData) {
            $blocks = $pageData['content_blocks'] ?? [];
            unset($pageData['content_blocks']);

            $slug = $pageData['slug'];
            $existingPage = Page::query()->where('slug', $slug)->first();

            if ($existingPage && ! SeedConfig::shouldOverwritePages()) {
                if (array_key_exists('show_hero', $pageData)) {
                    $existingPage->update(['show_hero' => $pageData['show_hero']]);
                }

                continue;
            }

            $page = Page::query()->updateOrCreate(
                ['slug' => $slug],
                array_merge($pageData, [
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'status' => PublishStatus::Published,
                ]),
            );

            foreach ($blocks as $index => $block) {
                $seedKey = $block['seed_key'] ?? ('block-'.($index + 1));

                ContentBlock::query()->updateOrCreate(
                    [
                        'page_id' => $page->id,
                        'seed_key' => $seedKey,
                    ],
                    [
                        'type' => $block['type'],
                        'title' => $block['title'] ?? null,
                        'content' => $block['content'] ?? [],
                        'sort_order' => $block['sort_order'] ?? ($index + 1),
                        'is_visible' => $block['is_visible'] ?? true,
                    ],
                );
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function pages(): array
    {
        $bodies = ReferenceSiteContent::pageBodies();
        $fields = ReferenceSiteContent::pageFields();

        $simple = static function (string $title, string $slug, string $template, string $heroTitle, string $heroSubtitle) use ($bodies, $fields): array {
            $content = $bodies[$slug] ?? '';
            $pageFields = $fields[$slug] ?? [];

            return [
                'title' => $title,
                'slug' => $slug,
                'hero_title' => $heroTitle,
                'hero_subtitle' => $heroSubtitle,
                'content' => $content,
                'seo_title' => $pageFields['seo_title'] ?? "{$title} | STECI UK Parish",
                'seo_description' => $pageFields['seo_description'] ?? strip_tags(substr($content, 0, 160)),
                'template' => $template,
                'sort_order' => 0,
                'is_home' => false,
                'show_hero' => false,
            ];
        };

        return [
            $this->homePage(),
            $simple('Welcome', 'welcome', 'about', 'Welcome to Our Parish', 'A warm invitation to worship and fellowship'),
            $simple('Our Church', 'our-church', 'about', 'Our Church', 'Who we are and what we believe'),
            $simple('STECI Heritage', 'steci-heritage', 'about', 'STECI Heritage', 'Rooted in the Saint Thomas Christian tradition'),
            $simple('Mission & Vision', 'mission-vision', 'about', 'Mission & Vision', 'Our calling to worship, witness, and service'),
            $simple('Leadership', 'leadership', 'about', 'Parish Leadership', 'Those who serve our UK Parish community'),
            $simple('UK Locations', 'uk-locations', 'about', 'UK Parish Locations', 'Five worship locations across the United Kingdom'),
            $simple('Service Times', 'service-times', 'default', 'Service Times', 'Monthly worship across five UK cities'),
            $simple('Online Worship', 'online-worship', 'default', 'Online Worship', 'Join us from wherever you are'),
            $simple('Sermons', 'sermons', 'default', 'Sermons & Messages', 'Biblical teaching from our parish'),
            $simple('Ministries', 'ministries', 'default', 'Our Ministries', 'Serving God and one another across the UK'),
            $simple('Sunday School', 'sunday-school', 'default', 'Sunday School', 'Nurturing children in faith'),
            $simple('Youth Fellowship', 'youth-fellowship', 'default', 'Youth Fellowship', 'Growing together in Christ'),
            $simple("Women's Fellowship", 'womens-fellowship', 'default', "Women's Fellowship", 'Prayer, fellowship, and service'),
            $simple('Choir', 'choir', 'default', 'Parish Choir', 'Worship through music'),
            $simple('Prayer Groups', 'prayer-groups', 'default', 'Prayer Groups', 'United in prayer across the UK'),
            $simple('Events', 'events', 'default', 'Parish Events', 'Upcoming gatherings and celebrations'),
            $simple('News', 'news', 'default', 'News & Announcements', 'Latest updates from the UK Parish'),
            $simple('Gallery', 'gallery', 'default', 'Photo Gallery', 'Moments from our parish life'),
            $simple('Resources', 'resources', 'default', 'Resources & Downloads', 'Liturgy, forms, and parish documents'),
            $simple('Liturgy', 'liturgy', 'default', 'Liturgy', 'Order of worship and liturgical resources'),
            $simple('Lectionary', 'lectionary', 'default', 'Lectionary', 'Scripture readings for worship'),
            $simple('Safeguarding', 'safeguarding', 'default', 'Safeguarding', 'Protecting children and vulnerable adults'),
            $simple('Contact', 'contact', 'contact', 'Contact Us', 'We would love to hear from you'),
            $simple('Prayer Request', 'prayer-request', 'form', 'Prayer Request', 'Share your prayer needs with us'),
            $simple('New Member', 'new-member', 'form', 'New Member Registration', 'Join our parish family'),
            $simple('Privacy Policy', 'privacy-policy', 'default', 'Privacy Policy', 'How we handle your personal data'),
            $simple('Terms of Use', 'terms-of-use', 'default', 'Terms of Use', 'Website terms and conditions'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function homePage(): array
    {
        $fields = ReferenceSiteContent::pageFields()['home'];
        $patches = ReferenceSiteContent::homeContentBlockPatches();

        return [
            'title' => 'Home',
            'slug' => 'home',
            'hero_title' => null,
            'hero_subtitle' => null,
            'show_hero' => false,
            'content' => null,
            'seo_title' => $fields['seo_title'],
            'seo_description' => $fields['seo_description'],
            'template' => 'home',
            'sort_order' => 0,
            'is_home' => true,
            'content_blocks' => [
                [
                    'seed_key' => 'hero',
                    'type' => ContentBlockType::Hero,
                    'title' => 'Hero Banner',
                    'sort_order' => 1,
                    'content' => $patches['hero'],
                ],
                [
                    'seed_key' => 'locations',
                    'type' => ContentBlockType::Location,
                    'title' => 'Service Locations',
                    'sort_order' => 2,
                    'content' => $patches['locations'],
                ],
                [
                    'seed_key' => 'welcome-quote',
                    'type' => ContentBlockType::Quote,
                    'title' => 'Welcome Message',
                    'sort_order' => 3,
                    'content' => [
                        'quote' => $patches['welcome-quote']['quote'],
                        'attribution' => 'St. Thomas Evangelical Church of India – UK Parish',
                        'link_url' => '/welcome',
                        'link_label' => 'Read Full Welcome Message',
                    ],
                ],
                [
                    'seed_key' => 'ministries',
                    'type' => ContentBlockType::MinistryCards,
                    'title' => 'Our Ministries',
                    'sort_order' => 4,
                    'content' => [
                        'heading' => 'Serving Christ Together',
                        'subheading' => 'Sunday School, prayer, choir, and mission — for every generation',
                        'limit' => 4,
                        'link_url' => '/ministries',
                        'link_label' => 'View All Ministries',
                    ],
                ],
                [
                    'seed_key' => 'events',
                    'type' => ContentBlockType::EventList,
                    'title' => 'Upcoming Events',
                    'sort_order' => 5,
                    'content' => [
                        'heading' => 'Parish Fellowship',
                        'subheading' => 'Worship gatherings, prayer meetings, and fellowship days',
                        'limit' => 3,
                        'link_url' => '/events',
                        'link_label' => 'See All Events',
                    ],
                ],
                [
                    'seed_key' => 'news',
                    'type' => ContentBlockType::TextImage,
                    'title' => 'Latest News',
                    'sort_order' => 6,
                    'content' => [
                        'heading' => 'Latest News',
                        'body' => 'Gospel-centred news from across our five worship locations — prayer, mission, Holy Communion, and parish fellowship.',
                        'link_url' => '/news',
                        'link_label' => 'Read All News',
                        'image_alt' => 'Parish community gathering',
                    ],
                ],
                [
                    'seed_key' => 'sermons',
                    'type' => ContentBlockType::SermonList,
                    'title' => 'Recent Sermons',
                    'sort_order' => 7,
                    'content' => [
                        'heading' => 'Expository Preaching',
                        'subheading' => 'Sermons from Holy Scripture — for the testimony of Jesus Christ',
                        'limit' => 3,
                        'link_url' => '/sermons',
                        'link_label' => 'Browse All Sermons',
                    ],
                ],
                [
                    'seed_key' => 'gallery',
                    'type' => ContentBlockType::Gallery,
                    'title' => 'Gallery Preview',
                    'sort_order' => 8,
                    'content' => [
                        'heading' => 'Parish Life in Pictures',
                        'limit' => 6,
                        'link_url' => '/gallery',
                        'link_label' => 'View Full Gallery',
                    ],
                ],
                [
                    'seed_key' => 'cta-prayer',
                    'type' => ContentBlockType::Cta,
                    'title' => 'Prayer Request CTA',
                    'sort_order' => 9,
                    'content' => [
                        'heading' => 'Need Prayer?',
                        'body' => 'Our prayer team would be honoured to pray with you. Submit a prayer request and know that you are held in the prayers of our parish family.',
                        'button_label' => 'Submit a Prayer Request',
                        'button_url' => '/prayer-request',
                        'style' => 'primary',
                    ],
                ],
                [
                    'seed_key' => 'cta-new-member',
                    'type' => ContentBlockType::Cta,
                    'title' => 'New Member CTA',
                    'sort_order' => 10,
                    'content' => [
                        'heading' => 'Join Our Parish Family',
                        'body' => 'Whether you are new to the area or exploring faith, we welcome you. Register your interest and a member of our leadership team will be in touch.',
                        'button_label' => 'Register as a New Member',
                        'button_url' => '/new-member',
                        'style' => 'secondary',
                    ],
                ],
            ],
        ];
    }
}
