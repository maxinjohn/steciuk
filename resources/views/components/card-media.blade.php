@props([
    'image' => null,
    'slug' => null,
    'title' => null,
    'context' => 'default',
    'category' => null,
    'content' => null,
    'alt' => '',
    'priority' => 'lazy',
])

@php
    $topic = cardMediaTopic($slug, $title, $context, $category, $content);
@endphp

<img
    src="{{ cardMediaUrl($image, $slug, $title, $context, $category, $content) }}"
    alt="{{ $alt }}"
    loading="{{ in_array($priority, ['eager', 'high'], true) ? 'eager' : 'lazy' }}"
    @if ($priority === 'high') fetchpriority="high" @endif
    decoding="async"
    data-topic="{{ $topic }}"
    @class([
        'card-media-image',
        'card-media-image--topic' => cardMediaIsTopicArt($image),
        'card-media-image--dynamic' => cardMediaIsTopicArt($image),
    ])
    {{ $attributes }}
>
