<?php

namespace App\Http\Middleware;

use App\Support\AdminPanelConfig;
use App\Support\SiteLayoutData;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ShareSiteLayoutData
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! AdminPanelConfig::isAdminRequest($request)) {
            View::share(SiteLayoutData::resolve());
        }

        return $next($request);
    }
}
