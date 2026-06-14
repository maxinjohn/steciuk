<?php

namespace App\Support;

use App\Models\Setting;

class FaithContent
{
    /**
     * @return array<int, array{text: string, ref: string, only_on?: string}>
     */
    public static function sanctuaryVerses(): array
    {
        return self::filterVerses(self::normalizedVerses(), fn (array $verse): bool => ($verse['only_on'] ?? '') === '');
    }

    /**
     * Verses for the public trust-bar ticker (admin pool, then prefilled global library).
     *
     * @return list<array{text: string, ref: string}>
     */
    public static function trustBarVerses(): array
    {
        $verses = self::sanctuaryVerses();

        if ($verses !== []) {
            return array_map(static fn (array $verse): array => [
                'text' => $verse['text'],
                'ref' => $verse['ref'],
            ], $verses);
        }

        return array_map(static fn (array $verse): array => [
            'text' => $verse['text'],
            'ref' => $verse['ref'],
        ], self::filterVerses(
            self::mapReferenceVerses(ReferenceSiteContent::faithSanctuaryVerses()),
            fn (array $verse): bool => ($verse['only_on'] ?? '') === '',
        ));
    }

    /**
     * @return array{text: string, ref: string}
     */
    public static function randomVerse(?string $context = null): array
    {
        $verses = self::normalizedVerses();

        if ($verses === []) {
            $verses = self::mapReferenceVerses(ReferenceSiteContent::faithSanctuaryVerses());
        }

        $context = self::normalizeContext($context);
        $pool = self::versePoolForContext($verses, $context);

        if ($pool === []) {
            return ['text' => '', 'ref' => ''];
        }

        $picked = $pool[array_rand($pool)];

        return [
            'text' => $picked['text'],
            'ref' => $picked['ref'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function comfortCards(): array
    {
        $stored = Setting::get('faith_comfort_cards');
        $decoded = is_string($stored) ? json_decode($stored, true) : $stored;

        if (is_array($decoded) && $decoded !== []) {
            return $decoded;
        }

        return ReferenceSiteContent::faithComfortCards();
    }

    /**
     * @return list<array{kicker: string, note: string}>
     */
    public static function sanctuaryRibbons(): array
    {
        $ribbons = self::normalizedRibbons(self::decodeJsonSetting('faith_sanctuary_ribbons'));

        if ($ribbons !== []) {
            return $ribbons;
        }

        $legacyKicker = trim((string) Setting::get('faith_sanctuary_kicker', ''));
        $legacyNote = trim((string) Setting::get('faith_sanctuary_note', ''));

        if ($legacyKicker !== '' || $legacyNote !== '') {
            return [[
                'kicker' => $legacyKicker,
                'note' => $legacyNote,
            ]];
        }

        return FaithCopyLibrary::sanctuaryRibbons();
    }

    /**
     * @return array{kicker: string, note: string}
     */
    public static function sanctuaryRibbon(): array
    {
        $ribbons = self::sanctuaryRibbons();

        if ($ribbons === []) {
            return ['kicker' => '', 'note' => ''];
        }

        return $ribbons[self::rotationIndex(count($ribbons))];
    }

    /**
     * @return list<array{kicker: string, heading: string, subheading: string}>
     */
    public static function comfortHeaders(): array
    {
        $headers = self::normalizedHeaders(self::decodeJsonSetting('faith_comfort_headers'));

        if ($headers !== []) {
            return $headers;
        }

        $legacyKicker = trim((string) Setting::get('faith_comfort_kicker', ''));
        $legacyHeading = trim((string) Setting::get('faith_comfort_heading', ''));
        $legacySubheading = trim((string) Setting::get('faith_comfort_subheading', ''));

        if ($legacyKicker !== '' || $legacyHeading !== '' || $legacySubheading !== '') {
            return [[
                'kicker' => $legacyKicker,
                'heading' => $legacyHeading,
                'subheading' => $legacySubheading,
            ]];
        }

        return FaithCopyLibrary::comfortHeaders();
    }

    /**
     * @return array{kicker: string, heading: string, subheading: string}
     */
    public static function comfortHeader(): array
    {
        $headers = self::comfortHeaders();

        if ($headers === []) {
            return ['kicker' => '', 'heading' => '', 'subheading' => ''];
        }

        return $headers[self::rotationIndex(count($headers))];
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $decoded
     * @return list<array{kicker: string, note: string}>
     */
    private static function normalizedRibbons(?array $decoded): array
    {
        return collect($decoded ?? [])
            ->map(function (mixed $ribbon): array {
                if (! is_array($ribbon)) {
                    return ['kicker' => '', 'note' => ''];
                }

                return [
                    'kicker' => trim((string) ($ribbon['kicker'] ?? '')),
                    'note' => trim((string) ($ribbon['note'] ?? '')),
                ];
            })
            ->filter(fn (array $ribbon): bool => $ribbon['kicker'] !== '' || $ribbon['note'] !== '')
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $decoded
     * @return list<array{kicker: string, heading: string, subheading: string}>
     */
    private static function normalizedHeaders(?array $decoded): array
    {
        return collect($decoded ?? [])
            ->map(function (mixed $header): array {
                if (! is_array($header)) {
                    return ['kicker' => '', 'heading' => '', 'subheading' => ''];
                }

                return [
                    'kicker' => trim((string) ($header['kicker'] ?? '')),
                    'heading' => trim((string) ($header['heading'] ?? '')),
                    'subheading' => trim((string) ($header['subheading'] ?? '')),
                ];
            })
            ->filter(fn (array $header): bool => $header['kicker'] !== '' || $header['heading'] !== '' || $header['subheading'] !== '')
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function decodeJsonSetting(string $key): array
    {
        $stored = Setting::get($key);
        $decoded = is_string($stored) ? json_decode($stored, true) : $stored;

        return is_array($decoded) ? $decoded : [];
    }

    private static function rotationIndex(int $count): int
    {
        if ($count <= 0) {
            return 0;
        }

        return (int) now()->format('w') % $count;
    }

    /**
     * @param  array<int, array{text: string, ref: string, only_on?: string}>  $verses
     * @return array<int, array{text: string, ref: string, only_on?: string}>
     */
    private static function versePoolForContext(array $verses, ?string $context): array
    {
        if ($context === null) {
            return self::filterVerses($verses, fn (array $verse): bool => ($verse['only_on'] ?? '') === '');
        }

        $scoped = self::filterVerses($verses, fn (array $verse): bool => ($verse['only_on'] ?? '') === $context);

        if ($scoped !== []) {
            return $scoped;
        }

        return self::filterVerses($verses, fn (array $verse): bool => ($verse['only_on'] ?? '') === '');
    }

    /**
     * @param  array<int, array{text: string, ref: string, only_on?: string}>  $verses
     * @param  callable(array{text: string, ref: string, only_on?: string}): bool  $predicate
     * @return array<int, array{text: string, ref: string, only_on?: string}>
     */
    private static function filterVerses(array $verses, callable $predicate): array
    {
        return array_values(array_filter($verses, $predicate));
    }

    /**
     * @param  array<int, array{text: string, ref: string, only_on?: string}>  $verses
     * @return array<int, array{text: string, ref: string, only_on: string}>
     */
    private static function mapReferenceVerses(array $verses): array
    {
        return collect($verses)
            ->map(fn (array $verse): array => [
                'text' => $verse['text'],
                'ref' => $verse['ref'],
                'only_on' => self::normalizeContext((string) ($verse['only_on'] ?? '')) ?? '',
            ])
            ->all();
    }

    /**
     * @return array<int, array{text: string, ref: string, only_on: string}>
     */
    private static function normalizedVerses(): array
    {
        $decoded = self::decodeStoredVerses();

        return collect($decoded ?? [])
            ->map(function (mixed $verse): array {
                if (! is_array($verse)) {
                    return ['text' => '', 'ref' => '', 'only_on' => ''];
                }

                return [
                    'text' => trim((string) ($verse['text'] ?? '')),
                    'ref' => trim((string) ($verse['ref'] ?? '')),
                    'only_on' => self::normalizeContext((string) ($verse['only_on'] ?? '')) ?? '',
                ];
            })
            ->filter(fn (array $verse): bool => $verse['text'] !== '')
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{text: string, ref: string}>|null
     */
    private static function decodeStoredVerses(): ?array
    {
        $stored = Setting::get('faith_sanctuary_verses');
        $decoded = is_string($stored) ? json_decode($stored, true) : $stored;

        return is_array($decoded) ? $decoded : null;
    }

    private static function normalizeContext(?string $context): ?string
    {
        if ($context === null) {
            return null;
        }

        $context = trim(strtolower($context));

        if ($context === '') {
            return null;
        }

        if (str_starts_with($context, '/')) {
            return '/'.trim($context, '/');
        }

        return $context;
    }
}
