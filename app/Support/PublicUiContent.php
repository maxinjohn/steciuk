<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Http\Request;

class PublicUiContent
{
    /**
     * @return array{kicker: string, items: list<array{label: string, ref: string, href: string}>}
     */
    public static function sparkStrip(): array
    {
        return self::normalizeSparkStrip(self::decodeJson('public_ui_spark_strip') ?? PublicUiCopyLibrary::sparkStrip());
    }

    /**
     * @return list<array{text: string, ref: string}>
     */
    public static function divineWhispers(): array
    {
        $stored = self::decodeJson('public_ui_divine_whispers');

        if (is_array($stored) && $stored !== []) {
            return self::normalizeWhispers($stored);
        }

        return PublicUiCopyLibrary::divineWhispers();
    }

    /**
     * @return array{kicker: string, items: list<array{label: string, desc: string, href: string, icon: string, tone: string}>}
     */
    public static function actionStrip(): array
    {
        return self::normalizeActionStrip(self::decodeJson('public_ui_action_strip') ?? PublicUiCopyLibrary::actionStrip());
    }

    /**
     * @return array{kicker: string, scripture: string, scripture_ref: string}
     */
    public static function pageIntroDefaults(): array
    {
        $stored = self::decodeJson('public_ui_page_intro');

        if (! is_array($stored)) {
            return PublicUiCopyLibrary::pageIntro();
        }

        $defaults = PublicUiCopyLibrary::pageIntro();

        return [
            'kicker' => trim((string) ($stored['kicker'] ?? '')) ?: $defaults['kicker'],
            'scripture' => trim((string) ($stored['scripture'] ?? '')) ?: $defaults['scripture'],
            'scripture_ref' => trim((string) ($stored['scripture_ref'] ?? '')) ?: $defaults['scripture_ref'],
        ];
    }

    /**
     * @return array{label: string, url: string, aria_label: string}
     */
    public static function prayerFab(): array
    {
        $stored = self::decodeJson('public_ui_prayer_fab');

        if (! is_array($stored)) {
            return PublicUiCopyLibrary::prayerFab();
        }

        $defaults = PublicUiCopyLibrary::prayerFab();

        return [
            'label' => trim((string) ($stored['label'] ?? '')) ?: $defaults['label'],
            'url' => self::normalizeUrl((string) ($stored['url'] ?? '')) ?: $defaults['url'],
            'aria_label' => trim((string) ($stored['aria_label'] ?? '')) ?: $defaults['aria_label'],
        ];
    }

    public static function heavenlyAtmosphereEnabled(): bool
    {
        return self::experienceToggles()['heavenly_atmosphere'];
    }

    /**
     * @return array{enabled: bool, speculation_rules: bool, reading_progress: bool, heavenly_atmosphere: bool}
     */
    public static function experienceToggles(): array
    {
        $stored = self::decodeJson('public_ui_experience');

        if (! is_array($stored)) {
            return PublicUiCopyLibrary::experienceToggles();
        }

        $defaults = PublicUiCopyLibrary::experienceToggles();

        return [
            'enabled' => array_key_exists('enabled', $stored) ? (bool) $stored['enabled'] : $defaults['enabled'],
            'speculation_rules' => array_key_exists('speculation_rules', $stored) ? (bool) $stored['speculation_rules'] : $defaults['speculation_rules'],
            'reading_progress' => array_key_exists('reading_progress', $stored) ? (bool) $stored['reading_progress'] : $defaults['reading_progress'],
            'heavenly_atmosphere' => array_key_exists('heavenly_atmosphere', $stored) ? (bool) $stored['heavenly_atmosphere'] : $defaults['heavenly_atmosphere'],
        ];
    }

    /**
     * @return array{kicker: string, text: string, ref: string}|null
     */
    public static function contextScriptureForRequest(?Request $request = null): ?array
    {
        $request ??= request();

        if ($request === null) {
            return null;
        }

        $entries = self::contextScriptureEntries();

        foreach ($entries as $entry) {
            if (($entry['route'] ?? '') === 'default') {
                continue;
            }

            if (! self::matchesContextEntry($request, $entry)) {
                continue;
            }

            return [
                'kicker' => $entry['kicker'],
                'text' => $entry['text'],
                'ref' => $entry['ref'],
            ];
        }

        $fallback = collect($entries)->firstWhere('route', 'default');

        if (is_array($fallback)) {
            return [
                'kicker' => $fallback['kicker'],
                'text' => $fallback['text'],
                'ref' => $fallback['ref'],
            ];
        }

        return null;
    }

    /**
     * @return list<array{route: string, slug: string, kicker: string, text: string, ref: string}>
     */
    public static function contextScriptureEntries(): array
    {
        $stored = self::decodeJson('public_ui_context_scripture');

        if (! is_array($stored) || $stored === []) {
            return PublicUiCopyLibrary::contextScripture();
        }

        return collect($stored)
            ->map(function (mixed $row): array {
                if (! is_array($row)) {
                    return ['route' => '', 'slug' => '', 'kicker' => '', 'text' => '', 'ref' => ''];
                }

                return [
                    'route' => trim((string) ($row['route'] ?? '')),
                    'slug' => trim((string) ($row['slug'] ?? '')),
                    'kicker' => trim((string) ($row['kicker'] ?? '')),
                    'text' => trim((string) ($row['text'] ?? '')),
                    'ref' => trim((string) ($row['ref'] ?? '')),
                ];
            })
            ->filter(fn (array $row): bool => $row['route'] !== '' && $row['text'] !== '')
            ->values()
            ->all();
    }

    /**
     * @param  array{route: string, slug: string, kicker: string, text: string, ref: string}  $entry
     */
    private static function matchesContextEntry(Request $request, array $entry): bool
    {
        $route = $entry['route'];
        $slug = $entry['slug'];

        if ($route === 'default') {
            return false;
        }

        if ($route === 'pages.show') {
            if (! $request->routeIs('pages.show')) {
                return false;
            }

            if ($slug === '') {
                return true;
            }

            return (string) $request->route('slug', '') === $slug;
        }

        if (str_ends_with($route, '.*')) {
            return $request->routeIs($route);
        }

        return $request->routeIs($route);
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function decodeJson(string $key): ?array
    {
        $stored = Setting::get($key);
        $decoded = is_string($stored) ? json_decode($stored, true) : $stored;

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{kicker: string, items: list<array{label: string, ref: string, href: string}>}
     */
    private static function normalizeSparkStrip(array $data): array
    {
        $defaults = PublicUiCopyLibrary::sparkStrip();
        $items = collect($data['items'] ?? [])
            ->map(function (mixed $item): array {
                if (! is_array($item)) {
                    return ['label' => '', 'ref' => '', 'href' => ''];
                }

                return [
                    'label' => trim((string) ($item['label'] ?? '')),
                    'ref' => trim((string) ($item['ref'] ?? '')),
                    'href' => self::normalizeUrl((string) ($item['href'] ?? '')) ?: url('/our-church'),
                ];
            })
            ->filter(fn (array $item): bool => $item['label'] !== '')
            ->values()
            ->all();

        return [
            'kicker' => trim((string) ($data['kicker'] ?? '')) ?: $defaults['kicker'],
            'items' => $items !== [] ? $items : $defaults['items'],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array{text: string, ref: string}>
     */
    private static function normalizeWhispers(array $rows): array
    {
        return collect($rows)
            ->map(fn (mixed $row): array => is_array($row) ? [
                'text' => trim((string) ($row['text'] ?? '')),
                'ref' => trim((string) ($row['ref'] ?? '')),
            ] : ['text' => '', 'ref' => ''])
            ->filter(fn (array $row): bool => $row['text'] !== '')
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{kicker: string, items: list<array{label: string, desc: string, href: string, icon: string, tone: string}>}
     */
    private static function normalizeActionStrip(array $data): array
    {
        $defaults = PublicUiCopyLibrary::actionStrip();
        $tones = ['gold', 'navy', 'rose', 'violet', 'sky'];
        $items = collect($data['items'] ?? [])
            ->map(function (mixed $item) use ($tones): array {
                if (! is_array($item)) {
                    return ['label' => '', 'desc' => '', 'href' => '', 'icon' => '✝', 'tone' => 'gold'];
                }

                $tone = trim((string) ($item['tone'] ?? 'gold'));

                return [
                    'label' => trim((string) ($item['label'] ?? '')),
                    'desc' => trim((string) ($item['desc'] ?? '')),
                    'href' => self::normalizeUrl((string) ($item['href'] ?? '')),
                    'icon' => trim((string) ($item['icon'] ?? '✝')) ?: '✝',
                    'tone' => in_array($tone, $tones, true) ? $tone : 'gold',
                ];
            })
            ->filter(fn (array $item): bool => $item['label'] !== '')
            ->values()
            ->all();

        return [
            'kicker' => trim((string) ($data['kicker'] ?? '')) ?: $defaults['kicker'],
            'items' => $items !== [] ? $items : $defaults['items'],
        ];
    }

    private static function normalizeUrl(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return '';
        }

        if (preg_match('#^(javascript|data|vbscript):#i', $url)) {
            return '';
        }

        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        return '/'.ltrim($url, '/');
    }
}
