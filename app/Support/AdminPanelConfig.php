<?php

namespace App\Support;

use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class AdminPanelConfig
{
    public static function path(): string
    {
        $path = (string) config('site.admin.path', 'admin');

        return $path !== '' ? $path : 'admin';
    }

    public static function pathPattern(): string
    {
        return static::path().'*';
    }

    public static function loginPath(): string
    {
        return static::path().'/login';
    }

    public static function name(): string
    {
        return (string) config('site.admin.name', 'STECI UK Parish Admin');
    }

    public static function shortName(): string
    {
        return (string) config('site.admin.short_name', 'Parish Admin');
    }

    public static function isAdminRequest(Request $request): bool
    {
        return $request->is(static::pathPattern());
    }

    public static function refererIsAdminPanel(?string $referer): bool
    {
        if ($referer === null || $referer === '') {
            return false;
        }

        $path = trim((string) (parse_url($referer, PHP_URL_PATH) ?? ''), '/');
        $adminPath = static::path();

        return $path === $adminPath || str_starts_with($path, $adminPath.'/');
    }

    public static function originIsAdminPanel(?string $origin): bool
    {
        if ($origin === null || $origin === '') {
            return false;
        }

        return static::refererIsAdminPanel($origin);
    }

    public static function isFilamentLivewireName(string $name): bool
    {
        return str_starts_with($name, 'app.filament.')
            || str_starts_with($name, 'filament.');
    }

    public static function isAdminLivewireRequest(Request $request): bool
    {
        if (! $request->is('livewire/*')) {
            return false;
        }

        foreach ((array) $request->input('components', []) as $component) {
            $snapshotRaw = $component['snapshot'] ?? '';

            if (is_string($snapshotRaw) && (
                str_contains($snapshotRaw, 'app.filament.')
                || str_contains($snapshotRaw, 'filament.livewire.')
                || str_contains($snapshotRaw, 'filament.auth.')
            )) {
                return true;
            }

            if (! is_string($snapshotRaw) || $snapshotRaw === '') {
                continue;
            }

            $snapshot = json_decode($snapshotRaw, true);

            if (! is_array($snapshot)) {
                continue;
            }

            $name = (string) ($snapshot['memo']['name'] ?? '');

            if ($name !== '' && static::isFilamentLivewireName($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Keep admin panel routes and admin Livewire working during maintenance.
     * Public pages stay blocked for everyone, including signed-in parish admins.
     */
    public static function shouldBypassMaintenanceTraffic(Request $request): bool
    {
        if (static::shouldBypassLaunchGate($request)) {
            return true;
        }

        if ($request->is('livewire/*') && (
            static::refererIsAdminPanel($request->headers->get('referer'))
            || static::originIsAdminPanel($request->headers->get('origin'))
        )) {
            return true;
        }

        return false;
    }

    /**
     * Never block parish admin sign-in, admin pages, or Filament Livewire traffic.
     */
    public static function shouldBypassAdminTraffic(Request $request): bool
    {
        if (static::shouldBypassLaunchGate($request)) {
            return true;
        }

        if (static::refererIsAdminPanel($request->headers->get('referer'))) {
            return true;
        }

        if (static::originIsAdminPanel($request->headers->get('origin'))) {
            return true;
        }

        $user = auth()->user();

        if ($user instanceof User && app(PermissionService::class)->canAccessAdmin($user)) {
            return true;
        }

        return false;
    }

    /**
     * Launch countdown applies to everyone on public URLs, including signed-in admins.
     * Only admin panel routes and Filament Livewire skip the gate here.
     */
    public static function shouldBypassLaunchGate(Request $request): bool
    {
        if (static::isAdminRequest($request)) {
            return true;
        }

        return static::isAdminLivewireRequest($request);
    }

    /** @deprecated Use shouldBypassAdminTraffic() */
    public static function shouldBypassSiteGates(Request $request): bool
    {
        return static::shouldBypassAdminTraffic($request);
    }

    public static function isAdminAuthContext(Request $request): bool
    {
        if (static::isAdminRequest($request)) {
            return true;
        }

        if (static::isAdminLivewireRequest($request)) {
            return true;
        }

        return static::refererIsAdminPanel($request->headers->get('referer'))
            || static::originIsAdminPanel($request->headers->get('origin'));
    }

    public static function shouldTrackAdminSession(Request $request): bool
    {
        if (static::isAdminRequest($request)) {
            return true;
        }

        if (! $request->is('livewire/*')) {
            return false;
        }

        if (static::refererIsAdminPanel($request->headers->get('referer'))
            || static::originIsAdminPanel($request->headers->get('origin'))
            || static::isAdminLivewireRequest($request)) {
            return true;
        }

        $user = auth()->user();

        return $user instanceof User && app(PermissionService::class)->canAccessAdmin($user);
    }

    public static function url(string $suffix = ''): string
    {
        $suffix = ltrim($suffix, '/');

        return $suffix === '' ? '/'.static::path() : '/'.static::path().'/'.$suffix;
    }
}
