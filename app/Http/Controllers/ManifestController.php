<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Response;

class ManifestController extends Controller
{
    public function __invoke(): Response
    {
        $name = Setting::get('church_name', 'STECI UK Parish');
        $shortName = Setting::get('pwa_short_name', 'STECI UK');
        $description = Setting::get('motto', 'For the Word of God and for the testimony of Jesus Christ');
        $themeColor = Setting::get('theme_color', '#d4cabb');
        $logo = Setting::get('logo');
        $customIcon = Setting::assetUrl($logo);
        $icon192 = $customIcon && ! str_ends_with($customIcon, '.svg')
            ? $customIcon
            : asset('icons/icon-192.png');
        $icon512 = $customIcon && ! str_ends_with($customIcon, '.svg')
            ? $customIcon
            : asset('icons/icon-512.png');
        $svgMark = asset('icons/favicon.svg');

        $icons = [
            [
                'src' => $icon192,
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => $icon512,
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => $icon512,
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'maskable',
            ],
            [
                'src' => $svgMark,
                'sizes' => 'any',
                'type' => 'image/svg+xml',
                'purpose' => 'any',
            ],
        ];

        $manifest = [
            'id' => '/',
            'name' => $name,
            'short_name' => $shortName,
            'description' => $description,
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait-primary',
            'background_color' => '#d4cabb',
            'theme_color' => $themeColor,
            'categories' => ['lifestyle', 'social'],
            'lang' => 'en-GB',
            'dir' => 'ltr',
            'icons' => $icons,
            'shortcuts' => [
                [
                    'name' => 'Service Times',
                    'short_name' => 'Worship',
                    'url' => '/service-times',
                    'description' => 'Find worship locations across the UK',
                    'icons' => [['src' => $icon192, 'sizes' => '192x192', 'type' => 'image/png']],
                ],
                [
                    'name' => 'Events',
                    'short_name' => 'Events',
                    'url' => '/events',
                    'description' => 'Upcoming parish events',
                    'icons' => [['src' => $icon192, 'sizes' => '192x192', 'type' => 'image/png']],
                ],
                [
                    'name' => 'News',
                    'short_name' => 'News',
                    'url' => '/news',
                    'description' => 'Latest parish news',
                    'icons' => [['src' => $icon192, 'sizes' => '192x192', 'type' => 'image/png']],
                ],
                [
                    'name' => 'Give',
                    'short_name' => 'Give',
                    'url' => '/give',
                    'description' => 'Support parish ministry',
                    'icons' => [['src' => $icon192, 'sizes' => '192x192', 'type' => 'image/png']],
                ],
                [
                    'name' => 'Prayer Request',
                    'short_name' => 'Prayer',
                    'url' => '/prayer-request',
                    'description' => 'Submit a confidential prayer request',
                    'icons' => [['src' => $icon192, 'sizes' => '192x192', 'type' => 'image/png']],
                ],
            ],
        ];

        return response(json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), 200, [
            'Content-Type' => 'application/manifest+json',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
