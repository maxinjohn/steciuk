<?php

namespace App\Http\Middleware;

use App\Enums\AccountStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApprovedMemberAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (! $user->isActive() || ! $user->familyIsActive()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['email' => $user->memberAccessBlockReason() ?? 'Your parish account is not active. Please contact the parish office for help.']);
        }

        if (! $user->isMember()) {
            return $next($request);
        }

        $status = $user->accountStatus();

        if ($status === AccountStatus::Approved) {
            return $next($request);
        }

        if ($request->routeIs('registration.pending', 'logout')) {
            return $next($request);
        }

        if ($status === AccountStatus::Pending) {
            return redirect()->route('registration.pending');
        }

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->withErrors(['email' => 'Your parish account is not active. Please contact the parish office for help.']);
    }
}
