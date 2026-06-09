<?php

namespace App\Http\Controllers;

use App\Support\AdminPanelConfig;
use Illuminate\Http\Response;

class ServiceWorkerController extends Controller
{
    public function __invoke(): Response
    {
        $cacheVersion = 'steci-'.substr(md5((string) config('app.key')), 0, 8);
        $offlineUrl = route('offline');
        $adminPath = '/'.AdminPanelConfig::path();

        $js = <<<JS
const CACHE = '{$cacheVersion}';
const OFFLINE_URL = '{$offlineUrl}';

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

    if (url.pathname.startsWith('{$adminPath}') || url.pathname.startsWith('/livewire')) {
        return;
    }

    const isStaticAsset =
        url.pathname.startsWith('/build/') ||
        url.pathname.match(/\\.(css|js|woff2?|png|jpg|jpeg|webp|svg|ico|webmanifest)\$/);

    if (isStaticAsset) {
        event.respondWith(
            caches.open(CACHE).then((cache) =>
                cache.match(event.request).then((cached) =>
                    cached || fetch(event.request).then((response) => {
                        if (response.ok) cache.put(event.request, response.clone());
                        return response;
                    })
                )
            )
        );
        return;
    }

    event.respondWith(
        fetch(event.request).catch(() => caches.match(OFFLINE_URL))
    );
});
JS;

        return response($js, 200, [
            'Content-Type' => 'application/javascript',
            'Service-Worker-Allowed' => '/',
            'Cache-Control' => 'no-cache',
        ]);
    }
}
