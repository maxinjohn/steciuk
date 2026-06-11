<?php

namespace App\Services;

use App\Models\ContentBlock;
use App\Models\Page;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PageContext
{
    private const TTL_SECONDS = 3600;

    public static function resolve(string $slug): ?Page
    {
        $key = 'page.context.'.$slug.'.v1';

        $attributes = Cache::remember($key, self::TTL_SECONDS, function () use ($slug): ?array {
            $page = Page::query()
                ->select([
                    'id', 'title', 'slug', 'hero_title', 'hero_subtitle', 'featured_image',
                    'content', 'seo_title', 'seo_description', 'template', 'accent_color',
                    'layout_variant', 'hero_style', 'show_hero', 'is_home', 'meta_robots',
                    'custom_css', 'custom_js',
                ])
                ->where('slug', $slug)
                ->published()
                ->first();

            if ($page === null) {
                return null;
            }

            $blocks = $page->contentBlocks()
                ->select(['id', 'page_id', 'type', 'title', 'content', 'sort_order', 'is_visible'])
                ->where('is_visible', true)
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($block) => $block->getAttributes())
                ->values()
                ->all();

            return [
                'page' => $page->getAttributes(),
                'blocks' => $blocks,
            ];
        });

        if ($attributes === null) {
            return null;
        }

        $page = (new Page)->newFromBuilder($attributes['page']);

        $page->setRelation(
            'contentBlocks',
            $attributes['blocks'] === []
                ? new EloquentCollection
                : new EloquentCollection(ContentBlock::hydrate($attributes['blocks'])),
        );

        return $page;
    }

    public static function forget(string $slug): void
    {
        Cache::forget('page.context.'.$slug.'.v1');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function view(string $view, string $pageSlug, array $data = []): View
    {
        $page = static::resolve($pageSlug);

        return view($view, array_merge($data, [
            'page' => $page,
            'pageSlug' => $pageSlug,
        ]));
    }
}
