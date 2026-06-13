@props([
    'ministry',
    'headingTag' => 'h2',
    'showKicker' => true,
])

<x-card
    :href="route('ministries.show', $ministry->slug)"
    :padding="false"
    class="bento-tile topic-card wow-card overflow-hidden"
>
    <div class="bento-tile-media topic-card-media wow-card-media">
        <div class="topic-card-aura" aria-hidden="true">
            <span class="topic-card-aura__orb topic-card-aura__orb--1"></span>
            <span class="topic-card-aura__orb topic-card-aura__orb--2"></span>
            <span class="topic-card-aura__orb topic-card-aura__orb--3"></span>
        </div>
        <x-card-media
            :image="$ministry->featured_image"
            :slug="$ministry->slug"
            :title="$ministry->name"
            context="ministry"
            :content="\App\Support\PageTopicArt::contentHintForRecord($ministry->description, $ministry->short_description)"
            :alt="$ministry->name"
            class="bento-tile-image"
        />
        <div class="topic-card-shade" aria-hidden="true"></div>
        <div class="topic-card-mesh" aria-hidden="true"></div>
        <div class="topic-card-scanlines" aria-hidden="true"></div>
        <div class="topic-card-shimmer" aria-hidden="true"></div>
        <div class="topic-card-vignette" aria-hidden="true"></div>
        <div class="topic-card-frame" aria-hidden="true">
            <span class="topic-card-frame__corner topic-card-frame__corner--tl"></span>
            <span class="topic-card-frame__corner topic-card-frame__corner--tr"></span>
            <span class="topic-card-frame__corner topic-card-frame__corner--bl"></span>
            <span class="topic-card-frame__corner topic-card-frame__corner--br"></span>
        </div>
    </div>
    <div class="bento-tile-body">
        @if ($showKicker)
            <span class="topic-card-kicker">Ministry</span>
        @endif
        <{{ $headingTag }} class="bento-tile-title">{{ $ministry->name }}</{{ $headingTag }}>
        @if ($ministry->short_description)
            <p class="bento-tile-desc line-clamp-2">{{ $ministry->short_description }}</p>
        @endif
        <span class="bento-tile-link">Explore →</span>
    </div>
</x-card>
