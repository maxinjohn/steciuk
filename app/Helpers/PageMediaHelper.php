<?php

use App\Support\PageTopicArt;

if (! function_exists('pageTopicArt')) {
    function pageTopicArt(
        ?string $slug = null,
        ?string $title = null,
        string $context = 'page',
        ?string $content = null,
        ?string $category = null,
        ?string $subtitle = null,
    ): string {
        return PageTopicArt::resolveTopic($slug, $title, $context, $content, $category, $subtitle);
    }
}

if (! function_exists('pageTopicArtUrl')) {
    function pageTopicArtUrl(
        ?string $slug = null,
        ?string $title = null,
        string $context = 'page',
        ?string $content = null,
        ?string $category = null,
        ?string $subtitle = null,
    ): string {
        return PageTopicArt::mediaUrl($slug, $title, $context, $content, $category, $subtitle);
    }
}

if (! function_exists('hasRealFeaturedImage')) {
    function hasRealFeaturedImage(?string $image): bool
    {
        return PageTopicArt::hasRealFeaturedImage($image);
    }
}
