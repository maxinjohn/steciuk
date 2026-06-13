@php
    use App\Models\Setting;
    use App\Support\AdminPanelConfig;

    $heading = Setting::text('admin_welcome_heading', 'Welcome - manage your parish with peace');
    $body = Setting::text(
        'admin_welcome_body',
        \App\Support\AdminMobileDock::mobileHint(),
    );
    $verse = Setting::text('admin_dashboard_verse', 'Be still, and know that I am God.');
    $verseRef = Setting::text('admin_dashboard_verse_ref', 'Psalm 46:10');
@endphp

<x-filament-widgets::widget>
    <div class="admin-sanctuary-banner">
        <div class="admin-sanctuary-glow" aria-hidden="true"></div>
        <div class="admin-sanctuary-banner__inner">
            <p class="admin-sanctuary-kicker">
                <span class="admin-sanctuary-kicker__icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M12 4v16M8 8h8"/>
                    </svg>
                </span>
                {{ AdminPanelConfig::name() }}
            </p>
            <h2 class="admin-sanctuary-title">{{ $heading }}</h2>
            <p class="admin-sanctuary-body">{{ $body }}</p>
            @if ($verse !== '')
                <figure class="admin-sanctuary-verse">
                    <blockquote class="admin-sanctuary-verse__text">{{ $verse }}</blockquote>
                    @if ($verseRef !== '')
                        <figcaption class="admin-sanctuary-verse__ref">{{ $verseRef }}</figcaption>
                    @endif
                </figure>
            @endif
        </div>
    </div>
</x-filament-widgets::widget>
