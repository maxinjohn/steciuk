@props([
    'title',
    'subtitle' => null,
    'eyebrow' => null,
    'badge' => 'UK Parish',
    'image' => null,
    'size' => 'default',
    'style' => 'gradient',
    'accent' => 'gold',
    'overlay' => true,
    'stats' => [],
])

@php
    $imageUrl = $image
        ? (str_starts_with($image, 'http') ? $image : asset('storage/' . ltrim($image, '/')))
        : null;

    $sizeClasses = match ($size) {
        'large', 'immersive' => 'min-h-0 py-14 sm:py-16 lg:min-h-[min(84vh,780px)] lg:py-24',
        'small' => 'min-h-0 py-10 sm:py-12 lg:min-h-[28vh]',
        default => 'min-h-0 py-12 sm:py-14 lg:min-h-[40vh] lg:py-24',
    };

    $statItems = collect($stats)->filter(fn ($stat) => ! empty($stat['label'] ?? null));
@endphp

<section {{ $attributes->merge(['class' => 'hero-modern hero-mesh hero-gen-z relative flex items-center ' . $sizeClasses]) }}>
    @if ($style !== 'minimal')
        <div class="hero-deco" aria-hidden="true">
            <span class="hero-glow hero-glow-left"></span>
            <span class="hero-glow hero-glow-right"></span>
            <span class="hero-pattern"></span>
        </div>
    @endif

    @if ($imageUrl && $style === 'image')
        <img src="{{ $imageUrl }}" alt="" loading="eager" fetchpriority="high" class="absolute inset-0 h-full w-full object-cover" aria-hidden="true">
        <div class="absolute inset-0 bg-[var(--site-hero-bg)]/80" aria-hidden="true"></div>
    @elseif ($style === 'minimal')
        <div class="absolute inset-0 bg-surface" aria-hidden="true"></div>
    @endif

    <div class="relative mx-auto w-full max-w-7xl px-4 sm:px-6 md:px-8 lg:px-10">
        <div class="hero-grid">
            <div @class(['hero-copy', 'text-ink' => $style === 'minimal'])>
                @if ($style !== 'minimal')
                    @if ($badge)
                        <div class="hero-badge mb-4 sm:mb-5">
                            <span class="hero-badge-dot" aria-hidden="true"></span>
                            {{ $badge }}
                        </div>
                    @endif

                    @if ($eyebrow)
                        <p class="hero-eyebrow">{{ $eyebrow }}</p>
                    @endif
                @endif

                <h1 @class(['hero-title' => $style !== 'minimal', 'section-title' => $style === 'minimal'])>
                    @if ($style === 'minimal')
                        {{ $title }}
                    @else
                        <span class="hero-title-gradient">{{ $title }}</span>
                    @endif
                </h1>

                @if ($subtitle)
                    <p @class([
                        'hero-subtitle mt-4 sm:mt-5' => $style !== 'minimal',
                        'section-subtitle mt-3 sm:mt-4' => $style === 'minimal',
                    ])>
                        {{ $subtitle }}
                    </p>
                @endif

                @if ($statItems->isNotEmpty() && $style !== 'minimal')
                    <dl class="hero-stats mt-6 sm:mt-8">
                        @foreach ($statItems as $stat)
                            <div class="hero-stat">
                                <dt class="hero-stat-value">{{ $stat['value'] ?? '' }}</dt>
                                <dd class="hero-stat-label">{{ $stat['label'] ?? '' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif

                @if ($slot->isNotEmpty())
                    <div class="hero-actions mt-8 sm:mt-10">
                        {{ $slot }}
                    </div>
                @endif
            </div>

            @if ($style !== 'minimal')
                <aside class="hero-side-panel max-lg:!hidden hidden lg:flex" aria-label="Quick parish info">
                    <div class="hero-panel hero-panel--glass">
                        <div class="hero-panel-sticker">5 cities</div>
                        <p class="hero-panel-kicker">Worship across Britain</p>
                        <h2 class="hero-panel-title">Manchester · Leicester · Dartford · Sunderland · Bristol</h2>
                        <p class="hero-panel-text">Monthly worship, fellowship, and pastoral care for STECI families across the UK.</p>
                        <a href="{{ url('/service-times') }}" class="hero-panel-link">
                            View service times
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </aside>
            @endif
        </div>
    </div>

    @if ($style !== 'minimal')
        <div class="hero-fade" aria-hidden="true"></div>
    @endif
</section>
