<?php

namespace App\Providers;

use App\Enums\MenuLocation;
use App\Models\Setting;
use App\Services\MenuCache;
use App\Support\FaithContent;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('*', function ($view): void {
            if (str_starts_with($view->name(), 'filament.')) {
                return;
            }

            static $shared = null;

            if ($shared === null) {
                $settings = Setting::allValues();

                $shared = [
                    'siteName' => $settings['church_name'] ?? 'St. Thomas Evangelical Church of India – UK Parish',
                    'siteMotto' => $settings['motto'] ?? 'For the Word of God and for the testimony of Jesus Christ',
                    'siteEmail' => $settings['contact_email'] ?? null,
                    'sitePhone' => $settings['phone'] ?? null,
                    'siteAddress' => $settings['main_address'] ?? null,
                    'charityNumber' => $settings['charity_number'] ?? null,
                    'siteLogo' => $settings['logo'] ?? null,
                    'siteFavicon' => $settings['favicon'] ?? null,
                    'socialYoutube' => $settings['youtube'] ?? $settings['youtube_url'] ?? null,
                    'socialFacebook' => $settings['facebook'] ?? $settings['facebook_url'] ?? null,
                    'socialInstagram' => $settings['instagram'] ?? $settings['instagram_url'] ?? null,
                    'socialTwitter' => $settings['twitter'] ?? $settings['twitter_url'] ?? null,
                    'donationLink' => $settings['donation_link'] ?? null,
                    'footerText' => $settings['footer_text'] ?? null,
                    'seoDefaultTitle' => $settings['seo_default_title'] ?? null,
                    'seoDefaultDescription' => $settings['seo_default_description'] ?? null,
                    'seoDefaultOgImage' => $settings['seo_default_og_image'] ?? null,
                    'themeColor' => $settings['theme_color'] ?? '#d4cabb',
                    'pwaShortName' => $settings['pwa_short_name'] ?? 'STECI UK',
                    'gospelReminderKicker' => $settings['gospel_reminder_kicker'] ?? null,
                    'gospelReminderReference' => $settings['gospel_reminder_reference'] ?? 'Revelation 1:9',
                    'siteAnnouncementEnabled' => ($settings['site_announcement_enabled'] ?? '0') === '1',
                    'siteAnnouncementText' => $settings['site_announcement_text'] ?? null,
                    'siteAnnouncementLink' => $settings['site_announcement_link'] ?? null,
                    'siteAnnouncementLinkLabel' => $settings['site_announcement_link_label'] ?? 'Learn more',
                    'serviceTimesHeading' => $settings['service_times_heading'] ?? 'Service Times',
                    'serviceTimesIntro' => $settings['service_times_intro'] ?? null,
                    'giveButtonLabel' => $settings['give_button_label'] ?? 'Give',
                    'footerCopyright' => $settings['footer_copyright'] ?? null,
                    'footerTagline' => $settings['footer_tagline'] ?? null,
                    'googleMapsEmbed' => $settings['google_maps_embed'] ?? null,
                    'faithSanctuaryKicker' => $settings['faith_sanctuary_kicker'] ?? null,
                    'faithSanctuaryNote' => $settings['faith_sanctuary_note'] ?? null,
                    'faithSanctuaryVerses' => FaithContent::sanctuaryVerses(),
                    'faithComfortKicker' => $settings['faith_comfort_kicker'] ?? null,
                    'faithComfortHeading' => $settings['faith_comfort_heading'] ?? null,
                    'faithComfortSubheading' => $settings['faith_comfort_subheading'] ?? null,
                    'faithComfortCards' => FaithContent::comfortCards(),
                    'contactOfficeHeading' => $settings['contact_office_heading'] ?? 'Parish Office',
                    'contactOfficeIntro' => $settings['contact_office_intro'] ?? 'Questions about worship, Holy Communion, prayer, or joining our parish family — we would love to hear from you.',
                    'contactFormHeading' => $settings['contact_form_heading'] ?? 'Send a Message',
                    'contactFormIntro' => $settings['contact_form_intro'] ?? 'Whether you need pastoral support, information about Holy Communion, or wish to join our parish — write to us and we will respond as soon as we can.',
                    'serviceLocations' => \App\Services\ServiceLocations::names(),
                    'headerMenu' => MenuCache::load(MenuLocation::Header),
                    'footerMenu' => MenuCache::load(MenuLocation::Footer),
                    'mobileMenu' => MenuCache::load(MenuLocation::Mobile),
                ];
            }

            $view->with($shared);
        });
    }
}
