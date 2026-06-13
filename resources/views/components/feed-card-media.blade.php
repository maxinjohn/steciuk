@props([
    'image' => null,
    'slug' => null,
    'title' => null,
    'context' => 'default',
    'category' => null,
    'content' => null,
    'alt' => '',
    'sticker' => null,
    'stickerClass' => '',
    'day' => null,
    'month' => null,
    'weekday' => null,
])

@php
    $topic = cardMediaTopic($slug, $title, $context, $category, $content);
@endphp

<div
    @class([
        'feed-card-media topic-card-media wow-card-media',
        'feed-card-media--dated' => filled($day) && filled($month),
    ])
    data-topic="{{ $topic }}"
>
    <div class="topic-card-aura" aria-hidden="true">
        <span class="topic-card-aura__orb topic-card-aura__orb--1"></span>
        <span class="topic-card-aura__orb topic-card-aura__orb--2"></span>
        <span class="topic-card-aura__orb topic-card-aura__orb--3"></span>
    </div>

    <x-card-media
        :image="$image"
        :slug="$slug"
        :title="$title"
        :context="$context"
        :category="$category"
        :content="$content"
        :alt="$alt"
        class="feed-card-image"
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

    @if (filled($day) && filled($month))
        <div class="topic-date-badge" aria-hidden="true">
            @if (filled($weekday))
                <span class="topic-date-badge__weekday">{{ $weekday }}</span>
            @endif
            <span class="topic-date-badge__day">{{ $day }}</span>
            <span class="topic-date-badge__month">{{ $month }}</span>
            <span class="topic-date-badge__ring"></span>
        </div>
    @endif

    @if ($sticker)
        <span @class(['feed-sticker feed-sticker--glass', $stickerClass])>{{ $sticker }}</span>
    @endif
</div>
