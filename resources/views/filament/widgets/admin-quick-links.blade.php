@php
    $links = [
        [
            'group' => 'People & households',
            'hint' => 'Members and family records',
            'items' => [
                ['label' => 'Members', 'url' => \App\Filament\Resources\Users\UserResource::getUrl('index'), 'desc' => 'Accounts, roles, and member profiles'],
                ['label' => 'Families', 'url' => \App\Filament\Resources\Families\FamilyResource::getUrl('index'), 'desc' => 'Households, admins, and family members'],
            ],
        ],
        [
            'group' => 'Giving & donations',
            'hint' => 'Recorded gifts and monthly totals',
            'items' => [
                ['label' => 'Donations', 'url' => \App\Filament\Resources\Donations\DonationResource::getUrl('index'), 'desc' => 'View, edit, export, and manage giving records'],
            ],
        ],
        [
            'group' => 'Messages & forms',
            'hint' => 'Contact submissions and enquiries',
            'items' => [
                ['label' => 'Inbox', 'url' => \App\Filament\Resources\FormSubmissions\FormSubmissionResource::getUrl('index'), 'desc' => 'Contact form messages and visitor enquiries'],
            ],
        ],
        [
            'group' => 'Website content',
            'hint' => 'Pages, menus, news, downloads',
            'items' => [
                ['label' => 'Pages', 'url' => \App\Filament\Resources\Pages\PageResource::getUrl('index'), 'desc' => 'Welcome, contact, about, and custom pages'],
                ['label' => 'Menus & links', 'url' => \App\Filament\Resources\MenuItems\MenuItemResource::getUrl('index'), 'desc' => 'Header, footer, and mobile navigation'],
                ['label' => 'News', 'url' => \App\Filament\Resources\News\NewsResource::getUrl('index'), 'desc' => 'Parish announcements and articles'],
                ['label' => 'Downloads', 'url' => \App\Filament\Resources\Resources\ResourceResource::getUrl('index'), 'desc' => 'PDFs, forms, and parish resource files'],
            ],
        ],
        [
            'group' => 'Worship & parish',
            'hint' => 'Services, sermons, events, ministries',
            'items' => [
                ['label' => 'Worship services', 'url' => \App\Filament\Resources\Services\ServiceResource::getUrl('index'), 'desc' => 'UK worship locations and schedules'],
                ['label' => 'Events', 'url' => \App\Filament\Resources\Events\EventResource::getUrl('index'), 'desc' => 'Upcoming parish gatherings'],
                ['label' => 'Ministries', 'url' => \App\Filament\Resources\Ministries\MinistryResource::getUrl('index'), 'desc' => 'Sunday school, youth, prayer groups'],
                ['label' => 'Sermons', 'url' => \App\Filament\Resources\Sermons\SermonResource::getUrl('index'), 'desc' => 'Audio, video, and sermon archive'],
            ],
        ],
        [
            'group' => 'Photos & media',
            'hint' => 'Gallery albums and photo library',
            'items' => [
                ['label' => 'Gallery albums', 'url' => \App\Filament\Resources\GalleryAlbums\GalleryAlbumResource::getUrl('index'), 'desc' => 'Public photo collections and cover images'],
                ['label' => 'Gallery photos', 'url' => \App\Filament\Resources\GalleryPhotos\GalleryPhotoResource::getUrl('index'), 'desc' => 'Upload and organise parish photos'],
            ],
        ],
        [
            'group' => 'Site settings',
            'hint' => 'Church identity, public copy, and email',
            'items' => [
                ['label' => 'Church & faith', 'url' => \App\Filament\Pages\ChurchSettings::getUrl(), 'desc' => 'Name, contact, faith comfort text, footer'],
                ['label' => 'Public site copy', 'url' => \App\Filament\Pages\SiteContentSettings::getUrl(), 'desc' => 'Announcements, listings, prayer & giving text'],
                ['label' => 'Email setup', 'url' => \App\Filament\Pages\MailSettings::getUrl(), 'desc' => 'Contact form delivery and test email'],
            ],
        ],
        [
            'group' => 'Security & access',
            'hint' => 'Roles and audit trail',
            'items' => [
                ['label' => 'Roles', 'url' => \App\Filament\Resources\Roles\RoleResource::getUrl('index'), 'desc' => 'Create custom roles and edit privileges'],
                ['label' => 'Activity log', 'url' => \App\Filament\Resources\SecurityAuditLogs\SecurityAuditLogResource::getUrl('index'), 'desc' => 'Logins, admin actions, and security events'],
            ],
        ],
    ];
@endphp

<x-filament-widgets::widget>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($links as $section)
            <div class="admin-quick-card rounded-2xl border border-stone-200/90 bg-white/95 p-4 shadow-md shadow-stone-900/5 sm:p-5 dark:border-white/10 dark:bg-slate-900/70 dark:shadow-black/20">
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-800 dark:text-amber-400">{{ $section['group'] }}</p>
                <p class="mt-1 text-xs text-stone-500 dark:text-slate-400">{{ $section['hint'] }}</p>
                <ul class="mt-3 space-y-2 sm:mt-4 sm:space-y-3">
                    @foreach ($section['items'] as $item)
                        <li>
                            <a href="{{ $item['url'] }}" class="group block min-h-11 rounded-xl border border-transparent px-3 py-2.5 transition hover:border-amber-200 hover:bg-amber-50 dark:hover:border-amber-500/30 dark:hover:bg-amber-500/10">
                                <span class="font-medium text-stone-900 group-hover:text-amber-900 dark:text-white dark:group-hover:text-amber-200">{{ $item['label'] }}</span>
                                <span class="mt-0.5 block text-xs leading-relaxed text-stone-500 dark:text-slate-400">{{ $item['desc'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</x-filament-widgets::widget>
