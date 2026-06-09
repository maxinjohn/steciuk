<?php

namespace App\Services;

use App\Models\Event;
use App\Models\GalleryAlbum;
use App\Models\Ministry;
use App\Models\News;
use App\Models\Page;
use App\Support\Seo;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Route;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapBuilder
{
    /** @var array<string, true> */
    private array $registered = [];

    public function build(): Sitemap
    {
        $sitemap = Sitemap::create();

        $this->addUrl($sitemap, route('home'), priority: 1.0, changeFrequency: Url::CHANGE_FREQUENCY_WEEKLY);

        foreach ($this->indexRoutes() as $routeName => $config) {
            if (Route::has($routeName)) {
                $this->addUrl(
                    $sitemap,
                    route($routeName),
                    priority: $config['priority'],
                    changeFrequency: $config['frequency'],
                );
            }
        }

        Page::query()
            ->published()
            ->orderBy('sort_order')
            ->get(['id', 'slug', 'is_home', 'meta_robots', 'updated_at'])
            ->each(function (Page $page) use ($sitemap): void {
                if ($page->is_home || ! Seo::isIndexable($page->meta_robots)) {
                    return;
                }

                if (Seo::isReservedSlug($page->slug)) {
                    return;
                }

                $this->addUrl(
                    $sitemap,
                    route('pages.show', $page->slug),
                    lastModified: $page->updated_at,
                    priority: 0.8,
                    changeFrequency: Url::CHANGE_FREQUENCY_MONTHLY,
                );
            });

        Event::query()
            ->published()
            ->orderByDesc('starts_at')
            ->get(['slug', 'updated_at'])
            ->each(fn (Event $event) => $this->addUrl(
                $sitemap,
                route('events.show', $event->slug),
                lastModified: $event->updated_at,
                priority: 0.7,
                changeFrequency: Url::CHANGE_FREQUENCY_WEEKLY,
            ));

        News::query()
            ->published()
            ->orderByDesc('published_at')
            ->get(['slug', 'updated_at'])
            ->each(fn (News $item) => $this->addUrl(
                $sitemap,
                route('news.show', $item->slug),
                lastModified: $item->updated_at,
                priority: 0.7,
                changeFrequency: Url::CHANGE_FREQUENCY_WEEKLY,
            ));

        Ministry::query()
            ->active()
            ->orderBy('sort_order')
            ->get(['slug', 'updated_at'])
            ->each(fn (Ministry $ministry) => $this->addUrl(
                $sitemap,
                route('ministries.show', $ministry->slug),
                lastModified: $ministry->updated_at,
                priority: 0.65,
                changeFrequency: Url::CHANGE_FREQUENCY_MONTHLY,
            ));

        GalleryAlbum::query()
            ->active()
            ->orderBy('sort_order')
            ->get(['slug', 'updated_at'])
            ->each(fn (GalleryAlbum $album) => $this->addUrl(
                $sitemap,
                route('gallery.show', $album->slug),
                lastModified: $album->updated_at,
                priority: 0.6,
                changeFrequency: Url::CHANGE_FREQUENCY_MONTHLY,
            ));

        return $sitemap;
    }

    /**
     * @return array<string, array{priority: float, frequency: string}>
     */
    private function indexRoutes(): array
    {
        return [
            'events.index' => ['priority' => 0.85, 'frequency' => Url::CHANGE_FREQUENCY_WEEKLY],
            'news.index' => ['priority' => 0.85, 'frequency' => Url::CHANGE_FREQUENCY_WEEKLY],
            'sermons.index' => ['priority' => 0.75, 'frequency' => Url::CHANGE_FREQUENCY_WEEKLY],
            'ministries.index' => ['priority' => 0.8, 'frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            'gallery.index' => ['priority' => 0.75, 'frequency' => Url::CHANGE_FREQUENCY_WEEKLY],
            'resources.index' => ['priority' => 0.7, 'frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            'services.index' => ['priority' => 0.85, 'frequency' => Url::CHANGE_FREQUENCY_WEEKLY],
        ];
    }

    private function addUrl(
        Sitemap $sitemap,
        string $location,
        ?CarbonInterface $lastModified = null,
        float $priority = 0.5,
        string $changeFrequency = Url::CHANGE_FREQUENCY_WEEKLY,
    ): void {
        $key = rtrim($location, '/');

        if (isset($this->registered[$key])) {
            return;
        }

        $tag = Url::create($location)
            ->setPriority($priority)
            ->setChangeFrequency($changeFrequency);

        if ($lastModified !== null) {
            $tag->setLastModificationDate($lastModified);
        }

        $sitemap->add($tag);
        $this->registered[$key] = true;
    }
}
