<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme-color-light="{{ $themeColor ?? '#d4cabb' }}" data-theme-color-dark="#131316">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, interactive-widget=resizes-content">
    <meta name="format-detection" content="telephone=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $ogImageOverride = trim($__env->yieldContent('og_image'));
        $ogType = trim($__env->yieldContent('og_type')) ?: 'website';
        $seoRobots = request()->routeIs(
            'login',
            'register',
            'password.request',
            'password.reset',
            'registration.pending',
            'account',
            'account.giving.export',
        ) ? 'noindex, nofollow' : null;
    @endphp
    <x-seo-meta :image="$ogImageOverride ?: null" :type="$ogType" :robots="$seoRobots" />

    @php
        $faviconUrl = \App\Models\Setting::assetUrl($siteFavicon ?? null) ?? asset('icons/favicon.svg');
    @endphp
    <link rel="icon" href="{{ $faviconUrl }}" type="image/svg+xml">

    <link rel="manifest" href="{{ route('manifest') }}">
    <meta name="theme-color" content="{{ $themeColor ?? '#d4cabb' }}">
    <script>
        (function () {
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ $pwaShortName ?? 'STECI UK' }}">
    @php
        $appleIcon = \App\Models\Setting::assetUrl($siteLogo ?? null)
            ?? asset(\App\Support\SiteBrandingAssets::BUNDLED_LOGO_PUBLIC);
    @endphp
    <link rel="apple-touch-icon" href="{{ $appleIcon }}">
    <meta name="mobile-web-app-capable" content="yes">

    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.bunny.net">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    @php
        $schema = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Church',
            'name' => $siteName,
            'description' => $siteMotto,
            'url' => url('/'),
            'email' => $siteEmail ?: null,
            'telephone' => $sitePhone ?: null,
            'address' => $siteAddressSchema ?? null,
            'identifier' => $charityNumber ?: null,
            'sameAs' => array_values(array_filter([
                $socialYoutube ?? null,
                $socialFacebook ?? null,
                $socialInstagram ?? null,
                $socialTwitter ?? null,
            ])) ?: null,
        ], fn ($v) => $v !== null && $v !== []);
    @endphp
    <script type="application/ld+json">
        {!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

    @stack('head')
</head>
<body @class([
    'site-body site-mesh has-mobile-dock min-h-screen flex flex-col min-[1300px]:pb-0',
    'is-home' => request()->routeIs('home'),
    'is-authenticated' => auth()->check(),
])>
    <div id="site-shell">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[100] focus:rounded-xl focus:bg-[var(--site-brand)] focus:px-4 focus:py-2 focus:text-white focus:shadow-lg">
            Skip to main content
        </a>

        <x-site-announcement />

        <header id="site-header" class="site-header sticky top-0 z-[200] pt-safe">
            <div class="site-header-inner">
                <a href="{{ route('home') }}" class="site-logo-link" aria-label="{{ $siteName }} — Home">
                    <x-site-logo />
                </a>

                <nav class="site-header-nav hidden min-[1300px]:flex min-[1300px]:min-w-0 min-[1300px]:items-center" aria-label="Main navigation">
                    <x-menu :items="$headerMenu" variant="desktop" />
                </nav>

                <div class="site-header-actions">
                    <button type="button" onclick="toggleDarkMode()" class="icon-btn" aria-label="Toggle dark mode">
                        <svg class="h-5 w-5 dark:hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>
                        <svg class="hidden h-5 w-5 dark:block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/></svg>
                    </button>

                    @if ($showGiveButton ?? filled($donationLink))
                        <x-button href="{{ $givePageUrl ?? route('give') }}" variant="primary" class="hidden !min-h-11 !w-auto !px-5 !py-2.5 !text-sm min-[1300px]:inline-flex">
                            {{ $giveButtonLabel ?? 'Give' }}
                        </x-button>
                    @else
                        <x-button href="{{ url('/service-times') }}" variant="primary" class="hidden !min-h-11 !w-auto !px-5 !py-2.5 !text-sm min-[1300px]:inline-flex">
                            Visit
                        </x-button>
                    @endif

                    @auth
                        <a href="{{ route('account') }}" class="site-member-chip-btn site-member-chip-btn--solo" aria-label="Account">
                            <svg class="site-member-chip-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                            </svg>
                            <span class="site-member-chip-label">Account</span>
                        </a>
                    @else
                        <x-site-member-chip class="hidden min-[1300px]:block" />
                    @endauth
                </div>
            </div>
        </header>

        <main id="main-content" class="flex-1 lg:pb-0">
            @yield('content')
        </main>

        <x-faith-spark-strip />
        <x-sanctuary-peace
            :kicker="$faithSanctuaryKicker ?? null"
            :note="$faithSanctuaryNote ?? null"
            :verses="$faithSanctuaryVerses ?? []"
        />
        <x-gospel-reminder />

        <footer class="site-footer lg:pb-0" aria-label="Site footer">
            <div class="site-content-shell mx-auto max-w-7xl py-12 lg:py-16">
                <div class="hidden gap-6 lg:grid lg:grid-cols-4 lg:gap-8">
                    <div>
                        <h2>About</h2>
                        <x-footer-about />
                    </div>
                    <div>
                        <h2>Quick Links</h2>
                        <div class="mt-3">
                            <x-menu :items="$footerMenu" variant="footer" />
                        </div>
                    </div>
                    <div>
                        <h2>Service Locations</h2>
                        <ul class="mt-3 space-y-2 text-sm text-[var(--site-footer-muted)]" role="list">
                            @forelse ($serviceLocations ?? [] as $location)
                                <li>{{ $location }}</li>
                            @empty
                                <li>Manchester</li>
                                <li>Leicester</li>
                                <li>Dartford</li>
                                <li>Sunderland</li>
                                <li>Bristol</li>
                            @endforelse
                        </ul>
                        <a href="{{ route('services.index') }}" class="site-footer-accent-link mt-4 inline-block text-sm transition hover:opacity-90">
                            View service times →
                        </a>
                    </div>
                    <div>
                        <h2>Contact</h2>
                        <ul class="mt-3 space-y-2 text-sm text-[var(--site-footer-muted)]" role="list">
                            @if ($siteAddress)
                                <li>{{ $siteAddress }}</li>
                            @endif
                            @if ($sitePhone)
                                <li><a href="tel:{{ preg_replace('/\s+/', '', $sitePhone) }}" class="transition hover:text-[var(--site-accent)]">{{ $sitePhone }}</a></li>
                            @endif
                            @if ($siteEmail)
                                <li><a href="mailto:{{ $siteEmail }}" class="break-all transition hover:text-[var(--site-accent)]">{{ $siteEmail }}</a></li>
                            @endif
                        </ul>
                        <div class="mt-4 flex gap-3">
                            @foreach ([
                                ['url' => $socialYoutube ?? null, 'label' => 'YouTube', 'path' => 'M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z'],
                                ['url' => $socialFacebook ?? null, 'label' => 'Facebook', 'path' => 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z'],
                                ['url' => $socialInstagram ?? null, 'label' => 'Instagram', 'path' => 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z'],
                            ] as $social)
                                @if ($social['url'])
                                    <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-[var(--site-footer-ink)] transition hover:bg-[var(--site-brand)] hover:text-white" aria-label="{{ $social['label'] }}">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $social['path'] }}"/></svg>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="space-y-2 lg:hidden" x-data="{ open: 'about' }">
                    @foreach ([
                        ['id' => 'about', 'title' => 'About', 'slot' => 'about'],
                        ['id' => 'links', 'title' => 'Quick Links', 'slot' => 'links'],
                        ['id' => 'locations', 'title' => 'Service Locations', 'slot' => 'locations'],
                        ['id' => 'contact', 'title' => 'Contact', 'slot' => 'contact'],
                    ] as $section)
                        <div class="overflow-hidden rounded-2xl bg-surface/5">
                            <button
                                type="button"
                                class="footer-accordion-btn"
                                @click="open = open === '{{ $section['id'] }}' ? null : '{{ $section['id'] }}'"
                                :aria-expanded="open === '{{ $section['id'] }}'"
                            >
                                {{ $section['title'] }}
                                <svg class="h-5 w-5 transition" :class="open === '{{ $section['id'] }}' && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                </svg>
                            </button>
                            <div
                                x-show="open === '{{ $section['id'] }}'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="border-t border-white/10 px-4 pb-4"
                                x-cloak
                            >
                                @if ($section['slot'] === 'about')
                                    <x-footer-about compact />
                                @elseif ($section['slot'] === 'links')
                                    <div class="pt-3">
                                        <x-menu :items="$footerMenu" variant="footer" />
                                    </div>
                                @elseif ($section['slot'] === 'locations')
                                    <ul class="space-y-2 pt-3 text-sm text-[var(--site-footer-muted)]" role="list">
                                        @forelse ($serviceLocations ?? [] as $location)
                                            <li>{{ $location }}</li>
                                        @empty
                                            <li>Manchester</li>
                                            <li>Leicester</li>
                                            <li>Dartford</li>
                                            <li>Sunderland</li>
                                            <li>Bristol</li>
                                        @endforelse
                                    </ul>
                                    <a href="{{ route('services.index') }}" class="site-footer-accent-link mt-3 inline-block text-sm">View service times →</a>
                                @elseif ($section['slot'] === 'contact')
                                    <ul class="space-y-2 pt-3 text-sm text-[var(--site-footer-muted)]" role="list">
                                        @if ($siteAddress)<li>{{ $siteAddress }}</li>@endif
                                        @if ($sitePhone)<li><a href="tel:{{ preg_replace('/\s+/', '', $sitePhone) }}" class="hover:text-[var(--site-accent)]">{{ $sitePhone }}</a></li>@endif
                                        @if ($siteEmail)<li><a href="mailto:{{ $siteEmail }}" class="break-all hover:text-[var(--site-accent)]">{{ $siteEmail }}</a></li>@endif
                                    </ul>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-10 border-t border-white/10 pt-8 text-center text-sm text-[var(--site-footer-muted)]">
                    <x-faith-pillars variant="footer" class="!py-0 !mb-8" />
                    <p class="text-xs uppercase tracking-[0.2em] text-[var(--site-footer-muted)]/70">Evangelical Oriental Protestant · Saint Thomas Christian heritage</p>
                    <p class="mt-6">&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
                </div>
            </div>
        </footer>

    </div>

    <div
        id="mobile-menu-overlay"
        class="mobile-overlay min-[1300px]:hidden"
        aria-hidden="true"
    ></div>

    <nav
        id="mobile-menu"
        class="mobile-sheet mobile-drawer min-[1300px]:hidden"
        aria-label="Mobile navigation"
        aria-hidden="true"
    >
        <div class="mobile-sheet-header mobile-drawer-header">
            <p class="mobile-drawer-title">Menu</p>
            <button type="button" id="mobile-menu-close" class="mobile-drawer-close icon-btn" aria-label="Close menu">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="mobile-sheet-body mobile-drawer-body">
            <x-menu :items="$mobileDrawerMenu" variant="mobile" />

            <div class="mobile-drawer-footer">
                @auth
                    <a href="{{ route('account') }}" data-close-mobile-menu class="mobile-drawer-cta">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        My account
                    </a>
                @else
                    <div class="mobile-drawer-auth">
                        <a href="{{ route('login') }}" data-close-mobile-menu class="mobile-drawer-auth-link">Sign in</a>
                        <a href="{{ route('register') }}" data-close-mobile-menu class="mobile-drawer-auth-link mobile-drawer-auth-link--primary">Join parish</a>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <div class="mobile-dock-wrap min-[1300px]:hidden">
        <nav class="mobile-dock" aria-label="Quick navigation">
        <a href="{{ route('home') }}" class="mobile-dock-item {{ request()->routeIs('home') ? 'is-active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
            Home
        </a>
        <a href="{{ url('/service-times') }}" class="mobile-dock-item {{ request()->is('service-times*', 'online-worship*', 'sermons*') ? 'is-active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Worship
        </a>
        <a href="{{ url('/events') }}" class="mobile-dock-item {{ request()->is('events*') ? 'is-active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
            Events
        </a>
        <button type="button" id="mobile-menu-toggle" class="mobile-dock-item mobile-dock-item--menu" aria-controls="mobile-menu" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
            Menu
        </button>
        </nav>
    </div>

    <div id="pwa-install-banner" class="pwa-install-banner" role="dialog" aria-label="Install app" hidden aria-hidden="true">
        <div class="pwa-install-inner">
            <div class="pwa-install-copy">
                <p class="font-bold text-ink">Install STECI UK</p>
                <p class="text-sm text-ink-muted">Add to your home screen for quick access</p>
            </div>
            <div class="pwa-install-actions">
                <button id="pwa-install-btn" type="button" class="btn btn-primary pwa-install-btn">Install</button>
                <button id="pwa-dismiss-btn" type="button" class="pwa-dismiss-btn" aria-label="Dismiss install prompt">Not now</button>
            </div>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')

    <style>[x-cloak]{display:none!important}</style>
</body>
</html>
