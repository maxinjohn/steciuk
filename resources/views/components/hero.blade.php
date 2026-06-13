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
    'showSidePanel' => false,
    'artSlug' => null,
    'artTitle' => null,
    'artContext' => 'page',
    'artContent' => null,
    'artCategory' => null,
])

@php
    $imageUrl = hasRealFeaturedImage($image)
        ? (str_starts_with($image, 'http') ? $image : public_upload_url($image))
        : null;

    $showTopicArt = $style !== 'minimal' && ! $imageUrl;
    $topicArtSlug = $artSlug ?? $artTitle;
    $topicArtTitle = $artTitle ?? $title;
    $topicArtContent = \App\Support\SiteTopicArt::buildContentHint($artContent, $subtitle, $eyebrow);
    $topicArtTopic = $showTopicArt
        ? pageTopicArt($topicArtSlug, $topicArtTitle, $artContext, $topicArtContent, $artCategory)
        : null;
    $topicArtUrl = $showTopicArt
        ? pageTopicArtUrl($topicArtSlug, $topicArtTitle, $artContext, $topicArtContent, $artCategory)
        : null;

    $sizeClasses = match ($size) {
        'large', 'immersive' => 'min-h-0 py-12 sm:py-14 lg:min-h-[min(72vh,680px)] lg:py-20',
        'small' => 'min-h-0 py-10 sm:py-12 lg:min-h-[28vh]',
        default => 'min-h-0 py-12 sm:py-14 lg:min-h-[40vh] lg:py-24',
    };

    if ($showTopicArt && $size === 'small') {
        $sizeClasses = 'min-h-0 py-8 sm:py-10 lg:py-11';
    }

    $statItems = collect($stats)->filter(fn ($stat) => ! empty($stat['label'] ?? null));
    $useHomeArtLayout = $showTopicArt
        && in_array($size, ['large', 'immersive'], true)
        && $style !== 'minimal';
@endphp

<section {{ $attributes->class([
    'hero-modern hero-mesh hero-gen-z hero-evangelical relative flex items-center',
    'hero-modern--topic-art' => $showTopicArt,
    'hero-modern--topic-art-large' => $showTopicArt && in_array($size, ['large', 'immersive'], true),
    $sizeClasses,
]) }}>
    @if ($style !== 'minimal' && ! $showTopicArt)
        <span class="hero-cross-mark" aria-hidden="true">✝</span>
        <div class="hero-deco" aria-hidden="true">
            <span class="hero-glow hero-glow-left"></span>
            <span class="hero-glow hero-glow-right"></span>
            <span class="hero-pattern"></span>
        </div>
    @endif

    @if ($imageUrl && $style === 'image')
        <img src="{{ $imageUrl }}" alt="" loading="eager" fetchpriority="high" class="absolute inset-0 h-full w-full object-cover" aria-hidden="true">
        <div class="absolute inset-0 bg-[var(--site-hero-bg)]/80" aria-hidden="true"></div>
    @elseif ($showTopicArt)
        <div class="absolute inset-0 bg-gradient-to-br from-[#0a1018] via-[#0d1420] to-[#121a28]" aria-hidden="true"></div>
    @elseif ($style === 'minimal')
        <div class="absolute inset-0 bg-surface" aria-hidden="true"></div>
    @endif

    <div class="page-section-inner relative mx-auto w-full max-w-7xl">
        <div @class([
            'hero-grid',
            'hero-grid--with-art' => $showTopicArt && ! $useHomeArtLayout,
            'hero-grid--home-art' => $useHomeArtLayout,
        ])>
            @if ($useHomeArtLayout)
                <div class="hero-intro-block">
                    @if ($badge)
                        <div class="hero-badge mb-4 sm:mb-5">
                            <span class="hero-badge-dot" aria-hidden="true"></span>
                            {{ $badge }}
                        </div>
                    @endif

                    @if ($eyebrow)
                        <p class="hero-eyebrow">{{ $eyebrow }}</p>
                    @endif

                    <h1 class="hero-title">
                        <span class="hero-title-gradient">{{ $title }}</span>
                    </h1>

                    @if ($subtitle)
                        <p class="hero-subtitle mt-4 sm:mt-5">{{ $subtitle }}</p>
                        @if (str_contains($subtitle, 'Word of God'))
                            <p class="hero-scripture-ref">{{ $gospelReminderReference ?? 'Revelation 1:9' }}</p>
                        @endif
                    @endif
                </div>

                <aside class="hero-art-showcase" aria-hidden="true">
                    <div class="hero-art-showcase__frame wow-card" data-topic="{{ $topicArtTopic }}">
                        <img
                            src="{{ $topicArtUrl }}"
                            alt=""
                            loading="eager"
                            decoding="async"
                            class="hero-art-showcase__image"
                        >
                        <div class="topic-card-aura">
                            <span class="topic-card-aura__orb topic-card-aura__orb--1"></span>
                            <span class="topic-card-aura__orb topic-card-aura__orb--2"></span>
                            <span class="topic-card-aura__orb topic-card-aura__orb--3"></span>
                        </div>
                        <div class="topic-card-shimmer"></div>
                        <div class="topic-card-frame">
                            <span class="topic-card-frame__corner topic-card-frame__corner--tl"></span>
                            <span class="topic-card-frame__corner topic-card-frame__corner--tr"></span>
                            <span class="topic-card-frame__corner topic-card-frame__corner--bl"></span>
                            <span class="topic-card-frame__corner topic-card-frame__corner--br"></span>
                        </div>
                    </div>
                </aside>

                @if ($statItems->isNotEmpty() || $slot->isNotEmpty())
                    <div class="hero-rich-block">
                        @if ($statItems->isNotEmpty())
                            <dl class="hero-stats mt-0 sm:mt-2">
                                @foreach ($statItems as $stat)
                                    <div class="hero-stat">
                                        <dt class="hero-stat-value">{{ $stat['value'] ?? '' }}</dt>
                                        <dd class="hero-stat-label">{{ $stat['label'] ?? '' }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif

                        @if ($slot->isNotEmpty())
                            <div class="hero-actions">
                                {{ $slot }}
                            </div>
                        @endif
                    </div>
                @endif
            @else
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
                    @if ($style !== 'minimal' && str_contains($subtitle, 'Word of God'))
                        <p class="hero-scripture-ref">{{ $gospelReminderReference ?? 'Revelation 1:9' }}</p>
                    @endif
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

            @if ($showTopicArt && $style !== 'minimal')
                <aside class="hero-art-showcase" aria-hidden="true">
                    <div class="hero-art-showcase__frame wow-card" data-topic="{{ $topicArtTopic }}">
                        <img
                            src="{{ $topicArtUrl }}"
                            alt=""
                            loading="eager"
                            decoding="async"
                            class="hero-art-showcase__image"
                        >
                        <div class="topic-card-aura">
                            <span class="topic-card-aura__orb topic-card-aura__orb--1"></span>
                            <span class="topic-card-aura__orb topic-card-aura__orb--2"></span>
                            <span class="topic-card-aura__orb topic-card-aura__orb--3"></span>
                        </div>
                        <div class="topic-card-shimmer"></div>
                        <div class="topic-card-frame">
                            <span class="topic-card-frame__corner topic-card-frame__corner--tl"></span>
                            <span class="topic-card-frame__corner topic-card-frame__corner--tr"></span>
                            <span class="topic-card-frame__corner topic-card-frame__corner--bl"></span>
                            <span class="topic-card-frame__corner topic-card-frame__corner--br"></span>
                        </div>
                    </div>
                </aside>
            @elseif ($style !== 'minimal' && $showSidePanel)
                <aside class="hero-side-panel max-lg:!hidden hidden lg:flex" aria-label="Parish worship">
                    <div class="hero-panel hero-panel--glass">
                        <div class="hero-panel-sticker">Monthly worship</div>
                        <p class="hero-panel-kicker">UK Parish · STECI</p>
                        <h2 class="hero-panel-title">Scripture · Gospel · Sacraments</h2>
                        <p class="hero-panel-text">Monthly worship, expository preaching, and intercessory prayer across five cities — confessing Christ as Lord in the evangelical Oriental Protestant tradition of the Saint Thomas Christian Church.</p>
                        <a href="{{ url('/our-church') }}" class="hero-panel-link">
                            Our beliefs
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </aside>
            @endif
            @if ($showSidePanel && ! $showTopicArt)
                <aside class="hero-side-panel hero-side-panel--stacked lg:hidden" aria-label="Parish worship">
                    <div class="hero-panel hero-panel--glass">
                        <div class="hero-panel-sticker">Monthly worship</div>
                        <p class="hero-panel-kicker">UK Parish · STECI</p>
                        <h2 class="hero-panel-title">Scripture · Gospel · Sacraments</h2>
                        <p class="hero-panel-text">Monthly worship, expository preaching, and intercessory prayer across five cities — confessing Christ as Lord in the evangelical Oriental Protestant tradition of the Saint Thomas Christian Church.</p>
                        <a href="{{ url('/our-church') }}" class="hero-panel-link">
                            Our beliefs
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </aside>
            @endif
            @endif
        </div>
    </div>

    @if ($style !== 'minimal')
        <div class="hero-fade" aria-hidden="true"></div>
    @endif
</section>
