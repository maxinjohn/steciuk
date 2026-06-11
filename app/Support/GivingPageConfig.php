<?php

namespace App\Support;

use App\Models\Setting;

class GivingPageConfig
{
    /**
     * @return array<string, string|null>
     */
    public static function bankDetails(): array
    {
        return [
            'bank_name' => Setting::text('give_bank_name'),
            'account_name' => Setting::text('give_account_name'),
            'sort_code' => Setting::text('give_sort_code'),
            'account_number' => Setting::text('give_account_number'),
            'reference' => Setting::text('give_payment_reference'),
            'payment_link' => Setting::text('give_payment_link'),
        ];
    }

    public static function hasBankDetails(): bool
    {
        $details = self::bankDetails();

        return filled($details['bank_name'])
            || filled($details['account_name'])
            || filled($details['sort_code'])
            || filled($details['account_number']);
    }

    public static function pageHeading(): string
    {
        return Setting::text('give_page_heading') ?: 'Support our parish';
    }

    public static function pageIntro(): string
    {
        return Setting::text('give_page_intro')
            ?: 'Your generous giving supports worship, pastoral care, and gospel mission across our UK parish.';
    }

    public static function anonymousIntro(): string
    {
        return Setting::text('give_anonymous_intro')
            ?: 'You can give anonymously by bank transfer using the parish account details below. No account or personal details are required.';
    }

    public static function memberIntro(): string
    {
        return Setting::text('give_member_intro')
            ?: 'Sign in to report a gift you have already made and view your approved giving history in your member account.';
    }

    /**
     * @return list<string>
     */
    public static function settingKeys(): array
    {
        return [
            'give_page_heading',
            'give_page_intro',
            'give_anonymous_intro',
            'give_member_intro',
            'give_bank_name',
            'give_account_name',
            'give_sort_code',
            'give_account_number',
            'give_payment_reference',
            'give_payment_link',
        ];
    }
}
