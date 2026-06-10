@extends('layouts.app')

@section('title', $page?->seo_title ?? 'Service Times')
@section('description', $page?->seo_description ?? 'Worship locations across the UK Parish')

@section('content')
    <x-page-shell :page="$page" suppress-content>
        <x-page-intro
            title="Holy Communion & Worship"
            subtitle="Monthly evangelical Oriental Protestant worship across five UK cities"
            kicker="Plan your visit"
            scripture="God is spirit, and his worshipers must worship in the Spirit and in truth."
            scripture-ref="John 4:24"
        />
        <x-worship-rhythm />

        <section class="page-section py-10 sm:py-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="location-grid">
                    @forelse ($services as $service)
                        <x-card class="location-card">
                            <div class="location-card-header">
                                <div class="location-card-icon" aria-hidden="true">
                                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <span class="feed-sticker feed-sticker--inline">{{ $service->location ?: 'UK Location' }}</span>
                                    <h2 class="location-card-title">{{ $service->title }}</h2>
                                </div>
                            </div>

                            @if ($service->description)
                                <p class="location-card-desc">{{ $service->description }}</p>
                            @endif

                            <dl class="location-card-meta">
                                @if ($service->service_day || $service->service_time)
                                    <div class="location-meta-item">
                                        <dt>When</dt>
                                        <dd>{{ trim(($service->service_day ?? '') . ' · ' . ($service->service_time ?? ''), ' ·') }}</dd>
                                        @if ($service->frequency)
                                            <dd class="location-meta-sub">{{ $service->frequency }}</dd>
                                        @endif
                                    </div>
                                @endif
                                @if ($service->address)
                                    <div class="location-meta-item location-meta-item--wide">
                                        <dt>Address</dt>
                                        <dd>{{ $service->address }}</dd>
                                    </div>
                                @endif
                            </dl>

                            <div class="location-card-actions">
                                @if ($service->map_link)
                                    <x-button href="{{ $service->map_link }}" variant="outline" class="!min-h-11 !text-sm" target="_blank" rel="noopener noreferrer">View Map</x-button>
                                @endif
                                @if ($service->online_stream_link)
                                    <x-button href="{{ $service->online_stream_link }}" variant="primary" class="!min-h-11 !text-sm" target="_blank" rel="noopener noreferrer">Watch Online</x-button>
                                @endif
                            </div>
                        </x-card>
                    @empty
                        <p class="feed-empty">Service times will be published here soon.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </x-page-shell>
@endsection
