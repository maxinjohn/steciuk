@props([
    'locationName',
    'service' => null,
    'linkUrl' => null,
    'linkLabel' => 'All Service Times',
])

@php
    $schedule = $service?->publicScheduleBlocks() ?? [];
    $artSlug = \Illuminate\Support\Str::slug($locationName);
    $frequency = $schedule['frequency'] ?? $service?->frequency;
@endphp

<div {{ $attributes->merge(['class' => 'location-panel wow-card']) }}>
    <div class="location-panel-layout">
        <div class="location-panel-media wow-card-media topic-card-media">
            <div class="topic-card-aura" aria-hidden="true">
                <span class="topic-card-aura__orb topic-card-aura__orb--1"></span>
            </div>
            <x-card-media
                :image="null"
                :slug="$artSlug"
                :title="($service?->title ?? $locationName).' worship'"
                context="service"
                :alt="$locationName.' worship location'"
                class="location-panel-image"
            />
            <div class="topic-card-shade" aria-hidden="true"></div>
            <div class="topic-card-vignette" aria-hidden="true"></div>
            <span class="feed-sticker feed-sticker--glass">{{ $locationName }}</span>
        </div>

        <div class="location-panel-body">
            <span class="topic-card-kicker">UK Parish · Worship</span>
            <h3 class="location-panel-title">{{ $service?->title ?? $locationName.' Worship' }}</h3>

            @if ($service?->language)
                <p class="location-panel-language">{{ $service->language }}</p>
            @endif

            @if ($frequency)
                <p class="location-panel-frequency">{{ $frequency }}</p>
            @endif

            @if ($service?->description)
                <p class="location-panel-desc">{{ $service->description }}</p>
            @endif

            @if ($service && (($schedule['date_lines'] ?? []) !== [] || $service->service_day || $service->service_time || $service->formattedAddress()))
                <dl class="location-panel-meta">
                    @if (($schedule['date_lines'] ?? []) !== [])
                        <div class="location-meta-item location-meta-item--when">
                            <dt><span class="location-meta-icon" aria-hidden="true">◷</span> When</dt>
                            <dd>
                                <ul class="location-date-list">
                                    @foreach ($schedule['date_lines'] as $dateLine)
                                        <li>{{ $dateLine }}</li>
                                    @endforeach
                                </ul>
                            </dd>
                        </div>
                    @elseif ($service->service_day || $service->service_time)
                        <div class="location-meta-item location-meta-item--when">
                            <dt><span class="location-meta-icon" aria-hidden="true">◷</span> When</dt>
                            <dd>{{ trim(($service->service_day ?? '').' · '.($service->service_time ?? ''), ' ·') }}</dd>
                        </div>
                    @endif

                    @if ($service->formattedAddress())
                        <div class="location-meta-item location-meta-item--wide">
                            <dt><span class="location-meta-icon" aria-hidden="true">⌖</span> Address</dt>
                            <dd class="location-address-line">{{ $service->formattedAddress() }}</dd>
                        </div>
                    @endif
                </dl>
            @endif

            <div class="location-panel-actions">
                @if ($service?->map_link)
                    <x-button href="{{ $service->map_link }}" variant="outline" class="!min-h-11 !w-auto !px-4 !py-2 !text-sm" target="_blank" rel="noopener noreferrer">View Map</x-button>
                @endif
                @if ($service?->online_stream_link)
                    <x-button href="{{ $service->online_stream_link }}" variant="primary" class="!min-h-11 !w-auto !px-4 !py-2 !text-sm" target="_blank" rel="noopener noreferrer">Watch Online</x-button>
                @endif
                @if ($linkUrl)
                    <x-button :href="$linkUrl" variant="secondary" class="!min-h-11 !w-auto !px-4 !py-2 !text-sm">
                        {{ $linkLabel }}
                    </x-button>
                @endif
            </div>
        </div>
    </div>
</div>
