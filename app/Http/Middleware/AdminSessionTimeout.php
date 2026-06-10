<?php

namespace App\Http\Middleware;

use App\Support\AdminPanelConfig;
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
            $timeout = config('security.session_lifetime_admin', 120) * 60;

            if ($lastActivity && (time() - $lastActivity) > $timeout) {
                return $this->expireSession($request);
            }

            session(['admin_last_activity' => time()]);
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
        ]);

        if ($request->hasHeader('X-Livewire') || $request->expectsJson()) {
            return ErrorResponse::json(419, reload: true);
        }

        return redirect()
            ->to(AdminPanelConfig::url('login').'?expired=1')
            ->with('status', 'Your session expired. Please sign in again.');
    }
}
