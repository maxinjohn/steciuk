<?php

namespace App\Support;

final class FaithComfortVerseBuckets
{
    public const FIELD_ALL = 'faith_sanctuary_verses_all';

    public const FIELD_ERROR = 'faith_sanctuary_verses_error';

    public const FIELD_MAINTENANCE = 'faith_sanctuary_verses_maintenance';

    public const FIELD_LAUNCH = 'faith_sanctuary_verses_launch';

    public const FIELD_PATHS = 'faith_sanctuary_verses_paths';

    public const SCOPE_ALL = '';

    public const SCOPE_ERROR = 'error';

    public const SCOPE_MAINTENANCE = 'maintenance';

    public const SCOPE_LAUNCH = 'launch';

    /**
     * @return list<string>
     */
    public static function verseFieldNames(): array
    {
        return [
            self::FIELD_ALL,
            self::FIELD_ERROR,
            self::FIELD_MAINTENANCE,
            self::FIELD_LAUNCH,
            self::FIELD_PATHS,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $verses
     * @return array{
     *     faith_sanctuary_verses_all: list<array{ref: string, text: string}>,
     *     faith_sanctuary_verses_error: list<array{ref: string, text: string}>,
     *     faith_sanctuary_verses_maintenance: list<array{ref: string, text: string}>,
     *     faith_sanctuary_verses_launch: list<array{ref: string, text: string}>,
     *     faith_sanctuary_verses_paths: list<array{ref: string, text: string, path: string}>
     * }
     */
    public static function split(array $verses): array
    {
        $buckets = [
            self::FIELD_ALL => [],
            self::FIELD_ERROR => [],
            self::FIELD_MAINTENANCE => [],
            self::FIELD_LAUNCH => [],
            self::FIELD_PATHS => [],
        ];

        foreach ($verses as $verse) {
            if (! is_array($verse)) {
                continue;
            }

            $ref = trim((string) ($verse['ref'] ?? ''));
            $text = trim((string) ($verse['text'] ?? ''));

            if ($text === '') {
                continue;
            }

            $item = ['ref' => $ref, 'text' => $text];
            $scope = self::normalizeScope((string) ($verse['only_on'] ?? ''));

            match ($scope) {
                self::SCOPE_ERROR => $buckets[self::FIELD_ERROR][] = $item,
                self::SCOPE_MAINTENANCE => $buckets[self::FIELD_MAINTENANCE][] = $item,
                self::SCOPE_LAUNCH => $buckets[self::FIELD_LAUNCH][] = $item,
                self::SCOPE_ALL => $buckets[self::FIELD_ALL][] = $item,
                default => $buckets[self::FIELD_PATHS][] = [
                    'ref' => $ref,
                    'text' => $text,
                    'path' => $scope,
                ],
            };
        }

        return $buckets;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<array{ref: string, text: string, only_on: string}>
     */
    public static function merge(array $data): array
    {
        $merged = [];

        $merged = array_merge($merged, self::mapBucket($data[self::FIELD_ALL] ?? [], self::SCOPE_ALL));
        $merged = array_merge($merged, self::mapBucket($data[self::FIELD_ERROR] ?? [], self::SCOPE_ERROR));
        $merged = array_merge($merged, self::mapBucket($data[self::FIELD_MAINTENANCE] ?? [], self::SCOPE_MAINTENANCE));
        $merged = array_merge($merged, self::mapBucket($data[self::FIELD_LAUNCH] ?? [], self::SCOPE_LAUNCH));

        foreach ($data[self::FIELD_PATHS] ?? [] as $verse) {
            if (! is_array($verse)) {
                continue;
            }

            $text = trim((string) ($verse['text'] ?? ''));
            $path = self::normalizeScope((string) ($verse['path'] ?? ''));

            if ($text === '' || $path === self::SCOPE_ALL) {
                continue;
            }

            if (in_array($path, [self::SCOPE_ERROR, self::SCOPE_MAINTENANCE, self::SCOPE_LAUNCH], true)) {
                continue;
            }

            $merged[] = [
                'ref' => trim((string) ($verse['ref'] ?? '')),
                'text' => $text,
                'only_on' => $path,
            ];
        }

        return $merged;
    }

    /**
     * @param  mixed  $items
     * @return list<array{ref: string, text: string, only_on: string}>
     */
    private static function mapBucket(mixed $items, string $scope): array
    {
        if (! is_array($items)) {
            return [];
        }

        $mapped = [];

        foreach ($items as $verse) {
            if (! is_array($verse)) {
                continue;
            }

            $text = trim((string) ($verse['text'] ?? ''));

            if ($text === '') {
                continue;
            }

            $mapped[] = [
                'ref' => trim((string) ($verse['ref'] ?? '')),
                'text' => $text,
                'only_on' => $scope,
            ];
        }

        return $mapped;
    }

    private static function normalizeScope(string $scope): string
    {
        $scope = trim(strtolower($scope));

        if ($scope === '') {
            return self::SCOPE_ALL;
        }

        if (str_starts_with($scope, '/')) {
            return '/'.trim($scope, '/');
        }

        return $scope;
    }
}
