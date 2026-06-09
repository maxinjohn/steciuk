<?php

namespace App\Support;

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

    public static function url(string $suffix = ''): string
    {
        $suffix = ltrim($suffix, '/');

        return $suffix === '' ? '/'.static::path() : '/'.static::path().'/'.$suffix;
    }
}
