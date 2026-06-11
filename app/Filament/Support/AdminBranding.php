<?php

namespace App\Filament\Support;

use App\Models\Setting;

class AdminBranding
{
    private static ?string $faviconUrl = null;

    public static function faviconUrl(): string
    {
        return self::$faviconUrl ??= Setting::assetUrl(Setting::get('favicon')) ?? asset('icons/favicon.svg');
    }
}
