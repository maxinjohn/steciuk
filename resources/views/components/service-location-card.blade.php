@props([
    'service',
])

@php
    $schedule = $service->publicScheduleBlocks();
    $locationLabel = $service->location ?: 'UK Location';
    $artSlug = \Illuminate\Support\Str::slug($locationLabel);
@endphp

<x-card :padding="false" class="location-card topic-card wow-card overflow-hidden">
    <div class="location-card-media wow-card-media topic-card-media">
        <div class="topic-card-aura" aria-hidden="true">
            <span class="topic-card-aura__orb topic-card-aura__orb--1"></span>
            <span class="topic-card-aura__orb topic-card-aura__orb--2"></span>
            <span class="topic-card-aura__orb topic-card-aura__orb--3"></span>
        </div>
        <x-card-media
            :image="null"
            :slug="$artSlug"
            :title="$service->title.' '.$locationLabel"
            context="service"
            :alt="$service->title"
            class="location-card-image"
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
        <span class="feed-sticker feed-sticker--glass">{{ $locationLabel }}</span>
        @if ($service->language)
            <span class="location-card-lang-badge">{{ $service->language }}</span>
        @endif
    </div>

    <div class="location-card-body">
        <div class="location-card-top">
            <span class="topic-card-kicker">Worship · Holy Communion</span>
            @php
                $frequency = $schedule['frequency'] ?? $service->frequency;
            @endphp
            <h2 class="location-card-title">{{ $service->title }}</h2>
            @if ($frequency)
                <p class="location-card-frequency">{{ $frequency }}</p>
            @endif
            @if ($service->description)
                <p class="location-card-desc">{{ $service->description }}</p>
            @endif
        </div>

        <dl class="location-card-meta">
            @if ($schedule['date_lines'] !== [] || $schedule['headline'] || $schedule['details'] !== [])
                <div class="location-meta-item location-meta-item--when">
                    <dt>
                        <span class="location-meta-icon" aria-hidden="true">◷</span>
                        When
                    </dt>
                    @if ($schedule['date_lines'] !== [])
                        <dd>
                            <ul class="location-date-list">
                                @foreach ($schedule['date_lines'] as $dateLine)
                                    <li>{{ $dateLine }}</li>
                                @endforeach
                            </ul>
                        </dd>
                    @else
                        <dd class="location-schedule-line">
                            {{ collect([$schedule['headline'], ...$schedule['details']])->filter()->implode(' · ') }}
                        </dd>
                        @if ($schedule['frequency'])
                            <dd class="location-meta-sub">{{ $schedule['frequency'] }}</dd>
                        @endif
                    @endif
                </div>
            @endif

            @if ($service->formattedAddress())
                <div class="location-meta-item location-meta-item--wide">
                    <dt>
                        <span class="location-meta-icon" aria-hidden="true">⌖</span>
                        Address
                    </dt>
                    <dd class="location-address-line">{{ $service->formattedAddress() }}</dd>
                </div>
            @endif

            @if ($service->contact_person || $service->contact_email || $service->contact_phone)
                <div class="location-meta-item">
                    <dt>
                        <span class="location-meta-icon" aria-hidden="true">✉</span>
                        Contact
                    </dt>
                    @if ($service->contact_person)
                        <dd>{{ $service->contact_person }}</dd>
                    @endif
                    @if ($service->contact_email)
                        <dd class="location-meta-sub">
                            <a href="mailto:{{ $service->contact_email }}" class="location-contact-link">{{ $service->contact_email }}</a>
                        </dd>
                    @endif
                    @if ($service->contact_phone)
                        <dd class="location-meta-sub">
                            <a href="tel:{{ preg_replace('/\s+/', '', $service->contact_phone) }}" class="location-contact-link">{{ $service->contact_phone }}</a>
                        </dd>
                    @endif
                </div>
            @endif
        </dl>

        @if ($service->map_link || $service->online_stream_link)
            <div class="location-card-actions">
                @if ($service->map_link)
                    <x-button href="{{ $service->map_link }}" variant="outline" class="!min-h-11 !text-sm" target="_blank" rel="noopener noreferrer">View Map</x-button>
                @endif
                @if ($service->online_stream_link)
                    <x-button href="{{ $service->online_stream_link }}" variant="primary" class="!min-h-11 !text-sm" target="_blank" rel="noopener noreferrer">Watch Online</x-button>
                @endif
            </div>
        @endif
    </div>
</x-card>
