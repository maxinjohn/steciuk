<?php

namespace App\Support;

use Illuminate\Http\Request;

class MemberPortalRoutes
{
    /** @return list<string> */
    public static function routeNames(): array
    {
        return [
            'login',
            'register',
            'password.request',
            'password.reset',
            'registration.pending',
            'account',
            'account.giving.export',
            'logout',
        ];
    }

    public static function isPortalRequest(Request $request): bool
    {
        if ($request->routeIs(...self::routeNames())) {
            return true;
        }

        return $request->is(
            'login',
            'register',
            'registration/pending',
            'forgot-password',
            'reset-password/*',
            'account',
            'account/*',
        );
    }

    /** @return list<string> Browser path prefixes that must bypass the service worker. */
    public static function serviceWorkerBypassPrefixes(): array
    {
        return [
            '/login',
            '/register',
            '/registration',
            '/forgot-password',
            '/reset-password',
            '/account',
            '/logout',
        ];
    }
}
