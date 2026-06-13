<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class Gravatar
{
    public static function hashForEmail(string $email): string
    {
        return md5(strtolower(trim($email)));
    }

    public static function url(string $email, int $size = 256): string
    {
        $hash = self::hashForEmail($email);

        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=404";
    }

    public static function exists(string $email, int $size = 256): bool
    {
        if (! filled($email)) {
            return false;
        }

        $hash = self::hashForEmail($email);
        $cacheKey = "gravatar.exists.{$hash}";

        return Cache::remember($cacheKey, now()->addDay(), function () use ($hash, $size): bool {
            try {
                $response = Http::timeout(4)
                    ->withHeaders(['User-Agent' => 'STECI-UK-Parish/1.0'])
                    ->head("https://www.gravatar.com/avatar/{$hash}?s={$size}&d=404");

                return $response->successful();
            } catch (Throwable) {
                return false;
            }
        });
    }

    public static function forgetCache(?string $email): void
    {
        if (! filled($email)) {
            return;
        }

        Cache::forget('gravatar.exists.'.self::hashForEmail($email));
    }
}
