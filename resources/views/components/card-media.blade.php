@props([
    'image' => null,
    'slug' => null,
    'title' => null,
    'context' => 'default',
    'category' => null,
    'content' => null,
    'alt' => '',
])

@php
    $topic = cardMediaTopic($slug, $title, $context, $category, $content);
@endphp

<img
    src="{{ cardMediaUrl($image, $slug, $title, $context, $category, $content) }}"
    alt="{{ $alt }}"
    loading="lazy"
    decoding="async"
    data-topic="{{ $topic }}"
    @class([
        'card-media-image',
        'card-media-image--topic' => cardMediaIsTopicArt($image),
        'card-media-image--dynamic' => cardMediaIsTopicArt($image),
    ])
    {{ $attributes }}
>
