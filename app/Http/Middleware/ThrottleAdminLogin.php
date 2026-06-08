<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ThrottleAdminLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin/login') && $request->isMethod('POST')) {
            $key = 'admin-login:'.$request->ip();
            $maxAttempts = (int) config('security.max_login_attempts', 5);
            $decaySeconds = (int) config('security.login_decay_minutes', 15) * 60;

            if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
                $seconds = RateLimiter::availableIn($key);

                throw ValidationException::withMessages([
                    'email' => 'Too many login attempts. Please try again in '.ceil($seconds / 60).' minute(s).',
                ]);
            }

            RateLimiter::hit($key, $decaySeconds);
        }

        return $next($request);
    }
}
