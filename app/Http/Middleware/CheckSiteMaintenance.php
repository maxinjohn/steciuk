<?php

namespace App\Http\Middleware;

use App\Services\MaintenanceModeService;
use App\Support\ErrorResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSiteMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = trim($request->path(), '/');

        if (MaintenanceModeService::shouldBypassRequest($request)) {
            return $next($request);
        }

        if (! MaintenanceModeService::isActiveForPath($path)) {
            return $next($request);
        }

        $gate = MaintenanceModeService::activeGateForPath($path);

        if ($request->hasHeader('X-Livewire') || $request->expectsJson()) {
            return ErrorResponse::json(503, reload: true);
        }

        return response()->view('errors.maintenance', MaintenanceModeService::viewData($gate), 503);
    }
}
