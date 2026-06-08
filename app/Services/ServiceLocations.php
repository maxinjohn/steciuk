<?php

namespace App\Services;

use App\Models\Service;
use Illuminate\Support\Facades\Cache;

class ServiceLocations
{
    private const CACHE_KEY = 'services.locations.v1';

    private const TTL_SECONDS = 3600;

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return Cache::remember(self::CACHE_KEY, self::TTL_SECONDS, static function (): array {
            return Service::query()
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->pluck('location')
                ->filter()
                ->values()
                ->all();
        });
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
