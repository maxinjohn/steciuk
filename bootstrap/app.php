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
        $middleware->append(\App\Http\Middleware\CheckSiteMaintenance::class);
        $middleware->append(\App\Http\Middleware\ThrottlePublicForms::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\AdminSessionTimeout::class);

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
                || $e instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
                return null;
            }

            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                if ($request->expectsJson()) {
                    return \App\Support\ErrorResponse::json(401);
                }

                if (! $request->is(\App\Support\AdminPanelConfig::pathPattern())) {
                    return \App\Support\ErrorResponse::view(401, $request);
                }

                return null;
            }

            if ($e instanceof \Illuminate\Session\TokenMismatchException) {
                $reload = $request->hasHeader('X-Livewire')
                    || \App\Support\AdminPanelConfig::isAdminRequest($request);

                if ($request->expectsJson() || $request->hasHeader('X-Livewire')) {
                    return \App\Support\ErrorResponse::json(419, reload: $reload);
                }
            }

            $status = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
                ? $e->getStatusCode()
                : 500;

            if ($request->expectsJson() || $request->hasHeader('X-Livewire')) {
                $reload = $status === 419 && \App\Support\AdminPanelConfig::isAdminRequest($request);

                return \App\Support\ErrorResponse::json($status, reload: $reload);
            }

            $data = [];

            if ($status === 429 && $e instanceof \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException) {
                $data['retryAfter'] = $e->getHeaders()['Retry-After'] ?? null;
            }

            return \App\Support\ErrorResponse::view($status, $request, $data);
        });
    })->create();
