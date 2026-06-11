<?php

namespace App\Support;

class GivingUrl
{
    public static function route(): string
    {
        return route('give');
    }

    public static function isEnabled(?string $donationLink = null): bool
    {
        $link = $donationLink ?? Setting::text('donation_link');

        return ! filled($link) || ! self::isExplicitlyDisabled($link);
    }

    public static function resolve(?string $url = null): string
    {
        $url ??= Setting::text('donation_link');

        if (blank($url) || self::pointsToGivePage($url)) {
            return self::route();
        }

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        if (str_starts_with($url, '/')) {
            return url($url);
        }

        return url('/'.ltrim($url, '/'));
    }

    public static function pointsToGivePage(?string $url): bool
    {
        if (blank($url)) {
            return true;
        }

        $trimmed = trim($url);

        if (in_array($trimmed, ['/give', 'give'], true)) {
            return true;
        }

        if (preg_match('#^https?://(www\.)?steciuk\.org/give/?$#i', $trimmed)) {
            return true;
        }

        return str_starts_with($trimmed, '/') && rtrim($trimmed, '/') === '/give';
    }

    private static function isExplicitlyDisabled(string $url): bool
    {
        return in_array(strtolower(trim($url)), ['0', 'false', 'off', 'disabled', 'none', '#'], true);
    }
}
