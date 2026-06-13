<?php

namespace App\Services;

use App\Models\Setting;
use App\Support\AdminPanelConfig;

class MaintenanceModeService
{
    public static function isEnabled(): bool
    {
        return Setting::get('maintenance_mode_enabled', '0') === '1';
    }

    public static function enable(): void
    {
        Setting::set('maintenance_mode_enabled', '1', 'general');
    }

    public static function disable(): void
    {
        Setting::set('maintenance_mode_enabled', '0', 'general');
    }

    public static function shouldBypass(string $path): bool
    {
        $adminPath = trim(AdminPanelConfig::path(), '/');

        if (in_array($path, [
            'up',
            'sitemap.xml',
            'robots.txt',
            'login',
            'register',
            'registration/pending',
            'forgot-password',
        ], true)) {
            return true;
        }

        if (str_starts_with($path, 'reset-password/')) {
            return true;
        }

        if ($path === $adminPath || str_starts_with($path, $adminPath.'/')) {
            return true;
        }

        return false;
    }
}
