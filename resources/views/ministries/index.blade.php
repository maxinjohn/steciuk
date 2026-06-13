@extends('layouts.app')

@section('title', $page?->seo_title ?? 'Ministries')
@section('description', $page?->seo_description ?? 'Discover ministries at STECI UK Parish')

@section('content')
    <x-page-shell :page="$page" suppress-content>
        <x-page-intro
            title="Ministries & Mission"
            subtitle="Sunday School, prayer, choir, and outreach — serving Christ together"
            kicker="Evangelical Oriental Protestant · Serve"
            scripture="To equip his people for works of service, so that the body of Christ may be built up."
            scripture-ref="Ephesians 4:12"
        />

        <section class="page-section page-section--compact">
            <div class="page-section-inner mx-auto max-w-7xl">
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
