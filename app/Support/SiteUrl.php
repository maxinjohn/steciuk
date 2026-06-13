<?php

namespace App\Support;

use Illuminate\Support\Facades\URL;

class SiteUrl
{
    public static function configureRootUrl(): void
    {
        $root = config('app.url');

        if (! filled($root)) {
            return;
        }

        $root = rtrim((string) $root, '/');

        URL::forceRootUrl($root);

        $scheme = parse_url($root, PHP_URL_SCHEME);

        if (filled($scheme)) {
            URL::forceScheme($scheme);
        }
    }

    public static function to(?string $path): string
    {
        return SafeUrl::resolve($path);
    }
}
