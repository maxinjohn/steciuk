@php
    $links = [
        [
            'group' => 'Website Content',
            'hint' => 'Pages, menus, news, downloads',
            'items' => [
                ['label' => 'Edit Pages', 'url' => \App\Filament\Resources\Pages\PageResource::getUrl('index'), 'desc' => 'Update welcome, contact, about, and custom pages'],
                ['label' => 'Menu Items', 'url' => \App\Filament\Resources\MenuItems\MenuItemResource::getUrl('index'), 'desc' => 'Header, footer, and mobile navigation'],
                ['label' => 'News', 'url' => \App\Filament\Resources\News\NewsResource::getUrl('index'), 'desc' => 'Parish announcements and articles'],
            ],
        ],
        [
            'group' => 'Worship & Parish',
            'hint' => 'Services, sermons, events, ministries',
            'items' => [
                ['label' => 'Service Times', 'url' => \App\Filament\Resources\Services\ServiceResource::getUrl('index'), 'desc' => 'UK worship locations and schedules'],
                ['label' => 'Events', 'url' => \App\Filament\Resources\Events\EventResource::getUrl('index'), 'desc' => 'Upcoming parish gatherings'],
                ['label' => 'Ministries', 'url' => \App\Filament\Resources\Ministries\MinistryResource::getUrl('index'), 'desc' => 'Sunday school, youth, prayer groups'],
            ],
        ],
        [
            'group' => 'Site Settings',
            'hint' => 'Church identity, faith copy, email, roles',
            'items' => [
                ['label' => 'Church Settings', 'url' => \App\Filament\Pages\ChurchSettings::getUrl(), 'desc' => 'Name, contact, faith comfort text, footer'],
                ['label' => 'Public Site Copy', 'url' => \App\Filament\Pages\SiteContentSettings::getUrl(), 'desc' => 'Announcements, listings, prayer & giving text'],
                ['label' => 'Roles & Permissions', 'url' => \App\Filament\Resources\Roles\RoleResource::getUrl('index'), 'desc' => 'Create custom roles and edit privileges'],
                ['label' => 'Email & SMTP', 'url' => \App\Filament\Pages\MailSettings::getUrl(), 'desc' => 'Contact form delivery and test email'],
            ],
        ],
    ];
@endphp

<x-filament-widgets::widget>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($links as $section)
            <div class="admin-quick-card rounded-2xl border border-stone-200/90 bg-white/95 p-5 shadow-md shadow-stone-900/5 dark:border-white/10 dark:bg-slate-900/70 dark:shadow-black/20">
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-800 dark:text-amber-400">{{ $section['group'] }}</p>
                <p class="mt-1 text-xs text-stone-500 dark:text-slate-400">{{ $section['hint'] }}</p>
                <ul class="mt-4 space-y-3">
                    @foreach ($section['items'] as $item)
                        <li>
                            <a href="{{ $item['url'] }}" class="group block rounded-xl border border-transparent px-3 py-2 transition hover:border-amber-200 hover:bg-amber-50 dark:hover:border-amber-500/30 dark:hover:bg-amber-500/10">
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
