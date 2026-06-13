<?php

use App\Support\SiteTopicArt;

if (! function_exists('cardMediaTopic')) {
    function cardMediaTopic(
        ?string $slug = null,
        ?string $title = null,
        string $context = 'default',
        ?string $category = null,
        ?string $content = null,
    ): string {
        return SiteTopicArt::resolve($slug, $title, $context, $category, $content);
    }
}

if (! function_exists('cardMediaUrl')) {
    function cardMediaUrl(
        ?string $featuredImage,
        ?string $slug = null,
        ?string $title = null,
        string $context = 'default',
        ?string $category = null,
        ?string $content = null,
    ): string {
        if ($featuredImage !== null && $featuredImage !== '') {
            $uploadUrl = public_upload_url($featuredImage);

            if ($uploadUrl !== null && $uploadUrl !== '') {
                return $uploadUrl;
            }

            if (str_starts_with($featuredImage, 'http')) {
                return $featuredImage;
            }
        }

        return SiteTopicArt::mediaUrl($slug, $title, $context, $category, $content);
    }
}

if (! function_exists('cardMediaIsTopicArt')) {
    function cardMediaIsTopicArt(?string $featuredImage): bool
    {
        if ($featuredImage === null || $featuredImage === '') {
            return true;
        }

        return public_upload_url($featuredImage) === null && ! str_starts_with($featuredImage, 'http');
    }
}
