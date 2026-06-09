<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthorized.');
        }

        $allowedSlugs = array_map(
            fn (string $role) => UserRole::tryFrom($role)?->value ?? $role,
            $roles,
        );

        if (! in_array($user->roleSlug(), $allowedSlugs, true)) {
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
