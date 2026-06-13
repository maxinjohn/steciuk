<?php

namespace App\Services;

use App\Enums\AdminPermission;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use App\Support\AdminPanelConfig;
use App\Support\FaithContent;
use App\Support\GatePageCopy;
use App\Support\SiteBrandingAssets;
use App\Support\SitePathGate;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class LaunchModeService
{
    public const SCOPE_SITE = SitePathGate::SCOPE_SITE;

    public const SCOPE_PATH = SitePathGate::SCOPE_PATH;

    public const MATCH_EXACT = SitePathGate::MATCH_EXACT;

    public const MATCH_PREFIX = SitePathGate::MATCH_PREFIX;

    /** @deprecated Use STYLE_COUNTDOWN */
    public const STYLE_AUTO = 'countdown';

    /** @deprecated Use STYLE_RIBBON */
    public const STYLE_EVENT = 'ribbon';

    public const STYLE_COUNTDOWN = 'countdown';

    public const STYLE_RIBBON = 'ribbon';

    public const THEME_PARISH = 'parish';

    public const THEME_NEON = 'neon';

    public const THEME_GRAND = 'grand';

    public const THEME_AURORA = 'aurora';

    public const THEME_PARTY = 'party';

    public const THEME_BOLD = 'bold';

    /** @deprecated Use THEME_BOLD */
    public const THEME_GENZ = 'bold';

    public const PHASE_COUNTDOWN = 'countdown';

    public const PHASE_RIBBON = 'ribbon';

    public const PHASE_LIVE = 'live';

    private const STORAGE_KEY = 'launch_gates';

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

    public static function isLaunched(): bool
    {
        foreach (self::gates() as $gate) {
            if (self::gateIsLive($gate)) {
                return true;
            }
        }

        return false;
    }

    public static function isGateActive(string $path): bool
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
            if (self::gateIsLive($gate)) {
                continue;
            }

            if (! SitePathGate::matches($gate, $path)) {
                continue;
            }

            $phase = self::gatePhase($gate);

            if ($phase === self::PHASE_LIVE) {
                self::markGateLaunched((string) $gate['id'], disable: true);

                continue;
            }

            $matches[] = $gate;
        }

        if ($matches === []) {
            return null;
        }

        $sorted = SitePathGate::sortBySpecificity($matches);

        return self::normalizeGate($sorted[0]);
    }

    /**
     * @param  array<string, mixed>  $gate
     */
    public static function gatePhase(array $gate): string
    {
        if (self::gateIsLive($gate)) {
            return self::PHASE_LIVE;
        }

        $countdownAt = self::gateCountdownAt($gate);

        if (self::gateLaunchStyle($gate) === self::STYLE_RIBBON) {
            return self::PHASE_RIBBON;
        }

        if ($countdownAt === null || now()->lt($countdownAt)) {
            return self::PHASE_COUNTDOWN;
        }

        return self::PHASE_LIVE;
    }

    /**
     * @param  array<string, mixed>  $gate
     */
    public static function gateIsLive(array $gate): bool
    {
        return filled($gate['launched_at'] ?? '');
    }

    /**
     * @param  array<string, mixed>  $gate
     */
    public static function gateCountdownAt(array $gate): ?CarbonInterface
    {
        return self::normalizeCountdownInput($gate['countdown_at'] ?? null);
    }

    public static function shouldBypass(string $path): bool
    {
        if (MaintenanceModeService::shouldBypass($path)) {
            return true;
        }

        if (in_array($path, ['launch/cut-ribbon'], true)) {
            return true;
        }

        return false;
    }

    public static function canPreviewSite(?User $user = null): bool
    {
        $user ??= auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->hasFullPanelAccess()
            || $user->hasAdminPermission(AdminPermission::SettingsChurch);
    }

    /**
     * @param  list<array<string, mixed>>  $gates
     */
    public static function saveGates(array $gates): void
    {
        $normalized = array_values(array_map(fn (array $gate): array => self::normalizeGate($gate), $gates));

        Setting::set(self::STORAGE_KEY, json_encode($normalized), 'launch');
        self::syncLegacySettings($normalized);
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

    public static function markLaunched(): void
    {
        $gates = self::gates();

        foreach ($gates as &$gate) {
            if ((bool) ($gate['enabled'] ?? false) && ! self::gateIsLive($gate)) {
                $gate['launched_at'] = now()->toIso8601String();
                $gate['enabled'] = false;
            }
        }
        unset($gate);

        self::saveGates($gates);
    }

    public static function markGateLaunched(string $gateId, bool $disable = true): void
    {
        $gates = self::gates();
        $found = false;

        foreach ($gates as &$gate) {
            if ((string) ($gate['id'] ?? '') !== $gateId) {
                continue;
            }

            $gate['launched_at'] = now()->toIso8601String();

            if ($disable) {
                $gate['enabled'] = false;
            }

            $found = true;
            break;
        }
        unset($gate);

        if ($found) {
            self::saveGates($gates);
        }
    }

    public static function resetLaunch(): void
    {
        $gates = self::gates();

        foreach ($gates as &$gate) {
            $gate['launched_at'] = '';
        }
        unset($gate);

        self::saveGates($gates);
    }

    public static function resetGate(string $gateId): void
    {
        $gates = self::gates();

        foreach ($gates as &$gate) {
            if ((string) ($gate['id'] ?? '') === $gateId) {
                $gate['launched_at'] = '';
                break;
            }
        }
        unset($gate);

        self::saveGates($gates);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function gateById(string $gateId): ?array
    {
        foreach (self::gates() as $gate) {
            if ((string) ($gate['id'] ?? '') === $gateId) {
                return $gate;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function resolveGateForRibbon(?string $gateId, string $path): ?array
    {
        if ($gateId !== null && $gateId !== '') {
            $gate = self::gateById($gateId);

            if ($gate !== null && (bool) ($gate['enabled'] ?? false) && ! self::gateIsLive($gate)) {
                return $gate;
            }
        }

        return self::activeGateForPath($path);
    }

    /**
     * @param  array<string, mixed>  $gate
     */
    public static function gateAllowsAdminRibbon(array $gate): bool
    {
        if (self::gateLaunchStyle($gate) !== self::STYLE_RIBBON) {
            return false;
        }

        if (! (bool) ($gate['allow_admin_ribbon'] ?? true)) {
            return false;
        }

        return self::gatePhase($gate) === self::PHASE_RIBBON;
    }

    /**
     * @param  array<string, mixed>  $gate
     */
    public static function gateAllowsPublicRibbon(array $gate): bool
    {
        return false;
    }

    /**
     * @return array<string, string>
     */
    public static function themeOptions(): array
    {
        return [
            self::THEME_PARISH => 'Parish classic — warm glass and gold',
            self::THEME_NEON => 'Night glow — dark background, bright accents',
            self::THEME_GRAND => 'Grand opening — ribbon red and gold',
            self::THEME_AURORA => 'Aurora — soft colour wash',
            self::THEME_PARTY => 'Celebration — bright colour and movement',
            self::THEME_BOLD => 'Bold contrast — strong layout and typography',
        ];
    }

    /**
     * @param  array<string, mixed>  $gate
     */
    public static function gateLaunchStyle(array $gate): string
    {
        return self::normalizeLaunchStyle($gate['launch_style'] ?? self::STYLE_COUNTDOWN);
    }

    public static function normalizeLaunchStyle(mixed $style): string
    {
        $style = (string) $style;

        return match ($style) {
            self::STYLE_RIBBON, 'event' => self::STYLE_RIBBON,
            default => self::STYLE_COUNTDOWN,
        };
    }

    public static function normalizeTheme(mixed $theme): string
    {
        $theme = match ((string) $theme) {
            'genz' => self::THEME_BOLD,
            default => (string) $theme,
        };

        return array_key_exists($theme, self::themeOptions())
            ? $theme
            : self::THEME_PARISH;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function primaryEnabledGate(): ?array
    {
        return self::enabledGates()[0] ?? null;
    }

    public static function scope(): string
    {
        $gate = self::primaryGate();

        return (string) ($gate['scope'] ?? self::SCOPE_SITE);
    }

    public static function targetPath(): string
    {
        $gate = self::primaryGate();

        return SitePathGate::normalizePath($gate['target_path'] ?? '');
    }

    public static function pathMatch(): string
    {
        $gate = self::primaryGate();

        $match = (string) ($gate['path_match'] ?? self::MATCH_PREFIX);

        return in_array($match, [self::MATCH_EXACT, self::MATCH_PREFIX], true)
            ? $match
            : self::MATCH_PREFIX;
    }

    public static function showCountdown(): bool
    {
        $gate = self::primaryGate();

        return ($gate['show_countdown'] ?? true) !== false
            && ($gate['show_countdown'] ?? '1') !== '0';
    }

    public static function allowRibbonCut(): bool
    {
        $gate = self::primaryGate();

        return (bool) ($gate['allow_admin_ribbon'] ?? true);
    }

    public static function countdownAt(): ?CarbonInterface
    {
        $gate = self::primaryGate();

        return $gate !== null ? self::gateCountdownAt($gate) : null;
    }

    public static function countdownHasPassed(): bool
    {
        $at = self::countdownAt();

        return $at !== null && now()->gte($at);
    }

    public static function countdownIso(): ?string
    {
        return self::countdownAt()?->toIso8601String();
    }

    /**
     * @return array<string, mixed>
     */
    public static function viewData(?array $gate = null): array
    {
        $gate ??= self::primaryGate() ?? self::defaultGate();
        $phase = self::gatePhase($gate);
        $countdownAt = self::gateCountdownAt($gate);
        $launchStyle = self::gateLaunchStyle($gate);
        $theme = self::normalizeTheme($gate['theme'] ?? self::THEME_PARISH);
        $showAdminRibbon = self::canPreviewSite() && self::gateAllowsAdminRibbon($gate);
        $isRibbonLaunch = $launchStyle === self::STYLE_RIBBON;
        $showRibbonCeremony = $isRibbonLaunch && $phase === self::PHASE_RIBBON;
        $splash = self::splashData($gate);
        $comfortVerse = self::gateComfortVerse($gate);

        return [
            'gateId' => (string) ($gate['id'] ?? ''),
            'phase' => $phase,
            'theme' => $theme,
            'title' => self::gateText($gate, 'title', GatePageCopy::LAUNCH_TITLE),
            'subtitle' => self::gateText(
                $gate,
                'subtitle',
                $isRibbonLaunch ? GatePageCopy::LAUNCH_SUBTITLE_RIBBON : GatePageCopy::LAUNCH_SUBTITLE_COUNTDOWN,
            ),
            'message' => self::gateText(
                $gate,
                'message',
                $isRibbonLaunch
                    ? GatePageCopy::LAUNCH_MESSAGE_RIBBON
                    : GatePageCopy::LAUNCH_MESSAGE_COUNTDOWN,
            ),
            'eventName' => self::gateText($gate, 'event_name'),
            'verse' => $comfortVerse['text'],
            'verseRef' => $comfortVerse['ref'],
            'countdownAt' => $countdownAt?->toIso8601String(),
            'showCountdown' => ! $isRibbonLaunch
                && ($gate['show_countdown'] ?? true) !== false
                && ($gate['show_countdown'] ?? '1') !== '0'
                && $phase === self::PHASE_COUNTDOWN
                && $countdownAt !== null,
            'showAdminRibbon' => $showAdminRibbon,
            'showPublicRibbon' => false,
            'showRibbonCeremony' => $showRibbonCeremony,
            'launchStyle' => $launchStyle,
            'scope' => (string) ($gate['scope'] ?? self::SCOPE_SITE),
            'targetPath' => SitePathGate::normalizePath($gate['target_path'] ?? ''),
            'adminUrl' => AdminPanelConfig::url('login'),
            'settingsUrl' => AdminPanelConfig::url('site-launch'),
            'ribbonUrl' => route('launch.cut-ribbon'),
            'splashType' => $splash['type'],
            'splashSiteName' => $splash['siteName'],
            'splashLogoUrl' => $splash['logoUrl'],
            'splashPageTitle' => $splash['pageTitle'],
            'splashKicker' => $splash['kicker'],
            'launchUrl' => $splash['launchUrl'],
        ];
    }

    /**
     * @param  array<string, mixed>  $gate
     * @return array{type: string, siteName: string, logoUrl: string, pageTitle: string, kicker: string, launchUrl: string}
     */
    public static function splashData(array $gate): array
    {
        $siteName = Setting::text('site_name', (string) config('site.name', 'STECI UK Parish'));
        $logoUrl = SiteBrandingAssets::fullLogoUrl(Setting::get('logo'));
        $scope = (string) ($gate['scope'] ?? self::SCOPE_SITE);

        if ($scope === self::SCOPE_PATH) {
            $path = SitePathGate::normalizePath($gate['target_path'] ?? '');

            return [
                'type' => 'page',
                'siteName' => $siteName,
                'logoUrl' => $logoUrl,
                'pageTitle' => self::resolvePageTitleForPath($path),
                'kicker' => 'Now live',
                'launchUrl' => $path !== '' ? url('/'.$path) : url('/'),
            ];
        }

        return [
            'type' => 'site',
            'siteName' => $siteName,
            'logoUrl' => $logoUrl,
            'pageTitle' => $siteName,
            'kicker' => 'Welcome',
            'launchUrl' => url('/'),
        ];
    }

    public static function launchUrl(?array $gate = null): string
    {
        return self::splashData($gate ?? self::primaryGate() ?? self::defaultGate())['launchUrl'];
    }

    public static function resolvePageTitleForPath(string $path): string
    {
        $path = SitePathGate::normalizePath($path);

        if ($path === '') {
            return 'Page launch';
        }

        try {
            $title = Page::query()->where('slug', $path)->value('title');

            if (is_string($title) && trim($title) !== '') {
                return trim($title);
            }
        } catch (\Throwable) {
            // Fall back to a readable slug label when pages are unavailable.
        }

        return str($path)->replace(['-', '/'], ' ')->title()->toString();
    }

    public static function previewUrl(?array $gate = null): string
    {
        return self::launchUrl($gate).'?'.http_build_query(['preview' => 1]);
    }

    public static function normalizePath(?string $path): string
    {
        return SitePathGate::normalizePath($path);
    }

    public static function pathMatches(string $requestPath, string $targetPath, string $match): bool
    {
        return SitePathGate::pathMatches($requestPath, $targetPath, $match);
    }

    public static function normalizeCountdownInput(mixed $value): ?CarbonInterface
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return Carbon::instance($value);
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function adminStatusSummary(): string
    {
        $enabled = self::enabledGates();

        if ($enabled === []) {
            return 'No active countdowns. Add one on the Countdowns tab and save.';
        }

        $lines = [];

        foreach ($enabled as $gate) {
            $phase = self::gatePhase($gate);
            $status = match ($phase) {
                self::PHASE_RIBBON => 'Ribbon launch',
                self::PHASE_COUNTDOWN => 'Counting down',
                default => 'Live',
            };

            $style = self::gateLaunchStyle($gate);
            $timing = '';

            if ($style === self::STYLE_COUNTDOWN) {
                $at = self::gateCountdownAt($gate);
                $timing = $at !== null ? ' · ends '.$at->format('j M Y, H:i') : '';
            }

            $lines[] = SitePathGate::summaryLabel($gate).' — '.$status.$timing;
        }

        return implode("\n", $lines);
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultGate(): array
    {
        return self::normalizeGate([
            'id' => SitePathGate::newId('lg'),
            'enabled' => false,
            'label' => 'New launch rule',
            'scope' => self::SCOPE_SITE,
            'target_path' => '',
            'path_match' => self::MATCH_PREFIX,
            'launch_style' => self::STYLE_COUNTDOWN,
            'theme' => self::THEME_PARISH,
            'countdown_at' => '',
            'launched_at' => '',
            'show_countdown' => true,
            'allow_admin_ribbon' => true,
            'allow_public_ribbon' => false,
            'event_name' => '',
            'subtitle' => GatePageCopy::LAUNCH_SUBTITLE_COUNTDOWN,
            'title' => GatePageCopy::LAUNCH_TITLE,
            'message' => GatePageCopy::LAUNCH_MESSAGE_COUNTDOWN,
            'verse' => 'Wait for the Lord; be strong and take heart and wait for the Lord.',
            'verse_ref' => 'Psalm 27:14',
        ]);
    }

    /**
     * @param  array<string, mixed>  $gate
     * @return array<string, mixed>
     */
    public static function normalizeGate(array $gate): array
    {
        $countdown = self::normalizeCountdownInput($gate['countdown_at'] ?? null);

        return [
            'id' => filled($gate['id'] ?? null) ? (string) $gate['id'] : SitePathGate::newId('lg'),
            'enabled' => (bool) ($gate['enabled'] ?? false),
            'label' => trim((string) ($gate['label'] ?? 'Countdown')),
            'scope' => in_array($gate['scope'] ?? self::SCOPE_SITE, [self::SCOPE_SITE, self::SCOPE_PATH], true)
                ? (string) $gate['scope']
                : self::SCOPE_SITE,
            'target_path' => SitePathGate::normalizePath($gate['target_path'] ?? ''),
            'path_match' => in_array(
                $match = (string) ($gate['path_match'] ?? self::MATCH_PREFIX),
                [self::MATCH_EXACT, self::MATCH_PREFIX],
                true,
            )
                ? $match
                : self::MATCH_PREFIX,
            'launch_style' => self::normalizeLaunchStyle($gate['launch_style'] ?? self::STYLE_COUNTDOWN),
            'theme' => self::normalizeTheme($gate['theme'] ?? self::THEME_PARISH),
            'countdown_at' => $countdown?->toIso8601String() ?? '',
            'launched_at' => trim((string) ($gate['launched_at'] ?? '')),
            'show_countdown' => ($gate['show_countdown'] ?? true) !== false && ($gate['show_countdown'] ?? '1') !== '0',
            'allow_admin_ribbon' => ($gate['allow_admin_ribbon'] ?? true) !== false && ($gate['allow_admin_ribbon'] ?? '1') !== '0',
            'allow_public_ribbon' => false,
            'event_name' => trim((string) ($gate['event_name'] ?? '')),
            'subtitle' => trim((string) ($gate['subtitle'] ?? GatePageCopy::LAUNCH_SUBTITLE_COUNTDOWN)),
            'title' => trim((string) ($gate['title'] ?? '')),
            'message' => trim((string) ($gate['message'] ?? '')),
            'verse' => trim((string) ($gate['verse'] ?? '')),
            'verse_ref' => trim((string) ($gate['verse_ref'] ?? '')),
        ];
    }

    /**
     * @param  array<string, mixed>  $gate
     * @return array{text: string, ref: string}
     */
    private static function gateComfortVerse(array $gate): array
    {
        $text = self::gateText($gate, 'verse', '');
        $ref = self::gateText($gate, 'verse_ref', '');

        if ($text !== '') {
            return ['text' => $text, 'ref' => $ref];
        }

        return FaithContent::randomVerse('launch');
    }

    /**
     * @param  array<string, mixed>  $gate
     */
    private static function gateText(array $gate, string $key, string $default = ''): string
    {
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
        if (Setting::get('launch_mode_enabled', '0') !== '1'
            && blank(Setting::get('launch_countdown_at'))
            && blank(Setting::get('launch_mode_target_path'))) {
            return null;
        }

        return self::normalizeGate([
            'id' => SitePathGate::newId('lg'),
            'enabled' => Setting::get('launch_mode_enabled', '0') === '1',
            'label' => Setting::text('launch_mode_event_name') ?: 'Site launch',
            'scope' => Setting::get('launch_mode_scope', self::SCOPE_SITE),
            'target_path' => Setting::get('launch_mode_target_path'),
            'path_match' => Setting::get('launch_mode_path_match', self::MATCH_PREFIX),
            'launch_style' => self::STYLE_COUNTDOWN,
            'theme' => self::THEME_PARISH,
            'countdown_at' => Setting::get('launch_countdown_at'),
            'launched_at' => Setting::get('launch_mode_launched_at'),
            'show_countdown' => Setting::get('launch_mode_show_countdown', '1') !== '0',
            'allow_admin_ribbon' => Setting::get('launch_mode_allow_ribbon_cut', '1') !== '0',
            'allow_public_ribbon' => false,
            'event_name' => Setting::get('launch_mode_event_name'),
            'subtitle' => Setting::get('launch_mode_subtitle', GatePageCopy::LAUNCH_SUBTITLE_COUNTDOWN),
            'title' => Setting::get('launch_mode_title'),
            'message' => Setting::get('launch_mode_message'),
            'verse' => Setting::get('launch_mode_verse'),
            'verse_ref' => Setting::get('launch_mode_verse_ref'),
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $gates
     */
    private static function syncLegacySettings(array $gates): void
    {
        $primary = $gates[0] ?? self::defaultGate();

        Setting::set('launch_mode_enabled', collect($gates)->contains(fn (array $gate): bool => (bool) ($gate['enabled'] ?? false)) ? '1' : '0', 'launch');
        Setting::set('launch_mode_scope', (string) ($primary['scope'] ?? self::SCOPE_SITE), 'launch');
        Setting::set('launch_mode_target_path', (string) ($primary['target_path'] ?? ''), 'launch');
        Setting::set('launch_mode_path_match', (string) ($primary['path_match'] ?? self::MATCH_PREFIX), 'launch');
        Setting::set('launch_countdown_at', (string) ($primary['countdown_at'] ?? ''), 'launch');
        Setting::set('launch_mode_launched_at', (string) ($primary['launched_at'] ?? ''), 'launch');
        Setting::set('launch_mode_show_countdown', ($primary['show_countdown'] ?? true) ? '1' : '0', 'launch');
        Setting::set('launch_mode_allow_ribbon_cut', ($primary['allow_admin_ribbon'] ?? true) ? '1' : '0', 'launch');
        Setting::set('launch_mode_event_name', (string) ($primary['event_name'] ?? ''), 'launch');
        Setting::set('launch_mode_subtitle', (string) ($primary['subtitle'] ?? ''), 'launch');
        Setting::set('launch_mode_title', (string) ($primary['title'] ?? ''), 'launch');
        Setting::set('launch_mode_message', (string) ($primary['message'] ?? ''), 'launch');
        Setting::set('launch_mode_verse', (string) ($primary['verse'] ?? ''), 'launch');
        Setting::set('launch_mode_verse_ref', (string) ($primary['verse_ref'] ?? ''), 'launch');
    }
}
