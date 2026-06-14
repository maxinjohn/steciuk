<?php

namespace App\Support;

use App\Enums\MenuLocation;
use App\Models\Page;
use App\Models\Setting;
use App\Services\MenuCache;
use App\Services\ServiceLocations;
use App\Support\NextWorshipChip;
use Illuminate\Support\Collection;

class SiteLayoutData
{
    /**
     * @var array<string, mixed>|null
     */
    private static ?array $resolved = null;

    /**
     * @return array<string, mixed>
     */
    public static function resolve(): array
    {
        if (self::$resolved !== null) {
            return self::$resolved;
        }

        $settings = Setting::allValues();
        $menus = MenuCache::loadAll();

        self::$resolved = [
            'siteName' => $settings['church_name'] ?? 'St. Thomas Evangelical Church of India – UK Parish',
            'siteMotto' => $settings['motto'] ?? 'For the Word of God and for the testimony of Jesus Christ',
            'siteEmail' => $settings['contact_email'] ?? null,
            'sitePhone' => $settings['phone'] ?? null,
            'siteAddress' => UkAddressFormatter::fromSettings($settings),
            'siteAddressSchema' => UkAddressFormatter::schemaOrgFromSettings($settings),
            'charityNumber' => $settings['charity_number'] ?? null,
            'siteLogo' => $settings['logo'] ?? null,
            'siteFavicon' => $settings['favicon'] ?? null,
            'socialYoutube' => $settings['youtube'] ?? $settings['youtube_url'] ?? null,
            'socialFacebook' => $settings['facebook'] ?? $settings['facebook_url'] ?? null,
            'socialInstagram' => $settings['instagram'] ?? $settings['instagram_url'] ?? null,
            'socialTwitter' => $settings['twitter'] ?? $settings['twitter_url'] ?? null,
            'donationLink' => GivingUrl::resolve($settings['donation_link'] ?? null),
            'givePageUrl' => GivingUrl::route(),
            'showGiveButton' => GivingUrl::isEnabled($settings['donation_link'] ?? null),
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
            'footerAboutTagline' => self::footerAboutTagline(
                $settings['footer_tagline'] ?? null,
                $settings['motto'] ?? 'For the Word of God and for the testimony of Jesus Christ',
            ),
            'googleMapsEmbed' => $settings['google_maps_embed'] ?? null,
            'faithSanctuaryRibbons' => FaithContent::sanctuaryRibbons(),
            'faithSanctuaryVerses' => FaithContent::sanctuaryVerses(),
            'faithComfortHeaders' => FaithContent::comfortHeaders(),
            'faithComfortCards' => FaithContent::comfortCards(),
            'contactOfficeHeading' => $settings['contact_office_heading'] ?? 'Parish Office',
            'contactOfficeIntro' => $settings['contact_office_intro'] ?? 'Questions about worship, Holy Communion, prayer, or joining our parish family — we would love to hear from you.',
            'contactFormHeading' => $settings['contact_form_heading'] ?? 'Send a Message',
            'contactFormIntro' => $settings['contact_form_intro'] ?? 'Whether you need pastoral support, information about Holy Communion, or wish to join our parish — write to us and we will respond as soon as we can.',
            'serviceLocations' => ServiceLocations::names(),
            'nextWorshipChip' => NextWorshipChip::resolve(),
            'needsLivewire' => self::needsLivewire(),
            'headerMenu' => self::withoutMemberAreaMenu($menus[MenuLocation::Header->value]),
            'footerMenu' => $menus[MenuLocation::Footer->value],
            'mobileMenu' => $menus[MenuLocation::Mobile->value],
            'navMenu' => self::navMenu(
                $menus[MenuLocation::Mobile->value],
                $menus[MenuLocation::Header->value],
            ),
            'mobileDrawerMenu' => self::mobileDrawerMenu(
                self::navMenu(
                    $menus[MenuLocation::Mobile->value],
                    $menus[MenuLocation::Header->value],
                ),
            ),
        ];

        return self::$resolved;
    }

    public static function needsLivewire(): bool
    {
        $request = request();

        if ($request->routeIs(
            'home',
            'login',
            'register',
            'password.request',
            'password.reset',
            'registration.pending',
            'account',
            'account.giving.export',
            'events.show',
            'ministries.show',
        )) {
            return true;
        }

        if ($request->routeIs('pages.show')) {
            $slug = (string) $request->route('slug', '');

            return Page::query()
                ->where('slug', $slug)
                ->whereIn('template', ['contact', 'form'])
                ->exists();
        }

        return false;
    }

    public static function forget(): void
    {
        self::$resolved = null;
    }

    public static function navMenu(Collection $mobileMenu, Collection $headerMenu): Collection
    {
        return $mobileMenu->isNotEmpty() ? $mobileMenu : $headerMenu;
    }

    public static function mobileDrawerMenu(Collection $navMenu): Collection
    {
        return self::withoutMemberAreaMenu($navMenu);
    }

    public static function withoutMemberAreaMenu(Collection $menu): Collection
    {
        return $menu
            ->reject(fn ($item) => self::isMemberAreaMenuItem($item))
            ->values();
    }

    public static function isMemberAreaMenuItem(object $item): bool
    {
        $seedKey = (string) ($item->seed_key ?? '');

        if ($seedKey === 'member-area' || str_starts_with($seedKey, 'members.')) {
            return true;
        }

        return strcasecmp(trim((string) ($item->label ?? '')), 'Member area') === 0;
    }

    public static function footerAboutTagline(?string $tagline, ?string $motto): string
    {
        $fallback = 'Word, worship, and witness across the United Kingdom.';
        $about = trim((string) ($tagline ?: $fallback));

        $motto = trim((string) $motto);
        if ($motto !== '' && str_starts_with($about, $motto)) {
            $about = trim(substr($about, strlen($motto)));
            $about = ltrim($about, " \t\n\r\0\x0B—–-");
        }

        return $about !== '' ? $about : $fallback;
    }
}
