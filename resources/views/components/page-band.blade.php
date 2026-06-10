@props([
    'title',
    'subtitle' => null,
    'kicker' => 'Evangelical Oriental Protestant Parish',
])

<section {{ $attributes->merge(['class' => 'page-band page-section']) }}>
    <div class="page-band-bg" aria-hidden="true">
        <span class="page-band-cross" aria-hidden="true">✝</span>
        <span class="page-band-orb page-band-orb--gold"></span>
        <span class="page-band-orb page-band-orb--navy"></span>
        <span class="page-band-grid"></span>
    </div>
    <div class="page-band-inner mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @if ($kicker)
            <div class="genz-kicker">
                <span class="genz-kicker-dot" aria-hidden="true"></span>
                {{ $kicker }}
            </div>
        @endif
        <h1 class="page-band-title">
            <span class="text-gradient-gold">{{ $title }}</span>
        </h1>
        @if ($subtitle)
            <p class="page-band-subtitle">{{ $subtitle }}</p>
        @endif
        @if ($slot->isNotEmpty())
            <div class="page-band-actions">{{ $slot }}</div>
        @endif
    </div>
</section>
