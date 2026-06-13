<?php

namespace App\Support;

use App\Models\Page;
use Illuminate\Support\Str;

final class PageTopicArt
{
    public static function excerpt(?string $html): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $html)));

        return Str::limit($text, 400, '');
    }

    public static function hasRealFeaturedImage(?string $path): bool
    {
        if ($path === null || trim($path) === '') {
            return false;
        }

        $path = trim($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return true;
        }

        return public_upload_url($path) !== null;
    }

    public static function resolveTopic(
        ?string $slug = null,
        ?string $title = null,
        string $context = 'page',
        ?string $content = null,
        ?string $category = null,
        ?string $subtitle = null,
    ): string {
        return SiteTopicArt::resolve(
            $slug,
            $title,
            $context,
            $category,
            self::contentHint($content, $subtitle),
        );
    }

    public static function mediaUrl(
        ?string $slug = null,
        ?string $title = null,
        string $context = 'page',
        ?string $content = null,
        ?string $category = null,
        ?string $subtitle = null,
    ): string {
        return SiteTopicArt::mediaUrl(
            $slug,
            $title,
            $context,
            $category,
            self::contentHint($content, $subtitle),
        );
    }

    /**
     * @param  string|null  ...$parts
     */
    public static function contentHint(?string ...$parts): string
    {
        return SiteTopicArt::buildContentHint(...$parts);
    }

    public static function contentHintForPage(Page $page): string
    {
        return self::contentHint(
            $page->content,
            $page->hero_subtitle,
            $page->seo_description,
            $page->hero_title ?: $page->title,
        );
    }

    public static function contentHintForRecord(
        ?string $description = null,
        ?string $excerpt = null,
        ?string $subtitle = null,
        ?string $location = null,
        ?string $category = null,
    ): string {
        return self::contentHint($description, $excerpt, $subtitle, $location, $category);
    }

    public static function topicLabel(string $topic): string
    {
        return Str::headline(str_replace('-', ' ', $topic));
    }

    public static function forPage(Page $page): array
    {
        $slug = $page->slug;
        $title = $page->hero_title ?: $page->title;
        $context = self::contextForPage($page);

        $hint = self::contentHintForPage($page);

        return [
            'topic' => self::resolveTopic($slug, $title, $context, $hint),
            'url' => self::mediaUrl($slug, $title, $context, $hint),
        ];
    }

    public static function contextForSlug(?string $slug): string
    {
        $slug = (string) $slug;

        $ministrySlugs = [
            'ministries', 'sunday-school', 'youth-fellowship', 'womens-fellowship',
            'choir', 'prayer-groups', 'evangelism-mission', 'pastoral-care',
        ];

        if (in_array($slug, $ministrySlugs, true)) {
            return 'ministry';
        }

        return match ($slug) {
            'events' => 'event',
            'news' => 'news',
            'sermons' => 'sermon',
            'resources', 'liturgy', 'lectionary' => 'resource',
            'gallery' => 'gallery',
            'service-times', 'online-worship', 'uk-locations' => 'service',
            default => 'page',
        };
    }

    public static function contextForPage(Page $page): string
    {
        if ($page->is_home) {
            return 'page';
        }

        return self::contextForSlug($page->slug);
    }

    public static function syncHeroStyleForTopicArt(Page $page): void
    {
        if (self::hasRealFeaturedImage($page->featured_image)) {
            return;
        }

        if ($page->hero_style === 'image') {
            $page->hero_style = 'gradient';
        }
    }
}
