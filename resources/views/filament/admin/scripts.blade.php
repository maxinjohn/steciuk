@if (file_exists(public_path('build/manifest.json')) && filament()->auth()->check())
    @vite('resources/js/admin-sidebar.js')
@endif
