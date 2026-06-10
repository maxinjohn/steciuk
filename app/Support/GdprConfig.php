<?php

namespace App\Support;

use App\Models\Setting;

class GdprConfig
{
    public static function privacyPolicyVersion(): string
    {
        return (string) config('gdpr.privacy_policy_version', '2026-06-v2');
    }

    public static function privacyPolicyUrl(): string
    {
        return url('/privacy-policy');
    }

    public static function termsUrl(): string
    {
        return url('/terms-of-use');
    }

    public static function dataProtectionContactEmail(): string
    {
        $email = Setting::get('contact_email');

        if (is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return strtolower(trim($email));
        }

        return strtolower(trim((string) config('site.admin_email', 'admin@steciuk.org')));
    }

    public static function icoComplaintUrl(): string
    {
        return 'https://ico.org.uk/make-a-complaint/';
    }

    /**
     * @return array<string, int>
     */
    public static function retentionSummary(): array
    {
        return (array) config('gdpr.retention', []);
    }
}
