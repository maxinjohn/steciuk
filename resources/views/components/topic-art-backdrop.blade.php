@props([
    'slug' => null,
    'title' => null,
    'context' => 'page',
    'content' => null,
    'category' => null,
    'variant' => 'hero',
])

@php
    $topic = pageTopicArt($slug, $title, $context, $content, $category);
    $url = pageTopicArtUrl($slug, $title, $context, $content, $category);
@endphp

<div
    {{ $attributes->class([
        'topic-art-backdrop',
        'topic-art-backdrop--' . $variant,
    ]) }}
    data-topic="{{ $topic }}"
    aria-hidden="true"
>
    <img
        src="{{ $url }}"
        alt=""
        loading="{{ in_array($variant, ['hero', 'band'], true) ? 'eager' : 'lazy' }}"
        @if (in_array($variant, ['hero', 'band'], true)) fetchpriority="high" @endif
        decoding="async"
        @class([
            'topic-art-backdrop__image card-media-image card-media-image--topic card-media-image--dynamic',
        ])
    >
    <div class="topic-card-aura">
        <span class="topic-card-aura__orb topic-card-aura__orb--1"></span>
        <span class="topic-card-aura__orb topic-card-aura__orb--2"></span>
        <span class="topic-card-aura__orb topic-card-aura__orb--3"></span>
    </div>
    <div class="topic-card-mesh"></div>
    <div class="topic-card-scanlines"></div>
    <div class="topic-card-shade"></div>
    <div class="topic-art-backdrop__veil"></div>
    <div class="topic-card-frame">
        <span class="topic-card-frame__corner topic-card-frame__corner--tl"></span>
        <span class="topic-card-frame__corner topic-card-frame__corner--tr"></span>
        <span class="topic-card-frame__corner topic-card-frame__corner--bl"></span>
        <span class="topic-card-frame__corner topic-card-frame__corner--br"></span>
    </div>
</div>
