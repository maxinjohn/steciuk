<?php

namespace App\Http\Middleware;

use App\Services\SecurityLogger;
use App\Support\AdminPanelConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockSuspiciousRequests
{
    /** @var array<int, string> */
    protected array $patterns = [
        '/(<script|javascript:|onerror=|onload=)/i',
        '/(union\s+select|insert\s+into|drop\s+table|exec\s+xp_)/i',
        '/(\.\.\/|\.\.\\\\|%2e%2e)/i',
        '/(<\?php|<\?=|\beval\s*\(|\bbase64_decode\s*\()/i',
        '/(\/etc\/passwd|\/proc\/self)/i',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('security.block_suspicious_requests')) {
            return $next($request);
        }

        if (AdminPanelConfig::shouldBypassAdminTraffic($request)) {
            return $next($request);
        }

        $segments = [
            $request->getSchemeAndHttpHost(),
            $request->path(),
            $request->getQueryString() ?? '',
        ];

        if ($request->isMethod('POST', 'PUT', 'PATCH', 'DELETE')) {
            $segments[] = json_encode($request->except(['password', 'password_confirmation', '_token']));
        }

        $payload = strtolower(implode('|', array_filter($segments)));

        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $payload)) {
                SecurityLogger::warning('blocked_suspicious_request', null, [
                    'path' => $request->path(),
                ]);

                abort(403, 'Request blocked for security reasons.');
            }
        }

        return $next($request);
    }
}
