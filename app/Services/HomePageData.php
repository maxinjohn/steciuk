<?php

namespace App\Services;

use App\Enums\PublishStatus;
use App\Models\ContentBlock;
use App\Models\Event;
use App\Models\GalleryAlbum;
use App\Models\Ministry;
use App\Models\News;
use App\Models\Page;
use App\Models\Service;
use App\Models\Sermon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;

class HomePageData
{
    private const CACHE_KEY = 'home.page.data.v4';

    private const CACHE_TTL_SECONDS = 3600;

    /**
     * @return array<string, mixed>
     */
    public static function resolve(): array
    {
        static $resolved = null;

        if ($resolved !== null) {
            return $resolved;
        }

        $packed = Cache::remember(
            static::CACHE_KEY,
            static::CACHE_TTL_SECONDS,
            static fn (): array => static::pack(static::build()),
        );

        $resolved = static::unpack($packed);

        return $resolved;
    }

    /**
     * @return array<string, mixed>
     */
    private static function build(): array
    {
        $page = Page::query()
            ->select([
                'id', 'title', 'slug', 'hero_title', 'hero_subtitle', 'featured_image',
                'content', 'seo_title', 'seo_description', 'template', 'accent_color',
                'layout_variant', 'hero_style', 'show_hero', 'is_home', 'meta_robots',
                'custom_css', 'custom_js',
            ])
            ->where('is_home', true)
            ->published()
            ->with([
                'contentBlocks' => fn ($q) => $q
                    ->select(['id', 'page_id', 'type', 'title', 'content', 'sort_order', 'is_visible'])
                    ->where('is_visible', true)
                    ->orderBy('sort_order'),
            ])
            ->first();

        return [
            'page' => $page,
            'services' => Service::query()
                ->select(['id', 'title', 'location', 'service_day', 'service_time', 'sort_order', 'status'])
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->get(),
            'ministries' => Ministry::query()
                ->select(['id', 'name', 'slug', 'short_description', 'featured_image', 'sort_order', 'status'])
                ->where('status', 'published')
                ->orderBy('sort_order')
                ->limit(6)
                ->get(),
            'events' => Event::query()
                ->select(['id', 'title', 'slug', 'starts_at', 'location', 'featured_image', 'status'])
                ->published()
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->limit(4)
                ->get(),
            'news' => News::query()
                ->select(['id', 'title', 'slug', 'published_at', 'excerpt', 'featured_image', 'status'])
                ->published()
                ->orderByDesc('published_at')
                ->limit(3)
                ->get(),
            'sermons' => Sermon::query()
                ->select(['id', 'title', 'speaker', 'preached_at', 'bible_passage', 'youtube_url', 'status'])
                ->where('status', PublishStatus::Published)
                ->orderByDesc('preached_at')
                ->limit(3)
                ->get(),
            'albums' => GalleryAlbum::query()
                ->select(['id', 'title', 'slug', 'cover_image', 'sort_order', 'status'])
                ->where('status', 'published')
                ->orderBy('sort_order')
                ->limit(4)
                ->get(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function pack(array $data): array
    {
        /** @var Page|null $page */
        $page = $data['page'];

        return [
            'page' => $page?->getAttributes(),
            'content_blocks' => $page?->contentBlocks
                ?->map(static fn (ContentBlock $block): array => $block->getAttributes())
                ->values()
                ->all() ?? [],
            'services' => static::packModels($data['services']),
            'ministries' => static::packModels($data['ministries']),
            'events' => static::packModels($data['events']),
            'news' => static::packModels($data['news']),
            'sermons' => static::packModels($data['sermons']),
            'albums' => static::packModels($data['albums']),
        ];
    }

    /**
     * @param  array<string, mixed>  $packed
     * @return array<string, mixed>
     */
    private static function unpack(array $packed): array
    {
        $page = null;

        if (! empty($packed['page']) && is_array($packed['page'])) {
            $page = (new Page)->newFromBuilder($packed['page']);

            if (! empty($packed['content_blocks'])) {
                $page->setRelation(
                    'contentBlocks',
                    new EloquentCollection(ContentBlock::hydrate($packed['content_blocks'])),
                );
            }
        }

        return [
            'page' => $page,
            'services' => static::hydrateModels(Service::class, $packed['services'] ?? []),
            'ministries' => static::hydrateModels(Ministry::class, $packed['ministries'] ?? []),
            'events' => static::hydrateModels(Event::class, $packed['events'] ?? []),
            'news' => static::hydrateModels(News::class, $packed['news'] ?? []),
            'sermons' => static::hydrateModels(Sermon::class, $packed['sermons'] ?? []),
            'albums' => static::hydrateModels(GalleryAlbum::class, $packed['albums'] ?? []),
        ];
    }

    /**
     * @param  iterable<int, \Illuminate\Database\Eloquent\Model>  $models
     * @return list<array<string, mixed>>
     */
    private static function packModels(iterable $models): array
    {
        $rows = [];

        foreach ($models as $model) {
            $rows[] = $model->getAttributes();
        }

        return $rows;
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     * @param  list<array<string, mixed>>  $rows
     */
    private static function hydrateModels(string $modelClass, array $rows): EloquentCollection
    {
        if ($rows === []) {
            return new EloquentCollection;
        }

        return new EloquentCollection($modelClass::hydrate($rows));
    }

    public static function forget(): void
    {
        foreach (['home.page.data.v4', 'home.page.data.v3', 'home.page.data.v2', 'home.page.data.v1'] as $key) {
            Cache::forget($key);
        }
    }
}
