@extends('layouts.app')

@section('title', $page?->seo_title ?? 'Gallery')
@section('description', $page?->seo_description ?? 'Photo gallery from STECI UK Parish')

@section('content')
    <x-page-shell :page="$page">
        @if (! $page?->hero_title)
            <x-page-band title="Photo Gallery" subtitle="Moments from worship, fellowship, and parish life" kicker="Memories" />
        @endif

        <section class="page-section py-10 sm:py-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="gallery-mosaic">
                    @forelse ($albums as $album)
                        <x-card
                            :href="route('gallery.show', $album->slug)"
                            :padding="false"
                            @class([
                                'gallery-tile overflow-hidden',
                                'gallery-tile--wide' => $loop->index % 5 === 0,
                            ])
                        >
                            <div class="gallery-tile-media">
                                @if ($album->cover_image)
                                    <img src="{{ asset('storage/' . ltrim($album->cover_image, '/')) }}" alt="{{ $album->title }}" loading="lazy" decoding="async" class="gallery-tile-image">
                                @else
                                    <div class="gallery-tile-fallback">
                                        <svg class="h-10 w-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                    </div>
                                @endif
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
                    <div class="mt-10">{{ $albums->links() }}</div>
                @endif
            </div>
        </section>
    </x-page-shell>
@endsection
