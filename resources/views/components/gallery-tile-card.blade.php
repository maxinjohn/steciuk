@props(['album'])

@php
    $coverPath = $album->resolvedCoverPath();
    $uploadImage = galleryResolvedPath($coverPath, 'display') !== null ? $coverPath : null;
    $isTopicArt = galleryCoverIsTopicArt($album->cover_image, $album);
    $photoCount = (int) ($album->photos_count ?? 0);
    $topic = cardMediaTopic($album->slug, $album->title, 'gallery', null, $album->description);
@endphp

<x-card
    :href="route('gallery.show', $album->slug)"
    :padding="false"
    class="gallery-tile topic-card wow-card overflow-hidden"
>
    <div
        @class([
            'gallery-tile-media topic-card-media wow-card-media',
            'is-loaded' => $isTopicArt,
        ])
        data-topic="{{ $topic }}"
    >
        <div class="topic-card-aura" aria-hidden="true">
            <span class="topic-card-aura__orb topic-card-aura__orb--1"></span>
            <span class="topic-card-aura__orb topic-card-aura__orb--2"></span>
            <span class="topic-card-aura__orb topic-card-aura__orb--3"></span>
        </div>

        <x-card-media
            :image="$uploadImage"
            :slug="$album->slug"
            :title="$album->title"
            context="gallery"
            :content="$album->description"
            :alt="$album->title"
            class="gallery-tile-image"
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

        <div class="gallery-tile-overlay">
            <span class="feed-sticker feed-sticker--glass">{{ $photoCount }} {{ $photoCount === 1 ? 'photo' : 'photos' }}</span>
            <h2 class="gallery-tile-title">{{ $album->title }}</h2>
            @if ($album->description)
                <p class="gallery-tile-desc line-clamp-2">{{ $album->description }}</p>
            @endif
        </div>
    </div>
</x-card>
