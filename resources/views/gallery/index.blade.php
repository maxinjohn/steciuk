@extends('layouts.app')

@section('title', $page?->seo_title ?? 'Gallery')
@section('description', $page?->seo_description ?? 'Photo gallery from STECI UK Parish')

@section('content')
    <x-page-shell :page="$page" suppress-content>
        <x-breadcrumbs :items="[['label' => 'Gallery', 'current' => true]]" />
        <x-page-intro
            title="Worship & Fellowship"
            subtitle="Holy Communion, prayer, and parish life in pictures"
            kicker="Saint Thomas heritage"
            scripture="Come, let us bow down in worship, let us kneel before the Lord our Maker."
            scripture-ref="Psalm 95:6"
        />

        <section class="page-section page-section--compact">
            <div class="page-section-inner mx-auto max-w-7xl">
                <div class="gallery-mosaic">
                    @forelse ($albums as $album)
                        <x-card
                            :href="route('gallery.show', $album->slug)"
                            :padding="false"
                            class="gallery-tile overflow-hidden"
                        >
                            <div class="gallery-tile-media">
                                @php
                                    $coverVariant = str_contains(strtolower($album->slug), 'fellowship') ? 'fellowship' : 'worship';
                                @endphp
                                <img src="{{ galleryCoverUrl($album->cover_image, $coverVariant, $album) }}" alt="{{ $album->title }}" loading="lazy" decoding="async" class="gallery-tile-image">
                                <div class="gallery-tile-overlay">
                                    <span class="feed-sticker">{{ $album->photos_count }} photos</span>
                                    <h2 class="gallery-tile-title">{{ $album->title }}</h2>
                                    @if ($album->description)
                                        <p class="gallery-tile-desc line-clamp-2">{{ $album->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </x-card>
                    @empty
                        <p class="feed-empty">Gallery albums coming soon.</p>
                    @endforelse
                </div>

                @if ($albums->hasPages())
                    <div class="site-pagination">{{ $albums->links() }}</div>
                @endif
            </div>
        </section>
    </x-page-shell>
@endsection
