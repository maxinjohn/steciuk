<?php

namespace App\Support;

use App\Enums\ServiceScheduleType;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class ServiceSchedule
{
    /**
     * @return array<string, mixed>
     */
    public static function defaultData(ServiceScheduleType $type): array
    {
        return match ($type) {
            ServiceScheduleType::OneTime => [
                'occurrences' => [
                    self::blankOccurrence(),
                ],
            ],
            ServiceScheduleType::Multiple => [
                'occurrences' => [
                    self::blankOccurrence(),
                    self::blankOccurrence(),
                ],
            ],
            ServiceScheduleType::Recurring => [
                'interval_unit' => 'monthly',
                'interval' => 1,
                'monthly_mode' => 'flexible',
                'weekday' => 'sunday',
                'day_of_month' => null,
                'nth' => 1,
                'start_time' => '10:30',
                'end_time' => null,
                'note' => '',
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    public static function blankOccurrence(): array
    {
        return [
            'date' => null,
            'start_time' => '10:30',
            'end_time' => null,
            'note' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function legacyToScheduleData(string $serviceDay, string $serviceTime, string $frequency): array
    {
        $day = strtolower(trim($serviceDay));
        $frequencyLower = strtolower(trim($frequency));

        if (str_contains($day, 'month') || str_contains($frequencyLower, 'month')) {
            return [
                'interval_unit' => 'monthly',
                'interval' => 1,
                'monthly_mode' => 'flexible',
                'weekday' => 'sunday',
                'day_of_month' => null,
                'nth' => 1,
                'start_time' => self::looksLikeTime($serviceTime) ? self::normalizeTime($serviceTime) : null,
                'end_time' => null,
                'note' => self::looksLikeTime($serviceTime) ? '' : trim($serviceTime),
            ];
        }

        $weekday = self::matchWeekday($serviceDay);

        if ($weekday !== null) {
            return [
                'interval_unit' => str_contains($frequencyLower, 'fortnight') ? 'fortnightly' : 'weekly',
                'interval' => 1,
                'monthly_mode' => 'flexible',
                'weekday' => $weekday,
                'day_of_month' => null,
                'nth' => 1,
                'start_time' => self::looksLikeTime($serviceTime) ? self::normalizeTime($serviceTime) : '10:30',
                'end_time' => null,
                'note' => self::looksLikeTime($serviceTime) ? '' : trim($serviceTime),
            ];
        }

        return self::defaultData(ServiceScheduleType::Recurring);
    }

    /**
     * @param  array<string, mixed>|null  $scheduleData
     * @return array{service_day: string, service_time: string, frequency: string}
     */
    public static function syncLegacyFields(ServiceScheduleType $type, ?array $scheduleData): array
    {
        $data = self::normalizeData($type, $scheduleData ?? []);

        return match ($type) {
            ServiceScheduleType::OneTime => self::legacyFromOneTime($data),
            ServiceScheduleType::Multiple => self::legacyFromMultiple($data),
            ServiceScheduleType::Recurring => self::legacyFromRecurring($data),
        };
    }

    /**
     * @param  array<string, mixed>|null  $scheduleData
     */
    public static function summary(ServiceScheduleType $type, ?array $scheduleData): string
    {
        $legacy = self::syncLegacyFields($type, $scheduleData);

        return collect([$legacy['service_day'], $legacy['service_time'], $legacy['frequency']])
            ->filter(fn (string $part): bool => trim($part) !== '')
            ->implode(' · ');
    }

    /**
     * @param  array<string, mixed>  $scheduleData
     */
    public static function previewFromForm(string $scheduleType, array $scheduleData): string
    {
        $type = ServiceScheduleType::tryFrom($scheduleType);

        if ($type === null) {
            return 'Choose a schedule type above to see the public summary.';
        }

        $normalized = self::normalizeData($type, $scheduleData);

        if ($type === ServiceScheduleType::OneTime && blank($normalized['occurrences'][0]['date'] ?? null)) {
            return 'Pick a service date and start time above.';
        }

        if ($type === ServiceScheduleType::Multiple && collect($normalized['occurrences'] ?? [])->every(fn (array $row): bool => blank($row['date'] ?? null))) {
            return 'Add at least one date with a start time above.';
        }

        if ($type === ServiceScheduleType::Recurring
            && ($normalized['interval_unit'] ?? 'monthly') === 'monthly'
            && ($normalized['monthly_mode'] ?? 'flexible') === 'flexible'
            && trim((string) ($normalized['note'] ?? '')) === '') {
            return 'For monthly worship with changing dates, add a visitor note above (e.g. contact the parish office for this month’s date).';
        }

        $summary = self::summary($type, $normalized);

        if (trim($summary) === '') {
            return match ($type) {
                ServiceScheduleType::OneTime => 'Pick a service date and start time above.',
                ServiceScheduleType::Multiple => 'Add at least one date with a start time above.',
                ServiceScheduleType::Recurring => 'Complete the repeating pattern above — weekday or monthly note.',
            };
        }

        return $summary;
    }

    /**
     * Structured schedule copy for public location cards.
     *
     * @return array{headline: string, details: list<string>, frequency: ?string, date_lines: list<string>}
     */
    public static function publicScheduleBlocks(
        ServiceScheduleType $type,
        ?array $scheduleData,
        string $fallbackDay = '',
        string $fallbackTime = '',
        string $fallbackFrequency = '',
    ): array {
        $normalized = self::normalizeData($type, $scheduleData ?? []);
        $legacy = self::syncLegacyFields($type, $normalized);

        $dateLines = [];

        if ($type === ServiceScheduleType::Multiple) {
            $dateLines = collect($normalized['occurrences'] ?? [])
                ->filter(fn (array $row): bool => filled($row['date'] ?? null))
                ->sortBy('date')
                ->map(function (array $row): string {
                    $date = Carbon::parse($row['date'])->format('l j M Y');
                    $time = self::formatOccurrenceTime($row);

                    return trim($date.($time !== '' ? ' · '.$time : ''));
                })
                ->values()
                ->all();
        }

        $details = [];

        if ($type !== ServiceScheduleType::Multiple) {
            if (filled($legacy['service_time'])) {
                $details[] = $legacy['service_time'];
            }
        }

        return [
            'headline' => $legacy['service_day'] ?: $fallbackDay,
            'details' => $details !== [] ? $details : (filled($fallbackTime) ? [$fallbackTime] : []),
            'frequency' => $legacy['frequency'] ?: ($fallbackFrequency ?: null),
            'date_lines' => $dateLines,
        ];
    }

    /**
     * @param  array<string, mixed>  $scheduleData
     * @return array<string, mixed>
     */
    public static function normalizeData(ServiceScheduleType $type, array $scheduleData): array
    {
        $defaults = self::defaultData($type);
        $merged = array_replace_recursive($defaults, $scheduleData);

        if (in_array($type, [ServiceScheduleType::OneTime, ServiceScheduleType::Multiple], true)) {
            $occurrences = collect($merged['occurrences'] ?? [])
                ->filter(fn ($occurrence): bool => is_array($occurrence))
                ->map(function (array $occurrence): array {
                    return [
                        'date' => filled($occurrence['date'] ?? null) ? (string) $occurrence['date'] : null,
                        'start_time' => self::normalizeTime($occurrence['start_time'] ?? null),
                        'end_time' => self::normalizeTime($occurrence['end_time'] ?? null),
                        'note' => trim((string) ($occurrence['note'] ?? '')),
                    ];
                })
                ->values()
                ->all();

            if ($occurrences === [] && $type === ServiceScheduleType::OneTime) {
                $occurrences = [self::blankOccurrence()];
            }

            $merged['occurrences'] = $occurrences;
        }

        if ($type === ServiceScheduleType::Recurring) {
            $merged['interval_unit'] = in_array($merged['interval_unit'] ?? '', ['weekly', 'fortnightly', 'monthly'], true)
                ? $merged['interval_unit']
                : 'monthly';
            $merged['interval'] = max(1, (int) ($merged['interval'] ?? 1));
            $merged['monthly_mode'] = in_array($merged['monthly_mode'] ?? '', ['flexible', 'day_of_month', 'nth_weekday', 'last_weekday'], true)
                ? $merged['monthly_mode']
                : 'flexible';
            $merged['weekday'] = self::matchWeekday((string) ($merged['weekday'] ?? 'sunday')) ?? 'sunday';
            $merged['day_of_month'] = filled($merged['day_of_month'] ?? null) ? (int) $merged['day_of_month'] : null;
            $merged['nth'] = max(1, min(4, (int) ($merged['nth'] ?? 1)));
            $merged['start_time'] = self::normalizeTime($merged['start_time'] ?? null);
            $merged['end_time'] = self::normalizeTime($merged['end_time'] ?? null);
            $merged['note'] = trim((string) ($merged['note'] ?? ''));
        }

        return $merged;
    }

    /**
     * @return array<string, string>
     */
    private static function legacyFromOneTime(array $data): array
    {
        $occurrence = $data['occurrences'][0] ?? self::blankOccurrence();
        $date = filled($occurrence['date'] ?? null)
            ? Carbon::parse($occurrence['date'])->format('l j F Y')
            : 'Date to be confirmed';

        return [
            'service_day' => $date,
            'service_time' => self::formatOccurrenceTime($occurrence),
            'frequency' => 'One-off worship service',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function legacyFromMultiple(array $data): array
    {
        $occurrences = collect($data['occurrences'] ?? [])
            ->filter(fn (array $occurrence): bool => filled($occurrence['date'] ?? null))
            ->sortBy('date')
            ->values();

        if ($occurrences->isEmpty()) {
            return [
                'service_day' => 'Multiple dates',
                'service_time' => 'Times to be confirmed',
                'frequency' => 'Multiple worship dates',
            ];
        }

        $preview = $occurrences
            ->take(3)
            ->map(function (array $occurrence): string {
                $date = Carbon::parse($occurrence['date'])->format('j M Y');
                $time = self::formatOccurrenceTime($occurrence);

                return trim($date.($time !== '' ? ' · '.$time : ''));
            })
            ->implode('; ');

        if ($occurrences->count() > 3) {
            $preview .= '; +'.($occurrences->count() - 3).' more';
        }

        return [
            'service_day' => $occurrences->count().' scheduled '.str('date')->plural($occurrences->count()),
            'service_time' => $preview,
            'frequency' => 'Multiple worship dates',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function legacyFromRecurring(array $data): array
    {
        $unit = (string) ($data['interval_unit'] ?? 'monthly');
        $interval = max(1, (int) ($data['interval'] ?? 1));
        $time = self::formatTimeRange($data['start_time'] ?? null, $data['end_time'] ?? null);
        $note = trim((string) ($data['note'] ?? ''));

        if ($unit === 'monthly') {
            $dayLine = match ($data['monthly_mode'] ?? 'flexible') {
                'day_of_month' => 'Monthly on the '.self::ordinal((int) ($data['day_of_month'] ?? 1)),
                'nth_weekday' => 'Monthly on the '.self::ordinal((int) ($data['nth'] ?? 1)).' '.self::weekdayLabel((string) ($data['weekday'] ?? 'sunday')),
                'last_weekday' => 'Monthly on the last '.self::weekdayLabel((string) ($data['weekday'] ?? 'sunday')),
                default => $interval > 1 ? "Every {$interval} months" : 'Monthly',
            };

            $timeLine = $time !== '' ? $time : $note;

            return [
                'service_day' => $dayLine,
                'service_time' => $timeLine !== '' ? $timeLine : 'Contact parish office for the current date and time',
                'frequency' => $interval > 1 ? "Every {$interval} months" : 'Monthly worship service',
            ];
        }

        $weekday = self::weekdayLabel((string) ($data['weekday'] ?? 'sunday'));
        $every = $unit === 'fortnightly'
            ? 'Every 2 weeks'
            : ($interval > 1 ? "Every {$interval} weeks" : 'Weekly');

        return [
            'service_day' => trim($every.' on '.$weekday),
            'service_time' => $time !== '' ? $time : ($note !== '' ? $note : 'Time to be confirmed'),
            'frequency' => $unit === 'fortnightly' ? 'Fortnightly worship service' : 'Weekly worship service',
        ];
    }

    /**
     * @param  array<string, mixed>  $occurrence
     */
    private static function formatOccurrenceTime(array $occurrence): string
    {
        $time = self::formatTimeRange($occurrence['start_time'] ?? null, $occurrence['end_time'] ?? null);
        $note = trim((string) ($occurrence['note'] ?? ''));

        return $time !== '' ? $time : $note;
    }

    private static function formatTimeRange(?string $start, ?string $end): string
    {
        $startFormatted = self::formatClock($start);
        $endFormatted = self::formatClock($end);

        if ($startFormatted === '' && $endFormatted === '') {
            return '';
        }

        if ($startFormatted !== '' && $endFormatted !== '') {
            return "{$startFormatted} – {$endFormatted}";
        }

        return $startFormatted !== '' ? $startFormatted : $endFormatted;
    }

    private static function formatClock(?string $time): string
    {
        $normalized = self::normalizeTime($time);

        if ($normalized === null) {
            return '';
        }

        return Carbon::createFromFormat('H:i', $normalized)->format('g:i A');
    }

    private static function normalizeTime(mixed $time): ?string
    {
        if (! filled($time)) {
            return null;
        }

        $value = trim((string) $time);

        if ($value === '') {
            return null;
        }

        foreach (['H:i', 'H:i:s', 'g:i A', 'g:i a'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('H:i');
            } catch (\Throwable) {
            }
        }

        return null;
    }

    private static function looksLikeTime(string $value): bool
    {
        return self::normalizeTime($value) !== null;
    }

    private static function matchWeekday(string $value): ?string
    {
        $needle = strtolower(trim($value));

        foreach (self::weekdayOptions() as $key => $label) {
            if ($needle === $key || str_contains($needle, $key) || str_contains($needle, strtolower($label))) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    public static function weekdayOptions(): array
    {
        return [
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function intervalUnitOptions(): array
    {
        return [
            'weekly' => 'Weekly',
            'fortnightly' => 'Fortnightly',
            'monthly' => 'Monthly',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function monthlyModeOptions(): array
    {
        return [
            'flexible' => 'Dates change each month (add a visitor note)',
            'day_of_month' => 'Same calendar date every month (e.g. 15th)',
            'nth_weekday' => 'Same weekday position (e.g. 2nd Sunday)',
            'last_weekday' => 'Last weekday of the month (e.g. last Sunday)',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function nthOptions(): array
    {
        return [
            1 => 'First',
            2 => 'Second',
            3 => 'Third',
            4 => 'Fourth',
        ];
    }

    private static function weekdayLabel(string $weekday): string
    {
        return self::weekdayOptions()[$weekday] ?? ucfirst($weekday);
    }

    private static function ordinal(int $number): string
    {
        $suffix = match (true) {
            in_array($number % 100, [11, 12, 13], true) => 'th',
            $number % 10 === 1 => 'st',
            $number % 10 === 2 => 'nd',
            $number % 10 === 3 => 'rd',
            default => 'th',
        };

        return $number.$suffix;
    }
}
