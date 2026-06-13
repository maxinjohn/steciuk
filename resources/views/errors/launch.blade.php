@php
    $siteName = \App\Models\Setting::get('site_name', config('app.name'));
    $theme = $theme ?? 'parish';
    $isRibbonLaunch = ($launchStyle ?? 'countdown') === 'ribbon';
    $isCeremony = $showRibbonCeremony ?? false;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme-color-light="#d4cabb" data-theme-color-dark="#131316">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#d4cabb">
    <title>{{ $title }} · {{ $siteName }}</title>
    <script>
        (function () {
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/launch-countdown.js'])
</head>
<body class="site-body @unless($isCeremony) site-mesh @endunless launch-body">
    <main
        class="launch-page launch-page--theme-{{ $theme }} @if ($isRibbonLaunch) launch-page--ribbon-mode @endif @if ($isCeremony) launch-page--ceremony @endif"
        tabindex="-1"
        data-launch-phase="{{ $phase ?? 'countdown' }}"
        data-launch-style="{{ $launchStyle ?? 'countdown' }}"
        @if ($showCountdown && $countdownAt)
            data-countdown-at="{{ $countdownAt }}"
        @endif
    >
        <div class="launch-page__fx" aria-hidden="true">
            <span class="launch-page__spark launch-page__spark--one"></span>
            <span class="launch-page__spark launch-page__spark--two"></span>
            <span class="launch-page__spark launch-page__spark--three"></span>
            <span class="launch-page__spark launch-page__spark--four"></span>
        </div>

        <div class="launch-page__toolbar">
            <button
                type="button"
                class="launch-toolbar-btn"
                data-launch-fullscreen
                aria-pressed="false"
                aria-label="Full screen"
                title="Full screen (Esc to exit)"
            >
                <svg class="launch-toolbar-btn__icon" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="M3 7V3h4M13 3h4v4M17 13v4h-4M7 17H3v-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        @if ($isCeremony)
            <article
                class="launch-ribbon-screen"
                data-launch-ribbon-ceremony
                data-splash-type="{{ $splashType ?? 'site' }}"
                data-launch-url="{{ $launchUrl ?? url('/') }}"
            >
                <div class="launch-ribbon-screen__scanlines" aria-hidden="true"></div>

                <header class="launch-ribbon-screen__header">
                    <p class="launch-ribbon-screen__kicker">{{ $subtitle ?: 'Opening ceremony' }}</p>
                    @if ($eventName)
                        <p class="launch-ribbon-screen__event">{{ $eventName }}</p>
                    @endif
                </header>

                <div class="launch-ribbon-screen__band" aria-hidden="true">
                    <div class="launch-ribbon-screen__flash"></div>
                    <div class="launch-ribbon-screen__burst launch-ribbon-screen__burst--one"></div>
                    <div class="launch-ribbon-screen__burst launch-ribbon-screen__burst--two"></div>
                    <div class="launch-ribbon-screen__band-inner">
                        <span class="launch-ribbon-screen__rope launch-ribbon-screen__rope--left"></span>
                        <span class="launch-ribbon-screen__knot"></span>
                        <span class="launch-ribbon-screen__scissors">
                            <svg viewBox="0 0 64 64" focusable="false" aria-hidden="true">
                                <path d="M8 44 L28 24 L18 14 L38 34 Z" fill="currentColor"/>
                                <circle cx="14" cy="48" r="8" fill="none" stroke="currentColor" stroke-width="4"/>
                                <circle cx="30" cy="48" r="8" fill="none" stroke="currentColor" stroke-width="4"/>
                                <path d="M56 8 L36 28 L46 38 L26 18 Z" fill="currentColor"/>
                            </svg>
                        </span>
                        <span class="launch-ribbon-screen__knot"></span>
                        <span class="launch-ribbon-screen__rope launch-ribbon-screen__rope--right"></span>
                    </div>
                </div>

                <div class="launch-ribbon-screen__content">
                    <h1 class="launch-ribbon-screen__title">{{ $title }}</h1>
                    <p class="launch-ribbon-screen__copy">{{ $message }}</p>

                    @if ($scope === 'path' && $targetPath)
                        <p class="launch-ribbon-screen__path">/{{ $targetPath }}</p>
                    @endif

                    @if ($showAdminRibbon ?? false)
                        <form method="post" action="{{ $ribbonUrl }}" class="launch-ribbon-screen__form" data-launch-ribbon-form>
                            @csrf
                            <input type="hidden" name="gate_id" value="{{ $gateId }}">
                            <button type="submit" class="launch-ribbon-screen__cut" data-launch-ribbon-cut>
                                <span class="launch-ribbon-screen__cut-icon" aria-hidden="true">✂️</span>
                                <span class="launch-ribbon-screen__cut-text">Cut the ribbon</span>
                            </button>
                        </form>
                    @else
                        <p class="launch-ribbon-screen__wait">This page launches when a parish team member cuts the ribbon. Sign in below if you are on the launch team.</p>
                    @endif
                </div>

                <footer class="launch-ribbon-screen__footer">
                    <p class="launch-admin-link">
                        Parish team?
                        <a href="{{ $settingsUrl }}">Manage launch</a>
                        ·
                        <a href="{{ $adminUrl }}">Admin sign-in</a>
                    </p>
                </footer>

                <div class="launch-ribbon-screen__confetti" aria-hidden="true"></div>
                <div class="launch-ribbon-screen__confetti launch-ribbon-screen__confetti--burst" aria-hidden="true"></div>

                <div class="launch-splash launch-splash--{{ $splashType ?? 'site' }}" data-launch-splash hidden>
                    <div class="launch-splash__halo" aria-hidden="true"></div>
                    <div class="launch-splash__inner">
                        @if (($splashType ?? 'site') === 'site')
                            <div class="launch-splash__logo-stage">
                                <img
                                    src="{{ $splashLogoUrl ?? '/images/branding/steci-parish-logo.png' }}"
                                    alt="{{ $splashSiteName ?? $siteName }}"
                                    class="launch-splash__logo"
                                    width="240"
                                    height="280"
                                    decoding="async"
                                >
                            </div>
                            <p class="launch-splash__kicker">{{ $splashKicker ?? 'Welcome' }}</p>
                            <h2 class="launch-splash__title">{{ $splashSiteName ?? $siteName }}</h2>
                            <p class="launch-splash__copy">The parish site is now live.</p>
                        @else
                            <p class="launch-splash__kicker">{{ $splashKicker ?? 'Now live' }}</p>
                            <h2 class="launch-splash__title">{{ $splashPageTitle ?? $title }}</h2>
                            @if ($targetPath ?? false)
                                <p class="launch-splash__path">/{{ $targetPath }}</p>
                            @endif
                            <p class="launch-splash__copy">Opening this page for everyone.</p>
                        @endif
                        <div class="launch-splash__progress" aria-hidden="true">
                            <span class="launch-splash__progress-bar"></span>
                        </div>
                    </div>
                </div>
            </article>
        @else
            <article class="launch-shell">
                <div class="launch-card">
                    <div class="launch-orbit launch-orbit--one" aria-hidden="true"></div>
                    <div class="launch-orbit launch-orbit--two" aria-hidden="true"></div>

                    <div class="launch-badge">
                        <span class="launch-badge-dot" aria-hidden="true"></span>
                        {{ $subtitle }}
                    </div>

                    @if ($eventName)
                        <p class="launch-event-name">{{ $eventName }}</p>
                    @endif

                    <h1 class="launch-title">{{ $title }}</h1>
                    <p class="launch-message">{{ $message }}</p>

                    @if ($showCountdown && $countdownAt)
                        <div class="launch-countdown" data-launch-countdown role="timer" aria-live="polite">
                            <div class="launch-countdown__unit">
                                <span class="launch-countdown__value" data-unit="days">00</span>
                                <span class="launch-countdown__label">Days</span>
                            </div>
                            <div class="launch-countdown__unit">
                                <span class="launch-countdown__value" data-unit="hours">00</span>
                                <span class="launch-countdown__label">Hours</span>
                            </div>
                            <div class="launch-countdown__unit">
                                <span class="launch-countdown__value" data-unit="minutes">00</span>
                                <span class="launch-countdown__label">Mins</span>
                            </div>
                            <div class="launch-countdown__unit launch-countdown__unit--pulse">
                                <span class="launch-countdown__value" data-unit="seconds">00</span>
                                <span class="launch-countdown__label">Secs</span>
                            </div>
                        </div>
                    @endif

                    <div class="launch-chips" aria-hidden="true">
                        <span class="launch-chip">UK parish</span>
                        <span class="launch-chip">Worship continues</span>
                        @if ($scope === 'path' && $targetPath)
                            <span class="launch-chip">{{ $targetPath }}</span>
                        @else
                            <span class="launch-chip">Coming soon</span>
                        @endif
                    </div>

                    <div class="launch-actions">
                        <a href="{{ url('/service-times') }}" class="btn btn-primary">Service times</a>
                        <a href="mailto:{{ \App\Models\Setting::get('contact_email', 'admin@steciuk.org') }}" class="btn btn-secondary">Email the parish</a>
                    </div>

                    @if ($verse)
                        <blockquote class="launch-verse">
                            {{ $verse }}
                            @if ($verseRef)
                                <cite>{{ $verseRef }}</cite>
                            @endif
                        </blockquote>
                    @endif

                    <p class="launch-admin-link">
                        Parish team?
                        <a href="{{ $settingsUrl }}">Manage launch</a>
                        ·
                        <a href="{{ $adminUrl }}">Admin sign-in</a>
                    </p>
                </div>
            </article>
        @endif
    </main>
</body>
</html>
