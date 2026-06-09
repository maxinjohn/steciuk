<?php

use Mews\Purifier\Facades\Purifier;

if (! function_exists('safeHtml')) {
    function safeHtml(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        return Purifier::clean($html);
    }
}

if (! function_exists('safeEmbed')) {
    function safeEmbed(?string $html): string
    {
        return \App\Support\EmbedSanitizer::iframe($html);
    }
}

if (! function_exists('safeCustomCss')) {
    function safeCustomCss(?string $css): string
    {
        return e(\App\Support\CustomAssetSanitizer::css($css) ?? '');
    }
}

if (! function_exists('safeCustomJs')) {
    function safeCustomJs(?string $js): string
    {
        return e(\App\Support\CustomAssetSanitizer::js($js) ?? '');
    }
}

if (! function_exists('safeUrl')) {
    function safeUrl(?string $url, string $fallback = '#'): string
    {
        return \App\Support\SafeUrl::forHref($url, $fallback);
    }
}
