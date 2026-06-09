<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

use App\Support\AdminPanelConfig;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $sitemap = route('sitemap');
        $host = rtrim(config('app.url'), '/');
        $adminPath = AdminPanelConfig::path();

        $lines = [
            '# STECI UK Parish',
            'User-agent: *',
            'Allow: /',
            'Disallow: /'.$adminPath,
            'Disallow: /'.$adminPath.'/',
            'Disallow: /offline',
            'Disallow: /livewire/',
            '',
            'User-agent: GPTBot',
            'Disallow: /',
            '',
            'User-agent: CCBot',
            'Disallow: /',
            '',
            'Sitemap: '.$sitemap,
            'Host: '.$host,
        ];

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
