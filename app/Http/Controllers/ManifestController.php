<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Response;

class ManifestController extends Controller
{
    public function __invoke(): Response
    {
        $name = Setting::get('church_name', 'STECI UK Parish');
        $shortName = 'STECI UK';
        $description = Setting::get('motto', 'For the Word of God and for the testimony of Jesus Christ');
        $themeColor = '#1a2332';
        $logo = Setting::get('logo');

        $icons = [
            [
                'src' => $logo ? asset('storage/'.ltrim($logo, '/')) : asset('icons/icon-192.png'),
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => $logo ? asset('storage/'.ltrim($logo, '/')) : asset('icons/icon-512.png'),
                'sizes' => '512x512',
                'type' => 'image/png',
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
            'background_color' => '#faf9f7',
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
