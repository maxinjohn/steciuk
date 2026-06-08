<?php

namespace App\Providers;

use App\Enums\MenuLocation;
use App\Models\Setting;
use App\Services\MenuCache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('*', function ($view): void {
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
                    'donationLink' => $settings['donation_link'] ?? null,
                    'footerText' => $settings['footer_text'] ?? null,
                    'seoDefaultTitle' => $settings['seo_default_title'] ?? null,
                    'seoDefaultDescription' => $settings['seo_default_description'] ?? null,
                    'googleMapsEmbed' => $settings['google_maps_embed'] ?? null,
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
