<?php

namespace App\Support;

use App\Enums\ServiceScheduleType;
use App\Models\Service;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;

class NextWorshipChip
{
    private const CACHE_KEY = 'site.next_worship_chip.v1';

    private const CACHE_TTL_SECONDS = 300;

    private const DEFAULT_LIVE_WINDOW_HOURS = 2;

    /**
     * @return array{label: string, detail: string, url: string, is_live: bool}|null
     */
    public static function resolve(): ?array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, static function (): ?array {
            $services = Service::query()
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->get();

            $best = null;

            foreach ($services as $service) {
                $candidate = self::nextOccurrence($service);

                if ($candidate === null) {
                    continue;
                }

                if ($best === null) {
                    $best = $candidate;

                    continue;
                }

                if (! empty($candidate['is_live']) && empty($best['is_live'])) {
                    $best = $candidate;

                    continue;
                }

                if (! empty($candidate['is_live']) === ! empty($best['is_live'])
                    && $candidate['starts_at']->lt($best['starts_at'])) {
                    $best = $candidate;
                }
            }

            if ($best === null) {
                return null;
            }

            return self::present($best);
        });
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array{service: Service, starts_at: CarbonInterface, ends_at: CarbonInterface|null, is_live?: bool}|null
     */
    private static function nextOccurrence(Service $service): ?array
    {
        $type = $service->scheduleType();
        $data = ServiceSchedule::normalizeData($type, $service->schedule_data ?? []);
        $now = Carbon::now();

        if ($type === ServiceScheduleType::OneTime || $type === ServiceScheduleType::Multiple) {
            foreach (collect($data['occurrences'] ?? [])->sortBy('date') as $occurrence) {
                $match = self::occurrenceFromRow($service, $occurrence, $now);

                if ($match !== null) {
                    return $match;
                }
            }

            return null;
        }

        $unit = (string) ($data['interval_unit'] ?? 'weekly');

        if ($unit === 'monthly') {
            return null;
        }

        $weekday = (string) ($data['weekday'] ?? 'sunday');
        $stepWeeks = $unit === 'fortnightly' ? 2 : max(1, (int) ($data['interval'] ?? 1));
        $time = (string) ($data['start_time'] ?? '10:30');
        $endTime = filled($data['end_time'] ?? null) ? (string) $data['end_time'] : null;

        $liveNow = self::liveRecurringOccurrence($service, $weekday, $time, $endTime, $now);

        if ($liveNow !== null) {
            return $liveNow;
        }

        $startsAt = self::nextWeekdayStart($weekday, $time, $stepWeeks, $now);

        if ($startsAt === null) {
            return null;
        }

        return [
            'service' => $service,
            'starts_at' => $startsAt,
            'ends_at' => self::buildDateTime($startsAt->toDateString(), $endTime),
        ];
    }

    /**
     * @return array{service: Service, starts_at: CarbonInterface, ends_at: CarbonInterface|null, is_live: bool}|null
     */
    private static function liveRecurringOccurrence(
        Service $service,
        string $weekday,
        string $time,
        ?string $endTime,
        CarbonInterface $now,
    ): ?array {
        $target = self::matchWeekdayNumber($weekday);

        if ($target === null || (int) $now->dayOfWeek !== $target) {
            return null;
        }

        $startsAt = self::buildDateTime($now->toDateString(), $time);

        if ($startsAt === null || $startsAt->gt($now)) {
            return null;
        }

        $endsAt = self::resolveEndTime($startsAt, $endTime);

        if ($endsAt->lt($now)) {
            return null;
        }

        return [
            'service' => $service,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'is_live' => true,
        ];
    }

    /**
     * @param  array<string, mixed>  $occurrence
     * @return array{service: Service, starts_at: CarbonInterface, ends_at: CarbonInterface|null, is_live?: bool}|null
     */
    private static function occurrenceFromRow(Service $service, array $occurrence, CarbonInterface $now): ?array
    {
        if (blank($occurrence['date'] ?? null)) {
            return null;
        }

        $startsAt = self::buildDateTime((string) $occurrence['date'], (string) ($occurrence['start_time'] ?? '10:30'));

        if ($startsAt === null) {
            return null;
        }

        $endsAt = filled($occurrence['end_time'] ?? null)
            ? self::buildDateTime((string) $occurrence['date'], (string) $occurrence['end_time'])
            : null;

        if ($startsAt->gt($now)) {
            return [
                'service' => $service,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ];
        }

        $effectiveEnd = self::resolveEndTime($startsAt, filled($occurrence['end_time'] ?? null) ? (string) $occurrence['end_time'] : null);

        if ($effectiveEnd->lt($now)) {
            return null;
        }

        return [
            'service' => $service,
            'starts_at' => $startsAt,
            'ends_at' => $effectiveEnd,
            'is_live' => true,
        ];
    }

    private static function resolveEndTime(CarbonInterface $startsAt, ?string $endTime): CarbonInterface
    {
        if (filled($endTime)) {
            $endsAt = self::buildDateTime($startsAt->toDateString(), $endTime);

            if ($endsAt !== null && $endsAt->gte($startsAt)) {
                return $endsAt;
            }
        }

        return $startsAt->copy()->addHours(self::DEFAULT_LIVE_WINDOW_HOURS);
    }

    private static function nextWeekdayStart(string $weekday, string $time, int $stepWeeks, CarbonInterface $now): ?CarbonInterface
    {
        $target = self::matchWeekdayNumber($weekday);

        if ($target === null) {
            return null;
        }

        $cursor = $now->copy()->startOfDay();
        $attempts = 0;

        while ($attempts < 104) {
            $candidateDay = $cursor->copy();

            while ((int) $candidateDay->dayOfWeek !== $target) {
                $candidateDay->addDay();
            }

            $startsAt = self::buildDateTime($candidateDay->toDateString(), $time);

            if ($startsAt !== null && $startsAt->gt($now)) {
                return $startsAt;
            }

            $cursor->addWeeks($stepWeeks);
            $attempts++;
        }

        return null;
    }

    private static function buildDateTime(string $date, ?string $time): ?CarbonInterface
    {
        $clock = trim((string) ($time ?: '10:30'));

        if ($clock === '') {
            $clock = '10:30';
        }

        try {
            return Carbon::parse(trim($date).' '.$clock);
        } catch (\Throwable) {
            return null;
        }
    }

    private static function matchWeekdayNumber(string $weekday): ?int
    {
        return match (strtolower(trim($weekday))) {
            'monday' => CarbonInterface::MONDAY,
            'tuesday' => CarbonInterface::TUESDAY,
            'wednesday' => CarbonInterface::WEDNESDAY,
            'thursday' => CarbonInterface::THURSDAY,
            'friday' => CarbonInterface::FRIDAY,
            'saturday' => CarbonInterface::SATURDAY,
            'sunday' => CarbonInterface::SUNDAY,
            default => null,
        };
    }

    /**
     * @param  array{service: Service, starts_at: CarbonInterface, ends_at: CarbonInterface|null, is_live?: bool}  $match
     * @return array{label: string, detail: string, url: string, is_live: bool}
     */
    private static function present(array $match): array
    {
        /** @var Service $service */
        $service = $match['service'];
        $startsAt = $match['starts_at'];
        $endsAt = $match['ends_at'];
        $now = Carbon::now();

        $isLive = ! empty($match['is_live'])
            || ($startsAt->lte($now) && $endsAt !== null && $endsAt->gte($now));

        if ($isLive) {
            $label = 'Worship now';
        } elseif ($startsAt->isToday()) {
            $label = 'Today · '.$startsAt->format('g:i A');
        } elseif ($startsAt->isTomorrow()) {
            $label = 'Tomorrow · '.$startsAt->format('g:i A');
        } elseif ($startsAt->lte($now->copy()->addDays(6))) {
            $label = $startsAt->format('l · g:i A');
        } else {
            $label = 'Next · '.$startsAt->format('D j M · g:i A');
        }

        $location = trim((string) ($service->location ?: $service->title));
        $url = $isLive && filled($service->online_stream_link)
            ? url('/online-worship')
            : url('/service-times');

        return [
            'label' => $label,
            'detail' => $location,
            'url' => $url,
            'is_live' => $isLive,
        ];
    }
}
