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
        $iconSrc = Setting::assetUrl($logo) ?? asset('images/steci-mark.svg');

        $icons = [
            [
                'src' => $iconSrc,
                'sizes' => '192x192',
                'type' => str_ends_with($iconSrc, '.svg') ? 'image/svg+xml' : 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => $iconSrc,
                'sizes' => '512x512',
                'type' => str_ends_with($iconSrc, '.svg') ? 'image/svg+xml' : 'image/png',
                'purpose' => 'any maskable',
            ],
        ];

        $manifest = [
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
                    'url' => '/service-times',
                    'description' => 'Find worship locations across the UK',
                ],
                [
                    'name' => 'Events',
                    'url' => '/events',
                    'description' => 'Upcoming parish events',
                ],
                [
                    'name' => 'Prayer Request',
                    'url' => '/prayer-request',
                    'description' => 'Submit a confidential prayer request',
                ],
            ],
        ];

        return response(json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), 200, [
            'Content-Type' => 'application/manifest+json',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
