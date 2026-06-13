<?php

use App\Http\Middleware\AdminSessionTimeout;
use App\Http\Middleware\BlockSuspiciousRequests;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckSiteLaunch;
use App\Http\Middleware\CheckSiteMaintenance;
use App\Http\Middleware\EnsureApprovedMemberAccount;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\SecureHeaders;
use App\Http\Middleware\ShareSiteLayoutData;
use App\Http\Middleware\ThrottlePublicForms;
use App\Support\AdminPanelConfig;
use App\Support\ErrorResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckRole::class,
            'member.approved' => EnsureApprovedMemberAccount::class,
        ]);

        $middleware->prepend(ForceHttps::class);
        $middleware->prepend(BlockSuspiciousRequests::class);
        $middleware->append(SecureHeaders::class);
        $middleware->append(ThrottlePublicForms::class);
        $middleware->appendToGroup('web', CheckSiteMaintenance::class);
        $middleware->appendToGroup('web', CheckSiteLaunch::class);
        $middleware->appendToGroup('web', AdminSessionTimeout::class);
        $middleware->appendToGroup('web', ShareSiteLayoutData::class);

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

        $middleware->redirectGuestsTo(function (Request $request): string {
            if (AdminPanelConfig::isAdminRequest($request)) {
                return AdminPanelConfig::url('login');
            }

            return route('login');
        });

        $middleware->redirectUsersTo(fn () => route('account'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*')
                || $request->expectsJson()
                || $request->hasHeader('X-Livewire'),
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

        $exceptions->render(function (Throwable $e, Request $request) {
            if (config('security.expose_exception_details')) {
                return null;
            }

            if ($e instanceof ValidationException
                || $e instanceof HttpResponseException) {
                return null;
            }

            if ($e instanceof AuthenticationException) {
                if ($request->expectsJson() || $request->hasHeader('X-Livewire')) {
                    return ErrorResponse::json(401);
                }

                if ($request->is(AdminPanelConfig::pathPattern())) {
                    return null;
                }

                return redirect()->guest($e->redirectTo($request) ?? route('login'));
            }

            if ($e instanceof TokenMismatchException) {
                $reload = $request->hasHeader('X-Livewire')
                    || AdminPanelConfig::isAdminRequest($request);

                if ($request->expectsJson() || $request->hasHeader('X-Livewire')) {
                    return ErrorResponse::json(419, reload: $reload);
                }
            }

            $status = $e instanceof HttpExceptionInterface
                ? $e->getStatusCode()
                : 500;

            if ($request->expectsJson() || $request->hasHeader('X-Livewire')) {
                $reload = AdminPanelConfig::shouldBypassAdminTraffic($request)
                    && in_array($status, [401, 403, 419, 429, 500, 503], true);

                return ErrorResponse::json($status, reload: $reload);
            }

            $data = [];

            if ($status === 429 && $e instanceof TooManyRequestsHttpException) {
                $data['retryAfter'] = $e->getHeaders()['Retry-After'] ?? null;
            }

            return ErrorResponse::view($status, $request, $data);
        });
    })->create();
