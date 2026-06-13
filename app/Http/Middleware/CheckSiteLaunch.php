<?php

namespace App\Http\Middleware;

use App\Services\LaunchModeService;
use App\Support\AdminPanelConfig;
use App\Support\ErrorResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSiteLaunch
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = trim($request->path(), '/');

        if (LaunchModeService::shouldBypass($path)) {
            return $next($request);
        }

        if (AdminPanelConfig::shouldBypassLaunchGate($request)) {
            return $next($request);
        }

        if ($request->boolean('preview') && LaunchModeService::canPreviewSite()) {
            return $next($request);
        }

        $gate = LaunchModeService::activeGateForPath($path);

        if ($gate === null) {
            return $next($request);
        }

        if ($request->hasHeader('X-Livewire') || $request->expectsJson()) {
            return ErrorResponse::json(503, reload: true);
        }

        return response()->view('errors.launch', LaunchModeService::viewData($gate), 200);
    }
}
