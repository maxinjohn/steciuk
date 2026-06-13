<?php

namespace App\Support;

use Illuminate\Support\Str;

final class SitePathGate
{
    public const SCOPE_SITE = 'site';

    public const SCOPE_PATH = 'path';

    public const MATCH_EXACT = 'exact';

    public const MATCH_PREFIX = 'prefix';

    public static function newId(string $prefix = 'sg'): string
    {
        return $prefix.'_'.Str::lower(Str::random(12));
    }

    public static function normalizePath(?string $path): string
    {
        return trim((string) $path, '/');
    }

    /**
     * @param  array<string, mixed>  $gate
     */
    public static function matches(array $gate, string $requestPath): bool
    {
        $scope = (string) ($gate['scope'] ?? self::SCOPE_SITE);

        if ($scope === self::SCOPE_SITE) {
            return true;
        }

        $target = self::normalizePath($gate['target_path'] ?? '');

        if ($target === '') {
            return false;
        }

        $match = (string) ($gate['path_match'] ?? self::MATCH_PREFIX);

        return self::pathMatches(self::normalizePath($requestPath), $target, $match);
    }

    public static function pathMatches(string $requestPath, string $targetPath, string $match): bool
    {
        $requestPath = self::normalizePath($requestPath);
        $targetPath = self::normalizePath($targetPath);

        if ($targetPath === '') {
            return false;
        }

        if ($match === self::MATCH_EXACT) {
            return $requestPath === $targetPath;
        }

        return $requestPath === $targetPath
            || str_starts_with($requestPath, $targetPath.'/');
    }

    /**
     * Prefer path-scoped gates over site-wide; longer paths win.
     *
     * @param  array<int, array<string, mixed>>  $gates
     */
    public static function sortBySpecificity(array $gates): array
    {
        usort($gates, function (array $a, array $b): int {
            $aSite = ($a['scope'] ?? self::SCOPE_SITE) === self::SCOPE_SITE;
            $bSite = ($b['scope'] ?? self::SCOPE_SITE) === self::SCOPE_SITE;

            if ($aSite !== $bSite) {
                return $aSite <=> $bSite;
            }

            return strlen(self::normalizePath($b['target_path'] ?? ''))
                <=> strlen(self::normalizePath($a['target_path'] ?? ''));
        });

        return $gates;
    }

    /**
     * Admin-facing label for status lists and notifications.
     *
     * @param  array<string, mixed>  $gate
     */
    public static function summaryLabel(array $gate): string
    {
        $label = trim((string) ($gate['label'] ?? 'Rule'));

        if (($gate['scope'] ?? self::SCOPE_SITE) === self::SCOPE_PATH) {
            $path = self::normalizePath($gate['target_path'] ?? '');

            return $path !== '' ? "{$label} — /{$path}" : "{$label} — path not set";
        }

        return "{$label} — entire public site";
    }

    /**
     * Compact label for admin repeater rows.
     *
     * @param  array<string, mixed>  $gate
     */
    public static function adminItemLabel(array $gate, string $fallback = 'Rule'): string
    {
        $prefix = ($gate['enabled'] ?? false) ? '● ' : '○ ';
        $name = trim((string) ($gate['label'] ?? '')) ?: $fallback;

        if (($gate['scope'] ?? self::SCOPE_SITE) === self::SCOPE_PATH) {
            $path = self::normalizePath($gate['target_path'] ?? '');

            return $path !== ''
                ? "{$prefix}{$name} · /{$path}"
                : "{$prefix}{$name} · path not set";
        }

        return "{$prefix}{$name} · entire public site";
    }
}
