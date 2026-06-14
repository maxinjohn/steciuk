<?php

namespace App\Support;

use App\Filament\Pages\ChurchSettings;
use App\Filament\Pages\FaithComfortSettings;
use App\Filament\Pages\EmailTemplatesSettings;
use App\Filament\Pages\GivingSettings;
use App\Filament\Pages\MailSettings;
use App\Filament\Pages\PublicExperienceSettings;
use App\Filament\Pages\SiteContentSettings;
use App\Filament\Pages\SiteLaunchSettings;
use App\Filament\Pages\SiteMaintenanceSettings;
use App\Filament\Resources\Conversations\ConversationResource;
use App\Filament\Resources\Donations\DonationResource;
use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Families\FamilyResource;
use App\Filament\Resources\FormSubmissions\FormSubmissionResource;
use App\Filament\Resources\GalleryAlbums\GalleryAlbumResource;
use App\Filament\Resources\GalleryPhotos\GalleryPhotoResource;
use App\Filament\Resources\MenuItems\MenuItemResource;
use App\Filament\Resources\Ministries\MinistryResource;
use App\Filament\Resources\News\NewsResource;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Resources\ResourceResource;
use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\SecurityAuditLogs\SecurityAuditLogResource;
use App\Filament\Resources\Sermons\SermonResource;
use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Resources\Users\UserResource;
use Filament\Pages\Page;
use Filament\Resources\Resource;

class AdminQuickLinks
{
    /**
     * @return list<array{group: string, hint: string, items: list<array{label: string, url: string, desc: string}>}>
     */
    public static function sections(): array
    {
        $sections = [
            [
                'group' => 'People & households',
                'hint' => 'Members and family records',
                'items' => [
                    self::resourceLink('Members', UserResource::class, 'Accounts, roles, and member profiles'),
                    self::resourceLink('Families', FamilyResource::class, 'Households, admins, and family members'),
                ],
            ],
            [
                'group' => 'Giving & donations',
                'hint' => 'Recorded gifts and monthly totals',
                'items' => [
                    self::pageLink('Giving page & bank details', GivingSettings::class, 'Public /give page copy and parish bank account'),
                    self::resourceLink('Donations', DonationResource::class, 'View, verify, export, and manage giving records'),
                ],
            ],
            [
                'group' => 'Messages & forms',
                'hint' => 'Conversations, contact submissions, and enquiries',
                'items' => [
                    self::resourceLink('Inbox', ConversationResource::class, 'Member messages and parish conversations'),
                    self::resourceLink('Form log', FormSubmissionResource::class, 'Contact, prayer, volunteer, and visitor form records'),
                ],
            ],
            [
                'group' => 'Website content',
                'hint' => 'Pages, menus, news, downloads',
                'items' => [
                    self::resourceLink('Pages', PageResource::class, 'Welcome, contact, about, and custom pages'),
                    self::resourceLink('Menus & links', MenuItemResource::class, 'Header, footer, and mobile navigation'),
                    self::resourceLink('News', NewsResource::class, 'Parish announcements and articles'),
                    self::resourceLink('Downloads', ResourceResource::class, 'PDFs, forms, and parish resource files'),
                ],
            ],
            [
                'group' => 'Worship & parish',
                'hint' => 'Services, sermons, events, ministries',
                'items' => [
                    self::resourceLink('Worship services', ServiceResource::class, 'UK worship locations and schedules'),
                    self::resourceLink('Events', EventResource::class, 'Upcoming parish gatherings'),
                    self::resourceLink('Ministries', MinistryResource::class, 'Sunday school, youth, prayer groups'),
                    self::resourceLink('Sermons', SermonResource::class, 'Audio, video, and sermon archive'),
                ],
            ],
            [
                'group' => 'Photos & media',
                'hint' => 'Gallery albums and photo library',
                'items' => [
                    self::resourceLink('Gallery albums', GalleryAlbumResource::class, 'Public photo collections and cover images'),
                    self::resourceLink('Gallery photos', GalleryPhotoResource::class, 'Upload and organise parish photos'),
                ],
            ],
            [
                'group' => 'Site settings',
                'hint' => 'Church identity, public copy, and email',
                'items' => [
                    self::pageLink('Church & faith', ChurchSettings::class, 'Parish name, contact, SEO, gospel bar, admin copy'),
                    self::pageLink('Faith & comfort', FaithComfortSettings::class, 'Rotating Scripture, sanctuary ribbon, comfort cards'),
                    self::pageLink('Public experience', PublicExperienceSettings::class, 'Spark strip, action cards, whispers, Gen Z toggles'),
                    self::pageLink('Maintenance mode', SiteMaintenanceSettings::class, 'Public maintenance page for site refreshes'),
                    self::pageLink('Launch countdown', SiteLaunchSettings::class, 'Pre-launch countdown for the site or a specific URL'),
                    self::pageLink('Public site copy', SiteContentSettings::class, 'Announcements, listings, prayer & giving text'),
                    self::pageLink('Email setup', MailSettings::class, 'Contact form delivery and test email'),
                    self::pageLink('Email templates', EmailTemplatesSettings::class, 'Approval, welcome, and parish notification emails'),
                ],
            ],
            [
                'group' => 'Security & access',
                'hint' => 'Roles and audit trail',
                'items' => [
                    self::resourceLink('Roles', RoleResource::class, 'Create custom roles and edit privileges'),
                    self::resourceLink('Activity log', SecurityAuditLogResource::class, 'Logins, admin actions, and security events'),
                ],
            ],
        ];

        return collect($sections)
            ->map(function (array $section): ?array {
                $items = collect($section['items'])->filter()->values()->all();

                if ($items === []) {
                    return null;
                }

                $section['items'] = $items;

                return $section;
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  class-string<Resource>  $resourceClass
     * @return array{label: string, url: string, desc: string}|null
     */
    private static function resourceLink(string $label, string $resourceClass, string $desc): ?array
    {
        if (! $resourceClass::canViewAny()) {
            return null;
        }

        return [
            'label' => $label,
            'url' => $resourceClass::getUrl('index'),
            'desc' => $desc,
        ];
    }

    /**
     * @param  class-string<Page>  $pageClass
     * @return array{label: string, url: string, desc: string}|null
     */
    private static function pageLink(string $label, string $pageClass, string $desc): ?array
    {
        if (! $pageClass::canAccess()) {
            return null;
        }

        return [
            'label' => $label,
            'url' => $pageClass::getUrl(),
            'desc' => $desc,
        ];
    }
}
