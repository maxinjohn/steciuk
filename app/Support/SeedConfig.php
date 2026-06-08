<?php

namespace App\Support;

class SeedConfig
{
    public const MODE_OFF = 'off';

    public const MODE_BOOTSTRAP = 'bootstrap';

    public const MODE_SYNC = 'sync';

    public static function mode(): string
    {
        return (string) config('site.seed.mode', self::MODE_OFF);
    }

    public static function isActive(): bool
    {
        return in_array(self::mode(), [self::MODE_BOOTSTRAP, self::MODE_SYNC], true);
    }

    public static function isBootstrap(): bool
    {
        return self::mode() === self::MODE_BOOTSTRAP;
    }

    public static function isSync(): bool
    {
        return self::mode() === self::MODE_SYNC;
    }

    public static function shouldOverwriteSettings(): bool
    {
        return self::isBootstrap() || (bool) config('site.seed.overwrite_settings');
    }

    public static function shouldOverwritePasswords(): bool
    {
        return self::isBootstrap() || (bool) config('site.seed.overwrite_user_passwords');
    }

    public static function shouldOverwritePages(): bool
    {
        return self::isBootstrap() || (bool) config('site.seed.overwrite_pages');
    }
}
