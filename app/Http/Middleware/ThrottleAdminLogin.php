<?php

namespace App\Http\Middleware;

use App\Filament\Auth\Login;
use App\Support\AdminPanelConfig;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ThrottleAdminLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        $email = self::emailFromRequest($request);
        $key = self::key($request, $email);
        $maxAttempts = self::maxAttempts();

        if ($request->is(AdminPanelConfig::loginPath()) && RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            session()->flash('admin_login_locked', [
                'seconds' => $seconds,
                'attempts' => $maxAttempts,
                'email' => $email !== '' ? $email : null,
            ]);
        }

        if ($request->is(AdminPanelConfig::loginPath()) && $request->isMethod('POST')) {
            if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
                $seconds = RateLimiter::availableIn($key);

                \App\Services\SecurityLogger::warning('login_rate_limited', null, [
                    'ip' => $request->ip(),
                    'email' => $email !== '' ? $email : null,
                    'retry_in' => $seconds,
                ]);

                throw ValidationException::withMessages([
                    'data.email' => self::lockoutMessage($seconds),
                ]);
            }
        }

        return $next($request);
    }

    public static function key(Request $request, ?string $email = null): string
    {
        $email ??= self::emailFromRequest($request);

        if ($email !== '') {
            return 'admin-login:'.hash('sha256', $email).'|'.$request->ip();
        }

        return 'admin-login:'.$request->ip();
    }

    public static function recordFailure(Request $request, ?string $email = null): void
    {
        $decaySeconds = (int) config('security.login_decay_minutes', 15) * 60;
        $email = $email !== null && $email !== '' ? Login::normalizeEmail($email) : self::emailFromRequest($request);

        if ($email !== '') {
            session(['admin_login_throttle_email' => $email]);
        }

        RateLimiter::hit(self::key($request, $email), $decaySeconds);
    }

    public static function clear(Request $request, ?string $email = null): void
    {
        RateLimiter::clear(self::key($request, $email));
    }

    public static function lockoutMessage(int $seconds): string
    {
        if ($seconds >= 3600) {
            return 'Too many login attempts. Please try again in '.ceil($seconds / 3600).' hour(s).';
        }

        if ($seconds >= 60) {
            return 'Too many login attempts. Please try again in '.ceil($seconds / 60).' minute(s).';
        }

        return 'Too many login attempts. Please try again in '.$seconds.' second(s).';
    }

    public static function isLocked(Request $request, ?string $email = null): bool
    {
        return RateLimiter::tooManyAttempts(
            self::key($request, $email),
            self::maxAttempts(),
        );
    }

    public static function secondsUntilUnlocked(Request $request, ?string $email = null): int
    {
        return RateLimiter::availableIn(self::key($request, $email));
    }

    public static function emailFromRequest(Request $request): string
    {
        $email = $request->input('data.email', $request->input('email', session('admin_login_throttle_email', '')));

        return Login::normalizeEmail($email);
    }

    public static function maxAttempts(): int
    {
        return (int) config('security.max_login_attempts', 5);
    }
}
