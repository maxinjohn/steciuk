@if (file_exists(public_path('build/manifest.json')))
    @if (filament()->auth()->check())
        <meta name="admin-session-timeout-minutes" content="{{ \App\Support\AdminSecurityConfig::sessionLifetimeMinutes() }}">
    @endif

    @vite('resources/js/admin-session.js')

    @if (filament()->auth()->check())
        @vite('resources/js/admin-sidebar.js')
    @endif
@endif
