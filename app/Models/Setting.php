<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    private const ALL_CACHE_KEY = 'settings.all.v1';

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * @return array<string, string|null>
     */
    public static function allValues(): array
    {
        return Cache::rememberForever(static::ALL_CACHE_KEY, function (): array {
            if (! \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                return [];
            }

            return static::query()
                ->pluck('value', 'key')
                ->all();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $values = static::allValues();
        } catch (\Throwable) {
            return $default;
        }

        return array_key_exists($key, $values) ? $values[$key] : $default;
    }

    public static function text(string $key, string $default = ''): string
    {
        $value = trim((string) static::get($key, $default));

        return $value !== '' ? $value : $default;
    }

    public static function set(string $key, mixed $value, ?string $group = null): static
    {
        $storedValue = is_array($value) ? json_encode($value) : (string) $value;

        $setting = static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $storedValue, 'group' => $group],
        );

        static::forgetCache();

        return $setting;
    }

    public static function forget(string $key): void
    {
        static::query()->where('key', $key)->delete();
        static::forgetCache();
    }

    public static function forgetCache(): void
    {
        Cache::forget(static::ALL_CACHE_KEY);
    }

    public static function assetUrl(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return asset(ltrim($path, '/'));
        }

        try {
            return \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($path, '/'));
        } catch (\Throwable) {
            return asset('storage/'.ltrim($path, '/'));
        }
    }
}
