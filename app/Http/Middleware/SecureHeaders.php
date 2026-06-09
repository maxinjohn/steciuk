<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '0');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->remove('X-Powered-By');
        $response->headers->set('X-DNS-Prefetch-Control', 'off');

        if ($request->secure() || config('security.force_https')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=63072000; includeSubDomains; preload');
        }

        if (config('security.csp_enabled') && ! \App\Support\AdminPanelConfig::isAdminRequest($request)) {
            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.youtube.com https://www.google.com",
                "style-src 'self' 'unsafe-inline'",
                "img-src 'self' data: blob: https:",
                "font-src 'self' data:",
                "connect-src 'self' https: wss:",
                "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com https://www.google.com https://maps.google.com",
                "media-src 'self' https: blob:",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'self'",
                "upgrade-insecure-requests",
            ]);
            $response->headers->set('Content-Security-Policy', $csp);
        }

        if (\App\Support\AdminPanelConfig::isAdminRequest($request) && config('security.csp_enabled')) {
            $response->headers->set('Content-Security-Policy', implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
                "style-src 'self' 'unsafe-inline'",
                "img-src 'self' data: blob: https:",
                "font-src 'self' data:",
                "connect-src 'self' https: wss:",
                "frame-src 'self'",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'none'",
            ]));
        }

        if (\App\Support\AdminPanelConfig::isAdminRequest($request)) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
        } elseif ($request->is('sitemap.xml', 'robots.txt', 'manifest.webmanifest', 'sw.js')) {
            $response->headers->set('Cache-Control', 'public, max-age=3600, stale-while-revalidate=86400');
        } elseif ($request->isMethod('GET') && ! $request->is('livewire/*')) {
            $response->headers->set('Cache-Control', 'public, max-age=300, stale-while-revalidate=60');
        }

        return $response;
    }
}
