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
            return static::query()
                ->pluck('value', 'key')
                ->all();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $values = static::allValues();

        return array_key_exists($key, $values) ? $values[$key] : $default;
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
        Cache::forget('settings.all');
    }
}
