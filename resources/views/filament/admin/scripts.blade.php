@if (file_exists(public_path('build/manifest.json')))
    @vite('resources/js/admin-session.js')
    @vite('resources/js/admin-form-tabs.js')

    @if (filament()->auth()->check())
        @vite('resources/js/admin-sidebar.js')
    @endif
@endif
