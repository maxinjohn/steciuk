<?php

namespace App\Services;

use App\Models\Service;
use App\Models\Setting;
use App\Support\AdminPanelConfig;
use App\Support\SitePathGate;
use Illuminate\Http\Request;

class MaintenanceModeService
{
    public const SERVICE_TIMES_PATH = 'service-times';

    private const STORAGE_KEY = 'maintenance_gates';

    /**
     * @return list<array<string, mixed>>
     */
    public static function gates(): array
    {
        $stored = json_decode(Setting::get(self::STORAGE_KEY, '') ?: '[]', true);

        if (is_array($stored) && $stored !== []) {
            return array_values(array_map(fn (array $gate): array => self::normalizeGate($gate), $stored));
        }

        $legacy = self::legacyGateFromSettings();

        return $legacy !== null ? [$legacy] : [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function enabledGates(): array
    {
        return array_values(array_filter(
            self::gates(),
            fn (array $gate): bool => (bool) ($gate['enabled'] ?? false),
        ));
    }

    public static function isEnabled(): bool
    {
        return self::enabledGates() !== [];
    }

    public static function isActiveForPath(string $path): bool
    {
        return self::activeGateForPath($path) !== null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function activeGateForPath(string $path): ?array
    {
        $path = SitePathGate::normalizePath($path);
        $matches = [];

        foreach (self::enabledGates() as $gate) {
            if (SitePathGate::matches($gate, $path)) {
                $matches[] = $gate;
            }
        }

        if ($matches === []) {
            return null;
        }

        $sorted = SitePathGate::sortBySpecificity($matches);

        return self::normalizeGate($sorted[0]);
    }

    public static function enable(): void
    {
        $gates = self::gates();

        if ($gates === []) {
            $gates[] = self::defaultGate();
        }

        foreach ($gates as &$gate) {
            $gate['enabled'] = true;
        }
        unset($gate);

        self::saveGates($gates);
    }

    public static function disable(): void
    {
        $gates = self::gates();

        foreach ($gates as &$gate) {
            $gate['enabled'] = false;
        }
        unset($gate);

        self::saveGates($gates);
    }

    /**
     * @param  list<array<string, mixed>>  $gates
     */
    public static function saveGates(array $gates): void
    {
        $normalized = array_values(array_map(fn (array $gate): array => self::normalizeGate($gate), $gates));

        Setting::set(self::STORAGE_KEY, json_encode($normalized), 'general');
        self::syncLegacySettings($normalized);
    }

    public static function hasActiveServices(): bool
    {
        return Service::query()->where('status', 'active')->exists();
    }

    public static function activeServiceCount(): int
    {
        return Service::query()->where('status', 'active')->count();
    }

    public static function showServiceTimesCta(?array $gate = null): bool
    {
        if (Setting::get('maintenance_mode_show_service_times', '1') === '0') {
            return false;
        }

        if ($gate !== null && ! (bool) ($gate['show_service_times'] ?? true)) {
            return false;
        }

        return self::serviceTimesUrl() !== null;
    }

    public static function showEmailCta(?array $gate = null): bool
    {
        if (Setting::get('maintenance_mode_show_email', '1') === '0') {
            return false;
        }

        if ($gate !== null && ! (bool) ($gate['show_email'] ?? true)) {
            return false;
        }

        return filled(Setting::get('contact_email'));
    }

    public static function serviceTimesUrl(): ?string
    {
        $custom = trim((string) Setting::get('maintenance_mode_service_times_url'));

        if ($custom !== '') {
            return self::normalizeUrl($custom);
        }

        if (! self::hasActiveServices()) {
            return null;
        }

        return url('/'.self::SERVICE_TIMES_PATH);
    }

    public static function serviceTimesLabel(): string
    {
        return Setting::text('maintenance_mode_service_times_label', 'Service times');
    }

    public static function allowsServiceTimesDuringMaintenance(): bool
    {
        if (! self::isEnabled() || ! self::showServiceTimesCta()) {
            return false;
        }

        $custom = trim((string) Setting::get('maintenance_mode_service_times_url'));

        if ($custom !== '') {
            return self::normalizePath($custom) === self::SERVICE_TIMES_PATH;
        }

        return self::hasActiveServices();
    }

    /**
     * @return list<string>
     */
    public static function chips(): array
    {
        $decoded = json_decode(Setting::get('maintenance_mode_chips', '') ?: '[]', true);

        if (is_array($decoded)) {
            $chips = collect($decoded)
                ->map(fn ($chip): string => is_array($chip) ? trim((string) ($chip['label'] ?? '')) : trim((string) $chip))
                ->filter()
                ->values()
                ->all();

            if ($chips !== []) {
                return $chips;
            }
        }

        return ['Worship continues', 'UK parish', 'Almost ready'];
    }

    /**
     * @return array<string, mixed>
     */
    public static function viewData(?array $gate = null): array
    {
        $gate ??= self::primaryGate();

        return [
            'status' => 503,
            'title' => self::gateText($gate, 'title', Setting::text('maintenance_mode_title', 'We\'ll be right back')),
            'badge' => self::gateText($gate, 'badge', Setting::text('maintenance_mode_badge', 'Site refresh mode')),
            'message' => self::gateText(
                $gate,
                'message',
                Setting::text(
                    'maintenance_mode_message',
                    'We are refreshing the parish site. Check back soon — worship continues across the UK.',
                ),
            ),
            'chips' => self::chips(),
            'showServiceTimes' => self::showServiceTimesCta($gate),
            'serviceTimesUrl' => self::serviceTimesUrl(),
            'serviceTimesLabel' => self::serviceTimesLabel(),
            'showEmail' => self::showEmailCta($gate),
            'contactEmail' => Setting::get('contact_email'),
            'verse' => Setting::text(
                'maintenance_mode_verse',
                'Wait for the Lord; be strong and take heart and wait for the Lord.',
            ),
            'verseRef' => Setting::text('maintenance_mode_verse_ref', 'Psalm 27:14'),
            'siteName' => Setting::get('site_name', config('app.name')),
            'adminUrl' => AdminPanelConfig::url('login'),
            'gateLabel' => $gate !== null ? SitePathGate::summaryLabel($gate) : '',
        ];
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

        if ($path === self::SERVICE_TIMES_PATH && self::allowsServiceTimesDuringMaintenance()) {
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

    public static function shouldBypassRequest(Request $request): bool
    {
        return self::shouldBypass(trim($request->path(), '/'))
            || AdminPanelConfig::shouldBypassAdminTraffic($request);
    }

    public static function adminStatusSummary(): string
    {
        $enabled = self::enabledGates();

        if ($enabled === []) {
            return 'No maintenance rules active. Add one on the Maintenance rules tab and save.';
        }

        return collect($enabled)
            ->map(fn (array $gate): string => SitePathGate::summaryLabel($gate).' — ON')
            ->implode("\n");
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultGate(): array
    {
        return self::normalizeGate([
            'id' => SitePathGate::newId('mg'),
            'enabled' => false,
            'label' => 'Site maintenance',
            'scope' => SitePathGate::SCOPE_SITE,
            'target_path' => '',
            'path_match' => SitePathGate::MATCH_PREFIX,
            'use_global_copy' => true,
            'title' => '',
            'badge' => '',
            'message' => '',
            'show_service_times' => true,
            'show_email' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $gate
     * @return array<string, mixed>
     */
    public static function normalizeGate(array $gate): array
    {
        return [
            'id' => filled($gate['id'] ?? null) ? (string) $gate['id'] : SitePathGate::newId('mg'),
            'enabled' => (bool) ($gate['enabled'] ?? false),
            'label' => trim((string) ($gate['label'] ?? 'Maintenance')),
            'scope' => in_array($gate['scope'] ?? SitePathGate::SCOPE_SITE, [SitePathGate::SCOPE_SITE, SitePathGate::SCOPE_PATH], true)
                ? (string) $gate['scope']
                : SitePathGate::SCOPE_SITE,
            'target_path' => SitePathGate::normalizePath($gate['target_path'] ?? ''),
            'path_match' => in_array(
                $match = (string) ($gate['path_match'] ?? SitePathGate::MATCH_PREFIX),
                [SitePathGate::MATCH_EXACT, SitePathGate::MATCH_PREFIX],
                true,
            )
                ? $match
                : SitePathGate::MATCH_PREFIX,
            'use_global_copy' => ($gate['use_global_copy'] ?? true) !== false && ($gate['use_global_copy'] ?? '1') !== '0',
            'title' => trim((string) ($gate['title'] ?? '')),
            'badge' => trim((string) ($gate['badge'] ?? '')),
            'message' => trim((string) ($gate['message'] ?? '')),
            'show_service_times' => ($gate['show_service_times'] ?? true) !== false && ($gate['show_service_times'] ?? '1') !== '0',
            'show_email' => ($gate['show_email'] ?? true) !== false && ($gate['show_email'] ?? '1') !== '0',
        ];
    }

    /**
     * @param  array<string, mixed>|null  $gate
     */
    private static function gateText(?array $gate, string $key, string $default = ''): string
    {
        if ($gate === null || ($gate['use_global_copy'] ?? true)) {
            return $default;
        }

        $value = trim((string) ($gate[$key] ?? ''));

        return $value !== '' ? $value : $default;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function primaryGate(): ?array
    {
        $enabled = self::enabledGates();

        if ($enabled !== []) {
            return $enabled[0];
        }

        $gates = self::gates();

        return $gates[0] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function legacyGateFromSettings(): ?array
    {
        if (Setting::get('maintenance_mode_enabled', '0') !== '1') {
            return null;
        }

        return self::normalizeGate([
            'id' => SitePathGate::newId('mg'),
            'enabled' => true,
            'label' => 'Site maintenance',
            'scope' => SitePathGate::SCOPE_SITE,
            'target_path' => '',
            'path_match' => SitePathGate::MATCH_PREFIX,
            'use_global_copy' => true,
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $gates
     */
    private static function syncLegacySettings(array $gates): void
    {
        Setting::set(
            'maintenance_mode_enabled',
            collect($gates)->contains(fn (array $gate): bool => (bool) ($gate['enabled'] ?? false)) ? '1' : '0',
            'general',
        );
    }

    private static function normalizeUrl(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return $url;
        }

        if (str_starts_with($url, '/')) {
            return url($url);
        }

        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        return url('/'.ltrim($url, '/'));
    }

    private static function normalizePath(string $url): string
    {
        $url = trim($url);

        if (preg_match('#^https?://#i', $url)) {
            $path = parse_url($url, PHP_URL_PATH);

            return trim((string) $path, '/');
        }

        return trim($url, '/');
    }
}
