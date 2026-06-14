<?php

namespace App\Http\Controllers;

use App\Support\AdminPanelConfig;
use App\Support\MemberPortalRoutes;
use Illuminate\Http\Response;

class ServiceWorkerController extends Controller
{
    public function __invoke(): Response
    {
        $cacheVersion = 'steci-'.substr(md5((string) config('app.key')), 0, 8);
        $offlineUrl = route('offline');
        $adminPath = '/'.AdminPanelConfig::path();
        $memberBypassPaths = json_encode(MemberPortalRoutes::serviceWorkerBypassPrefixes());

        $js = <<<JS
const CACHE = '{$cacheVersion}';
const OFFLINE_URL = '{$offlineUrl}';
const MEMBER_BYPASS_PREFIXES = {$memberBypassPaths};

const shouldBypassServiceWorker = (pathname) => {
    if (pathname.startsWith('{$adminPath}') || pathname.startsWith('/livewire')) {
        return true;
    }

    return MEMBER_BYPASS_PREFIXES.some((prefix) => pathname === prefix || pathname.startsWith(prefix + '/'));
};

const cacheFirst = (request) =>
    caches.open(CACHE).then((cache) =>
        cache.match(request).then((cached) =>
            cached || fetch(request).then((response) => {
                if (response.ok) {
                    cache.put(request, response.clone());
                }

                return response;
            })
        )
    );

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE).then((cache) => cache.add(OFFLINE_URL)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    if (shouldBypassServiceWorker(url.pathname)) {
        return;
    }

    if (url.pathname === '/sitemap.xml' || url.pathname === '/robots.txt') {
        return;
    }

    const isStaticAsset =
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/topic-art/') ||
        url.pathname.match(/\\.(css|js|woff2?|png|jpg|jpeg|webp|svg|ico|webmanifest|xml|txt)\$/);

    if (isStaticAsset) {
        event.respondWith(cacheFirst(event.request));
        return;
    }

    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .then((response) => response)
                .catch(() =>
                    caches.match(event.request).then((cached) => cached || caches.match(OFFLINE_URL))
                )
        );

        return;
    }

    event.respondWith(fetch(event.request));
});
JS;

        return response($js, 200, [
            'Content-Type' => 'application/javascript',
            'Service-Worker-Allowed' => '/',
            'Cache-Control' => 'no-cache',
        ]);
    }
}
