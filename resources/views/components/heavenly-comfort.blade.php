@props([
    'heading' => null,
    'subheading' => null,
    'kicker' => null,
    'cards' => [],
])

@php
    $defaultCards = [
        [
            'icon' => '🕊',
            'title' => 'Peace in Christ',
            'text' => 'His peace guards heart and mind — a gift for every believer who draws near in worship and prayer.',
            'ref' => 'Philippians 4:7',
        ],
        [
            'icon' => '🙏',
            'title' => 'Rest in Prayer',
            'text' => 'Bring every burden to the Lord. Our parish family intercedes with you in faith.',
            'ref' => 'Matthew 11:28',
            'link' => url('/prayer-request'),
            'linkLabel' => 'Submit a prayer request',
        ],
        [
            'icon' => '📖',
            'title' => 'Hope in Scripture',
            'text' => 'Holy Scripture nourishes faith — through preaching, reading, and Holy Communion at the Lord\'s table.',
            'ref' => 'Romans 15:4',
            'link' => url('/sermons'),
            'linkLabel' => 'Listen to a sermon',
        ],
        [
            'icon' => '✝',
            'title' => 'Assurance in Grace',
            'text' => 'Salvation is by grace through faith in Christ alone — not by works, but by the mercy of God.',
            'ref' => 'Ephesians 2:8–9',
            'link' => url('/our-church'),
            'linkLabel' => 'Read our beliefs',
        ],
    ];

    $comforts = collect($cards)->filter(fn ($item) => ! empty($item['title']))->values();
    if ($comforts->isEmpty()) {
        $comforts = collect($defaultCards);
    }
@endphp

<section {{ $attributes->merge(['class' => 'heavenly-comfort']) }} aria-label="Comfort in Christ">
    <div class="heavenly-comfort-glow heavenly-comfort-glow--left" aria-hidden="true"></div>
    <div class="heavenly-comfort-glow heavenly-comfort-glow--right" aria-hidden="true"></div>
    <div class="heavenly-comfort-inner mx-auto max-w-7xl">
        <div class="heavenly-comfort-header">
            <p class="heavenly-comfort-kicker">
                <span class="genz-kicker-dot" aria-hidden="true"></span>
                {{ $kicker ?: 'For every believer' }}
            </p>
            <h2 class="heavenly-comfort-title">{{ $heading ?: 'Rest in the Lord' }}</h2>
            <p class="heavenly-comfort-subtitle">{{ $subheading ?: 'Scripture, prayer, and Holy Communion — anchors for daily faith in Christ' }}</p>
        </div>
        <div class="heavenly-comfort-grid" role="list">
            @foreach ($comforts as $item)
                <article class="heavenly-comfort-card" role="listitem">
                    <span class="heavenly-comfort-icon" aria-hidden="true">{{ $item['icon'] ?? '🕊' }}</span>
                    <h3 class="heavenly-comfort-card-title">{{ $item['title'] }}</h3>
                    <p class="heavenly-comfort-card-text">{{ $item['text'] }}</p>
                    <p class="heavenly-comfort-card-ref">{{ $item['ref'] ?? '' }}</p>
                    @if (! empty($item['link']))
                        <a href="{{ $item['link'] }}" class="heavenly-comfort-link">{{ $item['linkLabel'] ?? 'Learn more' }} <span aria-hidden="true">→</span></a>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
