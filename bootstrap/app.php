<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        $middleware->prepend(\App\Http\Middleware\ForceHttps::class);
        $middleware->prepend(\App\Http\Middleware\BlockSuspiciousRequests::class);
        $middleware->append(\App\Http\Middleware\SecureHeaders::class);
        $middleware->append(\App\Http\Middleware\ThrottlePublicForms::class);

        $middleware->encryptCookies(except: [
            // Livewire needs some cookies readable
        ]);

        $middleware->validateCsrfTokens(except: [
            // No exceptions — all forms CSRF protected
        ]);

        $trustedProxies = env('TRUSTED_PROXIES');

        $middleware->trustProxies(
            at: $trustedProxies ? array_map('trim', explode(',', (string) $trustedProxies)) : null,
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->dontFlash([
            'password',
            'password_confirmation',
            'current_password',
            'token',
            '_token',
        ]);

        $exceptions->context(fn () => [
            'request_id' => (string) str()->uuid(),
        ]);

        $exceptions->render(function (\Throwable $e, Request $request) {
            if (config('security.expose_exception_details')) {
                return null;
            }

            if ($e instanceof \Illuminate\Validation\ValidationException
                || $e instanceof \Illuminate\Auth\AuthenticationException
                || $e instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
                return null;
            }

            $status = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
                ? $e->getStatusCode()
                : 500;

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => match ($status) {
                        404 => 'Not found.',
                        403 => 'Forbidden.',
                        419 => 'Page expired.',
                        429 => 'Too many requests.',
                        503 => 'Service unavailable.',
                        default => 'Server error.',
                    },
                ], $status);
            }

            $view = match ($status) {
                404 => 'errors.404',
                403 => 'errors.403',
                419 => 'errors.419',
                429 => 'errors.429',
                503 => 'errors.503',
                default => 'errors.500',
            };

            return response()->view($view, ['status' => $status], $status);
        });
    })->create();
