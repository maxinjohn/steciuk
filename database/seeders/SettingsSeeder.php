<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\SeedConfig;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'church_name', 'value' => 'St. Thomas Evangelical Church of India – UK Parish', 'group' => 'general'],
            ['key' => 'motto', 'value' => 'For the Word of God and for the testimony of Jesus Christ', 'group' => 'general'],
            ['key' => 'contact_email', 'value' => 'admin@steciuk.org', 'group' => 'contact'],
            ['key' => 'phone', 'value' => '07578 189530', 'group' => 'contact'],
            ['key' => 'charity_number', 'value' => '1143030', 'group' => 'contact'],
            ['key' => 'main_address', 'value' => 'United Kingdom', 'group' => 'contact'],
            ['key' => 'facebook', 'value' => 'https://facebook.com/steciuk', 'group' => 'social'],
            ['key' => 'instagram', 'value' => 'https://instagram.com/steciuk', 'group' => 'social'],
            ['key' => 'youtube', 'value' => 'https://youtube.com/@steciuk', 'group' => 'social'],
            ['key' => 'twitter', 'value' => 'https://twitter.com/steciuk', 'group' => 'social'],
            ['key' => 'google_maps_embed', 'value' => '', 'group' => 'contact'],
            ['key' => 'donation_link', 'value' => 'https://steciuk.org/give', 'group' => 'general'],
            ['key' => 'footer_text', 'value' => 'Serving families across the United Kingdom through worship, fellowship, and witness.', 'group' => 'general'],
            ['key' => 'seo_default_title', 'value' => 'St. Thomas Evangelical Church of India – UK Parish', 'group' => 'seo'],
            ['key' => 'seo_default_description', 'value' => 'Welcome to the UK Parish of the St. Thomas Evangelical Church of India. Join us for worship, fellowship, and spiritual growth across Manchester, Leicester, Dartford, Sunderland, and Bristol.', 'group' => 'seo'],
            ['key' => 'maintenance_mode_message', 'value' => 'Our website is temporarily undergoing maintenance. Please check back soon.', 'group' => 'general'],
            ['key' => 'logo', 'value' => '', 'group' => 'branding'],
            ['key' => 'favicon', 'value' => '', 'group' => 'branding'],
        ];

        foreach ($settings as $setting) {
            if (SeedConfig::shouldOverwriteSettings()) {
                Setting::query()->updateOrCreate(
                    ['key' => $setting['key']],
                    ['value' => $setting['value'], 'group' => $setting['group']],
                );
            } else {
                Setting::query()->firstOrCreate(
                    ['key' => $setting['key']],
                    ['value' => $setting['value'], 'group' => $setting['group']],
                );
            }
        }
    }
}
