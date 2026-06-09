<?php

namespace App\Http\Middleware;

use App\Services\MaintenanceModeService;
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

        return response()->view('errors.503', ['status' => 503], 503);
    }
}
