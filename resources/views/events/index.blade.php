@extends('layouts.app')

@section('title', $page?->seo_title ?? 'Events')
@section('description', $page?->seo_description ?? 'Upcoming events at STECI UK Parish')

@section('content')
    <x-page-shell :page="$page">
        @if (! $page?->hero_title)
            <x-page-band title="Upcoming Events" subtitle="Join us for fellowship, worship, and community" kicker="What's on" />
        @endif

        <section class="page-section py-10 sm:py-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="feed-grid">
                    @forelse ($upcoming as $event)
                        <x-card
                            href="{{ route('events.show', $event->slug) }}"
                            :padding="false"
                            @class([
                                'feed-card overflow-hidden',
                                'feed-card--featured' => $loop->first,
                            ])
                        >
                            <div class="feed-card-media">
                                @if ($event->featured_image)
                                    <img src="{{ asset('storage/'.$event->featured_image) }}" alt="" class="feed-card-image" loading="lazy" decoding="async">
                                @else
                                    <div class="feed-card-fallback feed-card-fallback--event">
                                        <span class="feed-date-day">{{ $event->starts_at->format('d') }}</span>
                                        <span class="feed-date-month">{{ $event->starts_at->format('M') }}</span>
                                    </div>
                                @endif
                                <span class="feed-sticker">Event</span>
                            </div>
                            <div class="feed-card-body">
                                <time datetime="{{ $event->starts_at->toIso8601String() }}" class="feed-meta">
                                    {{ $event->starts_at->format('l, j F · g:i A') }}
                                </time>
                                <h3 class="feed-card-title">{{ $event->title }}</h3>
                                @if ($event->location)
                                    <p class="feed-card-desc">{{ $event->location }}</p>
                                @endif
                                <span class="feed-card-cta">View details →</span>
                            </div>
                        </x-card>
                    @empty
                        <p class="feed-empty">No upcoming events. Check back soon.</p>
                    @endforelse
                </div>

                <div class="mt-10">{{ $upcoming->links() }}</div>

                @if ($past->isNotEmpty())
                    <div class="past-events mt-16">
                        <x-section-heading title="Past Events" subtitle="Recent gatherings from our parish calendar" kicker="Archive" align="left" />
                        <div class="past-events-grid">
                            @foreach ($past as $event)
                                <a href="{{ route('events.show', $event->slug) }}" class="past-event-chip">
                                    <span class="past-event-date">{{ $event->starts_at->format('j M Y') }}</span>
                                    <span class="past-event-title">{{ $event->title }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </x-page-shell>
@endsection
