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
            ['key' => 'footer_text', 'value' => 'Confessing Christ as Lord — Word, worship, and witness across the United Kingdom.', 'group' => 'general'],
            ['key' => 'seo_default_title', 'value' => 'St. Thomas Evangelical Church of India – UK Parish', 'group' => 'seo'],
            ['key' => 'seo_default_description', 'value' => 'An evangelical Episcopal parish rooted in the Saint Thomas Christian tradition. Join us for Holy Communion, expository preaching, and prayer across Manchester, Leicester, Dartford, Sunderland, and Bristol.', 'group' => 'seo'],
            ['key' => 'seo_default_og_image', 'value' => '', 'group' => 'seo'],
            ['key' => 'theme_color', 'value' => '#d4cabb', 'group' => 'branding'],
            ['key' => 'pwa_short_name', 'value' => 'STECI UK', 'group' => 'branding'],
            ['key' => 'admin_use_church_logo', 'value' => '1', 'group' => 'branding'],
            ['key' => 'gospel_reminder_kicker', 'value' => 'For the Word of God · and the testimony of Jesus Christ', 'group' => 'general'],
            ['key' => 'gospel_reminder_reference', 'value' => 'Revelation 19:10', 'group' => 'general'],
            ['key' => 'admin_welcome_heading', 'value' => 'Welcome — manage your parish with peace', 'group' => 'admin'],
            ['key' => 'admin_welcome_body', 'value' => 'Tap a sidebar section to expand it — only one group stays open at a time, and every link inside should appear. Edit pages, worship, photos, messages, and settings from here.', 'group' => 'admin'],
            ['key' => 'admin_dashboard_verse', 'value' => 'Be still, and know that I am God.', 'group' => 'admin'],
            ['key' => 'admin_dashboard_verse_ref', 'value' => 'Psalm 46:10', 'group' => 'admin'],
            ['key' => 'maintenance_mode_enabled', 'value' => '0', 'group' => 'general'],
            ['key' => 'maintenance_mode_message', 'value' => 'Our website is temporarily undergoing maintenance. Please check back soon.', 'group' => 'general'],
            ['key' => 'logo', 'value' => '/images/steci-mark.svg', 'group' => 'branding'],
            ['key' => 'favicon', 'value' => '/icons/favicon.svg', 'group' => 'branding'],
            ['key' => 'faith_sanctuary_kicker', 'value' => 'In Christ\'s peace', 'group' => 'faith'],
            ['key' => 'faith_sanctuary_note', 'value' => 'A quiet moment before you go — the Lord is near.', 'group' => 'faith'],
            ['key' => 'faith_sanctuary_verses', 'value' => json_encode([
                ['text' => 'Be still, and know that I am God.', 'ref' => 'Psalm 46:10'],
                ['text' => 'Come to me, all you who are weary and burdened, and I will give you rest.', 'ref' => 'Matthew 11:28'],
                ['text' => 'Peace I leave with you; my peace I give you.', 'ref' => 'John 14:27'],
            ]), 'group' => 'faith'],
            ['key' => 'faith_comfort_kicker', 'value' => 'Sanctuary for believers', 'group' => 'faith'],
            ['key' => 'faith_comfort_heading', 'value' => 'Rest for the soul', 'group' => 'faith'],
            ['key' => 'faith_comfort_subheading', 'value' => 'Gentle reminders of Christ’s presence — for worship, prayer, and daily faith', 'group' => 'faith'],
            ['key' => 'faith_comfort_cards', 'value' => json_encode([
                ['icon' => '🕊', 'title' => 'Peace in Christ', 'text' => 'His peace guards heart and mind as you draw near in worship and prayer.', 'ref' => 'Philippians 4:7'],
                ['icon' => '🙏', 'title' => 'Rest in Prayer', 'text' => 'Bring every burden to the Lord — our parish family prays with you.', 'ref' => 'Matthew 11:28', 'link' => '/prayer-request', 'linkLabel' => 'Submit a prayer request'],
                ['icon' => '📖', 'title' => 'Hope in Scripture', 'text' => 'Holy Scripture nourishes faith — through preaching, reading, and Holy Communion.', 'ref' => 'Romans 15:4', 'link' => '/sermons', 'linkLabel' => 'Listen to a sermon'],
            ]), 'group' => 'faith'],
            ['key' => 'contact_office_heading', 'value' => 'Parish Office', 'group' => 'contact'],
            ['key' => 'contact_office_intro', 'value' => 'Questions about worship, Holy Communion, prayer, or joining our parish family — we would love to hear from you.', 'group' => 'contact'],
            ['key' => 'contact_form_heading', 'value' => 'Send a Message', 'group' => 'contact'],
            ['key' => 'contact_form_intro', 'value' => 'Whether you need pastoral support, information about Holy Communion, or wish to join our parish — write to us and we will respond as soon as we can.', 'group' => 'contact'],
            ['key' => 'site_announcement_enabled', 'value' => '0', 'group' => 'general'],
            ['key' => 'site_announcement_text', 'value' => '', 'group' => 'general'],
            ['key' => 'site_announcement_link', 'value' => '', 'group' => 'general'],
            ['key' => 'site_announcement_link_label', 'value' => 'Learn more', 'group' => 'general'],
            ['key' => 'service_times_heading', 'value' => 'Service Times', 'group' => 'general'],
            ['key' => 'service_times_intro', 'value' => 'Find Holy Communion and worship across our UK parish locations.', 'group' => 'general'],
            ['key' => 'events_list_heading', 'value' => 'Upcoming Events', 'group' => 'general'],
            ['key' => 'events_list_intro', 'value' => 'Gatherings, fellowship, and parish life across the United Kingdom.', 'group' => 'general'],
            ['key' => 'sermons_list_heading', 'value' => 'Sermons & Preaching', 'group' => 'general'],
            ['key' => 'sermons_list_intro', 'value' => 'Expository preaching rooted in Holy Scripture.', 'group' => 'general'],
            ['key' => 'prayer_request_heading', 'value' => 'Prayer Request', 'group' => 'general'],
            ['key' => 'prayer_request_intro', 'value' => 'Share your need confidentially — our parish family will pray with you.', 'group' => 'general'],
            ['key' => 'give_button_label', 'value' => 'Give', 'group' => 'general'],
            ['key' => 'give_page_intro', 'value' => 'Support the work of the gospel through faithful giving.', 'group' => 'general'],
            ['key' => 'footer_tagline', 'value' => 'Confessing Christ as Lord — Word, worship, and witness.', 'group' => 'general'],
            ['key' => 'footer_copyright', 'value' => '', 'group' => 'general'],
            ['key' => 'mail_use_admin_smtp', 'value' => '0', 'group' => 'mail'],
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
