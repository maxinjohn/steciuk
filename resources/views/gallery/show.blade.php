@extends('layouts.app')

@section('title', $album->title . ' | Gallery | ' . $siteName)
@section('description', $album->description ?? 'Photo album from ' . $siteName)

@section('content')
    <x-hero
        :title="$album->title"
        :subtitle="$album->description"
        badge="Evangelical Oriental Protestant"
        size="small"
    />
    <x-parish-action-strip class="!py-3" />

    <section
        class="py-12 sm:py-16"
        x-data="galleryLightbox()"
        @keydown.escape.window="lightbox = false"
        @keydown.arrow-right.window="if (lightbox) next()"
        @keydown.arrow-left.window="if (lightbox) prev()"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($album->photos as $index => $photo)
                    @php
                        $variant = str_contains(strtolower($photo->title ?? ''), 'communion') ? 'communion' : (str_contains(strtolower($album->slug ?? ''), 'fellowship') ? 'fellowship' : 'worship');
                        $src = galleryPhotoUrl($photo->image_path, $variant);
                        $alt = $photo->alt_text ?? $photo->title ?? '';
                    @endphp
                    <button
                        type="button"
                        class="group relative aspect-square overflow-hidden rounded-2xl bg-[var(--site-surface-2)] focus:outline-none focus-visible:ring-2 focus-visible:ring-gold"
                        data-gallery-photo
                        data-src="{{ $src }}"
                        data-title="{{ $photo->title ?? '' }}"
                        data-caption="{{ $photo->caption ?? '' }}"
                        data-alt="{{ $alt }}"
                        @click="open({{ $index }})"
                        aria-label="View {{ $photo->title ?? 'photo ' . ($index + 1) }}"
                    >
                        <img
                            src="{{ $src }}"
                            alt="{{ $alt }}"
                            loading="lazy"
                            decoding="async"
                            class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                        >
                    </button>
                @endforeach
            </div>

            @if ($album->photos->isEmpty())
                <p class="rounded-2xl bg-surface p-10 text-center text-ink-muted shadow-sm">No photos in this album yet.</p>
            @endif

            <div class="mt-10">
                <x-button href="{{ route('gallery.index') }}" variant="outline">← Back to Gallery</x-button>
            </div>
        </div>

        <div
            x-show="lightbox"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[80] flex items-center justify-center bg-black/95 p-4 pb-dock"
            x-cloak
            role="dialog"
            aria-modal="true"
            :aria-label="photos[current]?.title || 'Photo viewer'"
        >
            <button type="button" @click="lightbox = false" class="absolute right-4 top-4 inline-flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20" aria-label="Close lightbox">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <button type="button" @click="prev()" class="absolute left-2 top-1/2 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 sm:left-4" aria-label="Previous photo">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            </button>

            <button type="button" @click="next()" class="absolute right-2 top-1/2 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 sm:right-4" aria-label="Next photo">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </button>

            <div class="max-h-[85vh] max-w-5xl text-center" @click.outside="lightbox = false">
                <template x-if="photos.length">
                    <div>
                        <img :src="photos[current]?.src" :alt="photos[current]?.alt || ''" class="mx-auto max-h-[75vh] rounded-lg object-contain shadow-2xl" decoding="async">
                        <p x-show="photos[current]?.title" x-text="photos[current]?.title" class="mt-4 font-bold text-lg text-white"></p>
                        <p x-show="photos[current]?.caption" x-text="photos[current]?.caption" class="mt-1 text-sm text-white/70"></p>
                    </div>
                </template>
            </div>
        </div>
    </section>

    <x-scripture-ribbon
        text="Come, let us bow down in worship, let us kneel before the Lord our Maker."
        reference="Psalm 95:6"
    />
@endsection
