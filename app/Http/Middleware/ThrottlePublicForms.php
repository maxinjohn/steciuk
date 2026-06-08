<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottlePublicForms
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('POST') && $request->hasHeader('X-Livewire')) {
            $key = 'livewire-form:'.$request->ip();

            if (RateLimiter::tooManyAttempts($key, 20)) {
                abort(429, 'Too many requests. Please slow down.');
            }

            RateLimiter::hit($key, 60);
        }

        return $next($request);
    }
}
