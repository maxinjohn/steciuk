<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SiteBrandingAssets
{
    public const BUNDLED_LOGO_PUBLIC = 'images/branding/steci-parish-logo.png';

    public const BUNDLED_MARK_PUBLIC = 'images/branding/steci-parish-logo-mark.png';

    public const UPLOAD_LOGO_RELATIVE = 'settings/branding/steci-parish-logo.png';

    public const UPLOAD_MARK_RELATIVE = 'settings/branding/steci-parish-logo-mark.png';

    /**
     * @var list<string>
     */
    private const LEGACY_LOGO_VALUES = [
        '',
        '/images/steci-mark.svg',
        'images/steci-mark.svg',
    ];

    public static function bundledLogoPath(): string
    {
        return public_path(self::BUNDLED_LOGO_PUBLIC);
    }

    public static function bundledMarkPath(): string
    {
        return public_path(self::BUNDLED_MARK_PUBLIC);
    }

    public static function bundledLogoExists(): bool
    {
        return is_file(self::bundledLogoPath());
    }

    public static function bundledMarkExists(): bool
    {
        return is_file(self::bundledMarkPath());
    }

    /**
     * Copy git-tracked branding assets into the public uploads disk when missing or outdated.
     */
    public static function ensureParishLogoInUploads(): string
    {
        SitePaths::ensureCommonUploadDirectories();

        $logoSynced = self::syncBundledAsset(self::bundledLogoPath(), self::UPLOAD_LOGO_RELATIVE);
        self::syncBundledAsset(self::bundledMarkPath(), self::UPLOAD_MARK_RELATIVE);

        if ($logoSynced) {
            SiteLogoProcessor::process(self::UPLOAD_LOGO_RELATIVE);
        }

        $disk = Storage::disk('public');

        if ($disk->exists(self::UPLOAD_LOGO_RELATIVE)) {
            return self::UPLOAD_LOGO_RELATIVE;
        }

        if (self::bundledLogoExists()) {
            return self::UPLOAD_LOGO_RELATIVE;
        }

        return '/'.self::BUNDLED_LOGO_PUBLIC;
    }

    public static function headerMarkUrl(?string $logoPath = null): string
    {
        self::ensureParishLogoInUploads();

        $path = ltrim((string) ($logoPath ?? Setting::get('logo', '')), '/');

        if ($path !== '') {
            $processed = SiteLogoProcessor::headerMarkUrl($path);

            if ($processed !== null) {
                return $processed;
            }
        }

        $disk = Storage::disk('public');

        if ($disk->exists(self::UPLOAD_MARK_RELATIVE)) {
            return Setting::assetUrl(self::UPLOAD_MARK_RELATIVE)
                ?? '/storage/'.self::UPLOAD_MARK_RELATIVE;
        }

        if (self::bundledMarkExists()) {
            return '/'.self::BUNDLED_MARK_PUBLIC;
        }

        return self::fullLogoUrl($logoPath);
    }

    public static function processUploadedLogo(?string $logoPath): void
    {
        if ($logoPath === null || trim($logoPath) === '') {
            return;
        }

        SiteLogoProcessor::process(ltrim($logoPath, '/'));
    }

    public static function usesHeaderLockup(?string $logoPath = null): bool
    {
        $path = $logoPath ?? Setting::get('logo');

        return SiteLogoProcessor::usesHeaderLockup($path);
    }

    public static function fullLogoUrl(?string $logoPath = null): string
    {
        $path = $logoPath ?? Setting::get('logo');

        if ($path) {
            return Setting::assetUrl($path) ?? '/'.ltrim((string) $path, '/');
        }

        if (self::bundledLogoExists()) {
            return '/'.self::BUNDLED_LOGO_PUBLIC;
        }

        return '/images/steci-mark.svg';
    }

    private static function syncBundledAsset(string $bundledPath, string $uploadRelative): bool
    {
        if (! is_file($bundledPath)) {
            return false;
        }

        $disk = Storage::disk('public');

        if ($disk->exists($uploadRelative) && filemtime($bundledPath) <= $disk->lastModified($uploadRelative)) {
            return false;
        }

        $disk->put($uploadRelative, (string) file_get_contents($bundledPath));

        return true;
    }

    public static function syncDefaultLogoSetting(bool $forceLegacy = true): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $uploadPath = self::ensureParishLogoInUploads();
        $current = trim((string) Setting::get('logo', ''));

        if ($forceLegacy && ! in_array($current, self::LEGACY_LOGO_VALUES, true)) {
            return;
        }

        Setting::set('logo', $uploadPath, 'branding');
    }

    public static function isParishLogo(?string $path): bool
    {
        if ($path === null || trim($path) === '') {
            return false;
        }

        return str_contains($path, 'steci-parish-logo')
            || str_contains($path, 'steci-parish-logo-mark')
            || str_contains($path, 'steci-parish-mark')
            || $path === self::UPLOAD_LOGO_RELATIVE
            || $path === self::UPLOAD_MARK_RELATIVE
            || $path === '/'.self::BUNDLED_LOGO_PUBLIC
            || $path === '/'.self::BUNDLED_MARK_PUBLIC;
    }
}
