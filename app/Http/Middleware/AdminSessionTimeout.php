<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSessionTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        if (\App\Support\AdminPanelConfig::isAdminRequest($request) && auth()->check()) {
            $lastActivity = session('admin_last_activity');
            $timeout = config('security.session_lifetime_admin', 120) * 60;

            if ($lastActivity && (time() - $lastActivity) > $timeout) {
                $userId = auth()->id();

                auth()->logout();
                session()->invalidate();
                session()->regenerateToken();

                \App\Services\SecurityLogger::warning('admin_session_expired', $userId, [
                    'ip' => $request->ip(),
                ]);

                return redirect()->route('filament.admin.auth.login')
                    ->with('status', 'Your session expired. Please sign in again.');
            }

            session(['admin_last_activity' => time()]);
        }

        return $next($request);
    }
}
