<?php

namespace App\Http\Middleware;

use App\Support\AdminPanelConfig;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottlePublicForms
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('POST') && $request->hasHeader('X-Livewire')) {
            if ($this->isAdminLivewireRequest($request)) {
                return $next($request);
            }

            $key = 'livewire-form:'.$request->ip();

            if (RateLimiter::tooManyAttempts($key, 20)) {
                \App\Services\SecurityLogger::warning('livewire_rate_limited', null, [
                    'ip' => $request->ip(),
                ]);

                abort(429, 'Too many requests. Please slow down.');
            }

            RateLimiter::hit($key, 60);
        }

        return $next($request);
    }

    private function isAdminLivewireRequest(Request $request): bool
    {
        if (AdminPanelConfig::isAdminRequest($request)) {
            return true;
        }

        $referer = (string) $request->headers->get('referer', '');

        if ($referer !== '' && str_contains($referer, AdminPanelConfig::url())) {
            return true;
        }

        foreach ((array) $request->input('components', []) as $component) {
            $snapshot = $component['snapshot'] ?? '';

            if (is_string($snapshot) && str_contains($snapshot, 'app.filament')) {
                return true;
            }
        }

        return false;
    }
}
