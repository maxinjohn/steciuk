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

                if ($best === null || $candidate['starts_at']->lt($best['starts_at'])) {
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
     * @return array{service: Service, starts_at: CarbonInterface, ends_at: CarbonInterface|null}|null
     */
    private static function nextOccurrence(Service $service): ?array
    {
        $type = $service->scheduleType();
        $data = ServiceSchedule::normalizeData($type, $service->schedule_data ?? []);
        $now = Carbon::now();

        if ($type === ServiceScheduleType::OneTime) {
            return self::occurrenceFromRow($service, $data['occurrences'][0] ?? [], $now);
        }

        if ($type === ServiceScheduleType::Multiple) {
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
     * @param  array<string, mixed>  $occurrence
     * @return array{service: Service, starts_at: CarbonInterface, ends_at: CarbonInterface|null}|null
     */
    private static function occurrenceFromRow(Service $service, array $occurrence, CarbonInterface $now): ?array
    {
        if (blank($occurrence['date'] ?? null)) {
            return null;
        }

        $startsAt = self::buildDateTime((string) $occurrence['date'], (string) ($occurrence['start_time'] ?? '10:30'));

        if ($startsAt === null || $startsAt->lt($now)) {
            return null;
        }

        $endsAt = filled($occurrence['end_time'] ?? null)
            ? self::buildDateTime((string) $occurrence['date'], (string) $occurrence['end_time'])
            : null;

        return [
            'service' => $service,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ];
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

            if ($startsAt !== null && $startsAt->gte($now)) {
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
     * @param  array{service: Service, starts_at: CarbonInterface, ends_at: CarbonInterface|null}  $match
     * @return array{label: string, detail: string, url: string, is_live: bool}
     */
    private static function present(array $match): array
    {
        /** @var Service $service */
        $service = $match['service'];
        $startsAt = $match['starts_at'];
        $endsAt = $match['ends_at'];
        $now = Carbon::now();

        $isLive = $startsAt->lte($now)
            && ($endsAt === null || $endsAt->gte($now->copy()->subMinutes(10)));

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

        return [
            'label' => $label,
            'detail' => $location,
            'url' => url('/service-times'),
            'is_live' => $isLive,
        ];
    }
}
