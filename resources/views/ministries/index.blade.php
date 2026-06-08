@extends('layouts.app')

@section('title', $page?->seo_title ?? 'Ministries')
@section('description', $page?->seo_description ?? 'Discover ministries at STECI UK Parish')

@section('content')
    <x-page-shell :page="$page">
        @if (! $page?->hero_title)
            <x-page-band title="Our Ministries" subtitle="Serving God and one another across the UK Parish" kicker="Get involved" />
        @endif

        <section class="page-section py-10 sm:py-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="bento-grid bento-grid--ministries">
                    @forelse ($ministries as $ministry)
                        <x-card
                            :href="route('ministries.show', $ministry->slug)"
                            :padding="false"
                            class="bento-tile overflow-hidden"
                        >
                            <div class="bento-tile-media">
                                @if ($ministry->featured_image)
                                    <img src="{{ asset('storage/' . ltrim($ministry->featured_image, '/')) }}" alt="{{ $ministry->name }}" loading="lazy" decoding="async" class="bento-tile-image">
                                @else
                                    <div class="bento-tile-fallback">
                                        <span>{{ strtoupper(substr($ministry->name, 0, 1)) }}</span>
                                    </div>
                                @endif
                                <div class="bento-tile-shade"></div>
                            </div>
                            <div class="bento-tile-body">
                                <h2 class="bento-tile-title">{{ $ministry->name }}</h2>
                                @if ($ministry->short_description)
                                    <p class="bento-tile-desc line-clamp-2">{{ $ministry->short_description }}</p>
                                @endif
                                <span class="bento-tile-link">Explore →</span>
                            </div>
                        </x-card>
                    @empty
                        <p class="feed-empty">Ministries coming soon.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </x-page-shell>
@endsection
