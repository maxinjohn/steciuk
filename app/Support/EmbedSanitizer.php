<?php

namespace App\Support;

class EmbedSanitizer
{
    /** @var list<string> */
    private const ALLOWED_FRAME_HOSTS = [
        'www.youtube.com',
        'youtube.com',
        'www.youtube-nocookie.com',
        'youtube-nocookie.com',
        'www.google.com',
        'google.com',
        'maps.google.com',
        'www.google.co.uk',
    ];

    public static function iframe(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        if (! preg_match('/<iframe\b[^>]*>/i', $html, $match)) {
            return '';
        }

        if (! preg_match('/\ssrc=(["\'])(.*?)\1/i', $match[0], $srcMatch)) {
            return '';
        }

        $host = parse_url(html_entity_decode($srcMatch[2]), PHP_URL_HOST);

        if ($host === null || ! in_array(strtolower($host), self::ALLOWED_FRAME_HOSTS, true)) {
            return '';
        }

        return '<iframe src="'.e($srcMatch[2]).'" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen class="h-full w-full border-0"></iframe>';
    }
}
