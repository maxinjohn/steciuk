@extends('layouts.app')

@section('title', $page?->seo_title ?? 'Events')
@section('description', $page?->seo_description ?? 'Upcoming events at STECI UK Parish')

@section('content')
    <x-page-shell :page="$page" suppress-content>
        <x-breadcrumbs :items="[['label' => 'Events', 'current' => true]]" />
        <x-page-intro
            title="Parish Events"
            subtitle="Worship gatherings, prayer meetings, and fellowship in Christ"
            kicker="Evangelical Oriental Protestant · Parish life"
            scripture="Let us not give up meeting together, as some are in the habit of doing, but let us encourage one another."
            scripture-ref="Hebrews 10:25"
            art-slug="events"
            art-title="Parish Events"
            art-context="event"
        />

        <section class="page-section page-section--compact">
            <div class="page-section-inner mx-auto max-w-7xl">
                <div class="feed-grid feed-rail">
                    @forelse ($upcoming as $event)
                        <x-card
                            href="{{ route('events.show', $event->slug) }}"
                            :padding="false"
                                @class([
                                'feed-card topic-card wow-card overflow-hidden',
                                'feed-card--featured' => $loop->first,
                            ])
                        >
                            <x-feed-card-media
                                :image="$event->featured_image"
                                :slug="$event->slug"
                                :title="$event->title"
                                context="event"
                                :alt="$event->title"
                                sticker="Event"
                                :day="$event->starts_at->format('d')"
                                :month="$event->starts_at->format('M')"
                                :weekday="$event->starts_at->format('D')"
                                :category="$event->category"
                                :content="\App\Support\PageTopicArt::contentHintForRecord($event->description, null, null, $event->location, $event->category)"
                                :priority="$loop->first ? 'high' : 'lazy'"
                            />
                            <div class="feed-card-body">
                                <div class="feed-card-head">
                                    <time datetime="{{ $event->starts_at->toIso8601String() }}" class="feed-meta">
                                        {{ $event->starts_at->format('l, j F · g:i A') }}
                                    </time>
                                    <div class="feed-card-head__actions">
                                        <x-event-when-chip :at="$event->starts_at" />
                                        <x-share-chip
                                            :url="route('events.show', $event->slug)"
                                            :title="$event->title"
                                        />
                                    </div>
                                </div>
                                <h3 class="feed-card-title">{{ $event->title }}</h3>
                                @if ($event->location)
                                    <p class="feed-card-desc">{{ $event->location }}</p>
                                @endif
                                <span class="feed-card-cta">View details →</span>
                            </div>
                        </x-card>
                    @empty
                        <div class="feed-empty feed-empty--rich col-span-full">
                            <p class="feed-empty__title">No upcoming events right now</p>
                            <p class="feed-empty__text">Join us for worship across our UK locations — times are updated regularly.</p>
                            <x-button href="{{ url('/service-times') }}" variant="outline" class="feed-empty__action">View worship times</x-button>
                        </div>
                    @endforelse
                </div>

                <div class="site-pagination">{{ $upcoming->links() }}</div>

                @if ($past->isNotEmpty())
                    <div class="past-events mt-16">
                        <x-section-heading title="Past Events" subtitle="Recent gatherings from our parish calendar" kicker="Archive" align="left" />
                        <div class="past-events-grid">
                            @foreach ($past as $event)
                                <a href="{{ route('events.show', $event->slug) }}" class="past-event-chip wow-chip">
                                    <span class="past-event-date-badge" aria-hidden="true">
                                        <span class="past-event-date-badge__day">{{ $event->starts_at->format('d') }}</span>
                                        <span class="past-event-date-badge__month">{{ $event->starts_at->format('M') }}</span>
                                    </span>
                                    <span class="past-event-copy">
                                        <span class="past-event-date">{{ $event->starts_at->format('l, j F Y') }}</span>
                                        <span class="past-event-title">{{ $event->title }}</span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </x-page-shell>
@endsection
