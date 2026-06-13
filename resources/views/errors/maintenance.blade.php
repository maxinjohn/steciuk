@php
    $data = $data ?? \App\Services\MaintenanceModeService::viewData();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme-color-light="#d4cabb" data-theme-color-dark="#131316">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#d4cabb">
    <title>Be right back · {{ $data['siteName'] }}</title>
    <script>
        (function () {
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css'])
</head>
<body class="site-body site-mesh">
    <main class="maintenance-page">
        <article class="maintenance-shell">
            <div class="maintenance-card">
                <div class="maintenance-badge">
                    <span class="maintenance-badge-dot" aria-hidden="true"></span>
                    {{ $data['badge'] }}
                </div>

                <h1 class="maintenance-title">{{ $data['title'] }}</h1>
                <p class="maintenance-message">{{ $data['message'] }}</p>

                <div class="maintenance-progress" role="presentation" aria-hidden="true">
                    <span class="maintenance-progress__bar"></span>
                </div>

                @if ($data['chips'] !== [])
                    <div class="maintenance-chips" aria-hidden="true">
                        @foreach ($data['chips'] as $chip)
                            <span class="maintenance-chip">{{ $chip }}</span>
                        @endforeach
                    </div>
                @endif

                @if ($data['showServiceTimes'] || $data['showEmail'])
                    <div class="maintenance-actions">
                        @if ($data['showServiceTimes'] && $data['serviceTimesUrl'])
                            <a href="{{ $data['serviceTimesUrl'] }}" class="btn btn-primary">{{ $data['serviceTimesLabel'] }}</a>
                        @endif
                        @if ($data['showEmail'] && $data['contactEmail'])
                            <a href="mailto:{{ $data['contactEmail'] }}" class="btn btn-secondary">Email the parish</a>
                        @endif
                    </div>
                @endif

                @if ($data['verse'])
                    <blockquote class="maintenance-verse">
                        {{ $data['verse'] }}
                        @if ($data['verseRef'])
                            <cite>{{ $data['verseRef'] }}</cite>
                        @endif
                    </blockquote>
                @endif

                <p class="maintenance-admin-link">
                    Parish team? <a href="{{ $data['adminUrl'] }}">Sign in to admin</a>
                </p>
            </div>
        </article>
    </main>
</body>
</html>
