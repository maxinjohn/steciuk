<?php

namespace App\Support;

class SitePaths
{
    /**
     * Resolve a path from .env — absolute paths pass through, relative paths
     * are resolved from the Laravel project root (not PHP's cwd).
     */
    public static function resolve(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $path = trim($path);

        if ($path === ':memory:') {
            return $path;
        }

        if (self::isAbsolute($path)) {
            return $path;
        }

        return base_path($path);
    }

    public static function isAbsolute(string $path): bool
    {
        if (str_starts_with($path, '/')) {
            return true;
        }

        return (bool) preg_match('/^[A-Za-z]:[\\\\\\/]/', $path);
    }
}
