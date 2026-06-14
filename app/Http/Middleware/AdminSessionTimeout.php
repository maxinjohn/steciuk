<?php

namespace App\Http\Middleware;

use App\Support\AdminPanelConfig;
use App\Support\AdminSecurityConfig;
use App\Support\ErrorResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSessionTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        if (AdminPanelConfig::shouldTrackAdminSession($request) && auth()->check()) {
            $lastActivity = session('admin_last_activity');
            $timeout = AdminSecurityConfig::sessionLifetimeSeconds();
            $now = time();

            if ($lastActivity !== null && ($now - (int) $lastActivity) > $timeout) {
                return $this->expireSession($request);
            }

            session(['admin_last_activity' => $now]);
        }

        return $next($request);
    }

    private function expireSession(Request $request): Response
    {
        $userId = auth()->id();

        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();

        \App\Services\SecurityLogger::warning('admin_session_expired', $userId, [
            'ip' => $request->ip(),
            'timeout_minutes' => AdminSecurityConfig::sessionLifetimeMinutes(),
        ]);

        if ($request->hasHeader('X-Livewire') || $request->expectsJson()) {
            return ErrorResponse::json(419, reload: true);
        }

        return redirect()
            ->to(AdminPanelConfig::url('login').'?expired=1')
            ->with('status', 'Your session expired after '.AdminSecurityConfig::sessionLifetimeMinutes().' minutes of inactivity. Please sign in again.');
    }
}
