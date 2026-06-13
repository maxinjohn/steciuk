@php
    $data = $data ?? \App\Services\MaintenanceModeService::viewData();
    $theme = $data['theme'] ?? 'parish';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme-color-light="#d4cabb" data-theme-color-dark="#131316">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#d4cabb">
    <title>{{ $data['title'] }} · {{ $data['siteName'] }}</title>
    <script>
        (function () {
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/maintenance-page.js'])
</head>
<body class="site-body site-mesh maintenance-body">
    <main
        class="maintenance-page maintenance-page--theme-{{ $theme }}"
        tabindex="-1"
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
                data-maintenance-fullscreen
                aria-pressed="false"
                aria-label="Full screen"
                title="Full screen (Esc to exit)"
            >
                <svg class="launch-toolbar-btn__icon" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="M3 7V3h4M13 3h4v4M17 13v4h-4M7 17H3v-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        <article class="launch-shell">
            <div class="launch-card">
                <div class="launch-orbit launch-orbit--one" aria-hidden="true"></div>
                <div class="launch-orbit launch-orbit--two" aria-hidden="true"></div>

                <div class="launch-badge">
                    <span class="launch-badge-dot" aria-hidden="true"></span>
                    {{ $data['badge'] }}
                </div>

                @if (($data['scope'] ?? 'site') === 'path' && ($data['targetPath'] ?? '') !== '')
                    <p class="launch-event-name">/{{ $data['targetPath'] }}</p>
                @endif

                <h1 class="launch-title">{{ $data['title'] }}</h1>
                <p class="launch-message">{{ $data['message'] }}</p>

                <div class="maintenance-progress" role="presentation" aria-hidden="true">
                    <span class="maintenance-progress__bar"></span>
                </div>

                @if ($data['chips'] !== [])
                    <div class="launch-chips" aria-hidden="true">
                        @foreach ($data['chips'] as $chip)
                            <span class="launch-chip">{{ $chip }}</span>
                        @endforeach
                    </div>
                @endif

                @if ($data['showServiceTimes'] || $data['showEmail'])
                    <div class="launch-actions">
                        @if ($data['showServiceTimes'] && $data['serviceTimesUrl'])
                            <a href="{{ $data['serviceTimesUrl'] }}" class="btn btn-primary">{{ $data['serviceTimesLabel'] }}</a>
                        @endif
                        @if ($data['showEmail'] && $data['contactEmail'])
                            <a href="mailto:{{ $data['contactEmail'] }}" class="btn btn-secondary">Email the parish</a>
                        @endif
                    </div>
                @endif

                @if ($data['verse'])
                    <blockquote class="launch-verse">
                        {{ $data['verse'] }}
                        @if ($data['verseRef'])
                            <cite>{{ $data['verseRef'] }}</cite>
                        @endif
                    </blockquote>
                @endif

                <p class="launch-admin-link">
                    Parish team? <a href="{{ $data['adminUrl'] }}">Sign in to admin</a>
                </p>
            </div>
        </article>
    </main>
</body>
</html>
