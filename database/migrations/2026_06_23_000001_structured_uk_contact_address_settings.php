<?php

use App\Models\Setting;
use App\Support\UkAddressFormatter;
use App\Support\UkPostcode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $defaults = [
            'contact_address_line_1' => '',
            'contact_address_line_2' => '',
            'contact_city' => '',
            'contact_county' => '',
            'contact_postcode' => '',
            'contact_country' => 'United Kingdom',
        ];

        foreach ($defaults as $key => $value) {
            if (Setting::get($key) === null) {
                Setting::set($key, $value, 'contact');
            }
        }

        $legacy = trim((string) (Setting::get('main_address') ?? ''));

        if ($legacy !== '' && $legacy !== 'United Kingdom' && blank(Setting::get('contact_address_line_1'))) {
            Setting::set('contact_address_line_1', $legacy, 'contact');
        }

        $postcode = UkPostcode::normalize(Setting::get('contact_postcode'));

        if ($postcode !== null) {
            Setting::set('contact_postcode', $postcode, 'contact');
        }

        Setting::set(
            'main_address',
            UkAddressFormatter::fromSettings(Setting::allValues()) ?? 'United Kingdom',
            'contact',
        );

        Setting::forgetCache();
    }

    public function down(): void
    {
        // Forward-only content migration.
    }
};
