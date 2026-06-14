@extends('layouts.app')

@section('title', \App\Support\Seo::documentTitle($event->title, 'Events', $siteName))
@section('description', strip_tags($event->description))
@section('og_type', 'article')
@if ($event->featured_image)
    @section('og_image', \App\Support\Seo::absoluteAsset($event->featured_image))
@endif

@push('head')
    @php
        $eventSchema = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $event->title,
            'description' => \App\Support\Seo::truncateDescription(strip_tags($event->description)),
            'startDate' => $event->starts_at->toIso8601String(),
            'endDate' => $event->ends_at?->toIso8601String(),
            'eventStatus' => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'location' => [
                '@type' => 'Place',
                'name' => $event->location,
                'address' => $event->address,
            ],
            'url' => url()->current(),
            'image' => \App\Support\Seo::absoluteAsset($event->featured_image),
            'organizer' => [
                '@type' => 'Organization',
                'name' => $siteName,
                'url' => url('/'),
            ],
        ], fn ($value) => $value !== null && $value !== '' && $value !== []);
    @endphp
    <script type="application/ld+json">
        {!! json_encode($eventSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
    <article>
        <x-breadcrumbs :items="[
            ['label' => 'Events', 'url' => route('events.index')],
            ['label' => $event->title, 'current' => true],
        ]" />
        <x-hero
            :title="$event->title"
            :subtitle="$event->location"
            :image="$event->featured_image"
            size="small"
            :art-slug="$event->slug"
            :art-title="$event->title"
            art-context="event"
            :art-content="$event->description"
            :art-category="$event->category"
        >
            <div class="hero-meta-row">
                <time datetime="{{ $event->starts_at->toIso8601String() }}" class="hero-meta-chip">
                    {{ $event->starts_at->format('l, j F Y · g:i A') }}
                    @if ($event->ends_at)
                        – {{ $event->ends_at->format('g:i A') }}
                    @endif
                </time>
                <x-event-when-chip :at="$event->starts_at" />
                <x-share-chip
                    variant="hero"
                    :url="url()->current()"
                    :title="$event->title"
                />
            </div>
        </x-hero>

        <x-faith-page-bridge />

        <section class="page-section page-section--article py-10 sm:py-12 md:py-16">
            <div class="page-section-inner mx-auto max-w-7xl">
                <div class="article-layout">
                    <div>
                        @if ($event->description)
                            <div class="prose-church prose-church--page">{!! safeHtml($event->description) !!}</div>
                        @endif
                    </div>

                    <aside class="article-sidebar">
                        <x-card>
                            <h2 class="font-bold text-xl font-semibold text-ink">Event Details</h2>
                            <dl class="detail-dl mt-4">
                                <div>
                                    <dt>Date & Time</dt>
                                    <dd>{{ $event->starts_at->format('l, j F Y') }}<br>{{ $event->starts_at->format('g:i A') }}</dd>
                                </div>
                                @if ($event->location)
                                    <div>
                                        <dt>Location</dt>
                                        <dd>{{ $event->location }}</dd>
                                    </div>
                                @endif
                                @if ($event->address)
                                    <div>
                                        <dt>Address</dt>
                                        <dd>{{ $event->address }}</dd>
                                    </div>
                                @endif
                                @if ($event->category)
                                    <div>
                                        <dt>Category</dt>
                                        <dd><span class="site-category-pill !mb-0">{{ $event->category }}</span></dd>
                                    </div>
                                @endif
                            </dl>

                            @if ($event->registration_link)
                                <x-button href="{{ $event->registration_link }}" variant="primary" class="mt-6 w-full" target="_blank" rel="noopener noreferrer">
                                    Register Now
                                </x-button>
                            @endif
                        </x-card>

                        <x-card>
                            <h2 class="font-bold text-xl font-semibold text-ink">Enquire About This Event</h2>
                            <p class="mt-2 text-sm text-ink-muted">Have questions? Send us a message.</p>
                            <div class="mt-4">
                                @livewire('forms.event-enquiry-form')
                            </div>
                        </x-card>
                    </aside>
                </div>

                <div class="site-divider mt-10 pt-8">
                    <x-button href="{{ route('events.index') }}" variant="outline">← Back to Events</x-button>
                </div>
            </div>
        </section>

        <x-scripture-ribbon
            text="Let us not give up meeting together, as some are in the habit of doing, but let us encourage one another."
            reference="Hebrews 10:25"
        />
    </article>
@endsection
