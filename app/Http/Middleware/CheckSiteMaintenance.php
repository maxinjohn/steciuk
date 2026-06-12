<?php

namespace App\Http\Middleware;

use App\Services\MaintenanceModeService;
use App\Support\AdminPanelConfig;
use App\Support\ErrorResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSiteMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! MaintenanceModeService::isEnabled()) {
            return $next($request);
        }

        if (MaintenanceModeService::shouldBypass(trim($request->path(), '/'))) {
            return $next($request);
        }

        if ($request->hasHeader('X-Livewire') && AdminPanelConfig::shouldTrackAdminSession($request)) {
            return $next($request);
        }

        if ($request->hasHeader('X-Livewire') || $request->expectsJson()) {
            return ErrorResponse::json(503, reload: true);
        }

        return response()->view('errors.503', ['status' => 503], 503);
    }
}
