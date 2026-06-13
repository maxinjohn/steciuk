@props([
    'blocks',
    'services' => collect(),
    'ministries' => collect(),
    'events' => collect(),
    'news' => collect(),
    'sermons' => collect(),
    'albums' => collect(),
])

@php
    $resolveLink = function (?string $url): string {
        return \App\Support\SafeUrl::resolve($url);
    };

    $storageUrl = function (?string $path): ?string {
        if (! $path) {
            return null;
        }

        return str_starts_with($path, 'http') ? $path : asset('storage/' . ltrim($path, '/'));
    };

    $youtubeEmbed = function (?string $url): ?string {
        if (! $url) {
            return null;
        }

        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return 'https://www.youtube-nocookie.com/embed/' . $matches[1];
        }

        return null;
    };
@endphp

@foreach ($blocks as $block)
    @php
        $data = $block->content ?? [];
        $type = $block->type->value ?? $block->type;
    @endphp

    @switch($type)
        @case('hero')
            <x-hero
                :title="$data['headline'] ?? $block->title"
                :subtitle="$data['subtitle'] ?? null"
                :eyebrow="$data['eyebrow'] ?? null"
                :badge="$data['badge'] ?? 'UK Parish'"
                :image="$data['image'] ?? null"
                :stats="$data['stats'] ?? []"
                size="large"
                :show-side-panel="true"
            >
                @if (! empty($data['primary_cta_label']))
                    <x-button href="{{ $resolveLink($data['primary_cta_url'] ?? '#') }}" hero>
                        {{ $data['primary_cta_label'] }}
                    </x-button>
                @endif
                @if (! empty($data['secondary_cta_label']))
                    <x-button href="{{ $resolveLink($data['secondary_cta_url'] ?? '#') }}" hero variant="outline">
                        {{ $data['secondary_cta_label'] }}
                    </x-button>
                @endif
                @if (! empty($data['tertiary_cta_label']))
                    <x-button href="{{ $resolveLink($data['tertiary_cta_url'] ?? '#') }}" hero variant="outline">
                        {{ $data['tertiary_cta_label'] }}
                    </x-button>
                @endif
            </x-hero>
            <x-scripture-ribbon text="God is spirit, and his worshippers must worship in the Spirit and in truth." reference="John 4:24" />
            <x-heavenly-comfort
                :heading="$faithComfortHeading ?? null"
                :subheading="$faithComfortSubheading ?? null"
                :kicker="$faithComfortKicker ?? null"
                :cards="$faithComfortCards ?? []"
            />
            <x-faith-whispers variant="home" />
            @break

        @case('text_image')
        @case('image_text')
            @php $imageFirst = $type === 'image_text'; @endphp
            <section class="page-section page-section--compact">
                <div class="page-section-inner mx-auto max-w-7xl">
                    <div class="grid items-center gap-6 lg:grid-cols-2 lg:gap-10 {{ $imageFirst ? '' : '' }}">
                        @if ($imageFirst)
                            <div class="relative aspect-[4/3] overflow-hidden rounded-2xl bg-[var(--site-surface-2)] shadow-lg">
                                @if ($storageUrl($data['image'] ?? null))
                                    <img src="{{ $storageUrl($data['image']) }}" alt="{{ $data['image_alt'] ?? $data['heading'] ?? '' }}" loading="lazy" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full items-center justify-center bg-gradient-to-br from-[var(--site-brand-dark)] to-[var(--site-brand)]">
                                        <svg class="h-16 w-16 text-brand/40" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                    </div>
                                @endif
                            </div>
                        @endif
                        <div>
                            @if (! empty($data['heading']))
                                <x-section-heading :title="$data['heading']" :subtitle="$data['subheading'] ?? null" align="left" class="!mb-6" />
                            @endif
                            @if (! empty($data['body']))
                                <div class="prose-church">{!! safeHtml($data['body']) !!}</div>
                            @endif
                            @if (! empty($data['link_label']))
                                <x-button href="{{ $resolveLink($data['link_url'] ?? '#') }}" variant="secondary" class="mt-6">
                                    {{ $data['link_label'] }}
                                </x-button>
                            @endif
                        </div>
                        @unless ($imageFirst)
                            <div class="relative aspect-[4/3] overflow-hidden rounded-2xl bg-[var(--site-surface-2)] shadow-lg">
                                @if ($storageUrl($data['image'] ?? null))
                                    <img src="{{ $storageUrl($data['image']) }}" alt="{{ $data['image_alt'] ?? $data['heading'] ?? '' }}" loading="lazy" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full items-center justify-center bg-gradient-to-br from-[var(--site-brand-dark)] to-[var(--site-brand)]">
                                        <svg class="h-16 w-16 text-brand/40" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                    </div>
                                @endif
                            </div>
                        @endunless
                    </div>
                </div>
            </section>
            @break

        @case('cta')
            @php
                $style = $data['style'] ?? 'primary';
                $isPrimary = $style === 'primary';
            @endphp
            <section class="page-section page-section--compact">
                <div class="page-section-inner mx-auto max-w-7xl">
                    <div @class([
                        'cta-gen-z overflow-hidden rounded-3xl px-6 py-12 text-center sm:px-12 sm:py-16',
                        'cta-gen-z--primary' => $isPrimary,
                        'cta-gen-z--secondary card-modern' => ! $isPrimary,
                    ])>
                        <x-section-heading
                            :title="$data['heading'] ?? $block->title"
                            :subtitle="$data['body'] ?? null"
                            :light="$isPrimary"
                            class="!mb-8"
                        />
                        @if (! empty($data['button_label']))
                            <x-button
                                href="{{ $resolveLink($data['button_url'] ?? '#') }}"
                                :variant="$isPrimary ? 'primary' : 'secondary'"
                            >
                                {{ $data['button_label'] }}
                            </x-button>
                        @endif
                    </div>
                </div>
            </section>
            @break

        @case('ministry_cards')
            @php $items = $ministries->take($data['limit'] ?? 4); @endphp
            <section class="page-section page-section--compact">
                <div class="page-section-inner mx-auto max-w-7xl">
                    <x-section-heading :title="$data['heading'] ?? 'Our Ministries'" :subtitle="$data['subheading'] ?? null" />
                    <div class="bento-grid bento-grid--ministries mt-8">
                        @forelse ($items as $ministry)
                            <a href="{{ route('ministries.show', $ministry->slug) }}" class="bento-tile group">
                                <div class="bento-tile-media">
                                    @if ($storageUrl($ministry->featured_image))
                                        <img src="{{ $storageUrl($ministry->featured_image) }}" alt="{{ $ministry->name }}" loading="lazy" decoding="async" class="bento-tile-image">
                                    @else
                                        <div class="bento-tile-fallback"><span>{{ substr($ministry->name, 0, 1) }}</span></div>
                                    @endif
                                </div>
                                <div class="bento-tile-body">
                                    <span class="feed-sticker feed-sticker--inline">Ministry</span>
                                    <h3 class="bento-tile-title">{{ $ministry->name }}</h3>
                                    @if ($ministry->short_description)
                                        <p class="bento-tile-desc">{{ $ministry->short_description }}</p>
                                    @endif
                                    <span class="bento-tile-link">Explore →</span>
                                </div>
                            </a>
                        @empty
                            <p class="feed-empty col-span-full">Ministries will appear here soon.</p>
                        @endforelse
                    </div>
                    @if (! empty($data['link_label']))
                        <div class="mt-10 text-center">
                            <x-button href="{{ $resolveLink($data['link_url'] ?? route('ministries.index')) }}" variant="outline">
                                {{ $data['link_label'] }}
                            </x-button>
                        </div>
                    @endif
                </div>
            </section>
            @break

        @case('event_list')
            @php $items = $events->take($data['limit'] ?? 3); @endphp
            <section class="page-section page-section--compact">
                <div class="page-section-inner mx-auto max-w-7xl">
                    <x-section-heading :title="$data['heading'] ?? 'Upcoming Events'" />
                    <div class="feed-grid mt-8">
                        @forelse ($items as $event)
                            <x-card
                                href="{{ route('events.show', $event->slug) }}"
                                :padding="false"
                                @class(['feed-card overflow-hidden', 'feed-card--featured' => $loop->first])
                            >
                                <div class="feed-card-media">
                                    @if ($storageUrl($event->featured_image))
                                        <img src="{{ $storageUrl($event->featured_image) }}" alt="" class="feed-card-image" loading="lazy" decoding="async">
                                    @else
                                        <div class="feed-card-fallback feed-card-fallback--event">
                                            <span class="feed-date-day">{{ $event->starts_at->format('d') }}</span>
                                            <span class="feed-date-month">{{ $event->starts_at->format('M') }}</span>
                                        </div>
                                    @endif
                                    <span class="feed-sticker">{{ $event->category ?? 'Event' }}</span>
                                </div>
                                <div class="feed-card-body">
                                    <time datetime="{{ $event->starts_at->toIso8601String() }}" class="feed-meta">
                                        {{ $event->starts_at->format('l, j F') }}
                                    </time>
                                    <h3 class="feed-card-title">{{ $event->title }}</h3>
                                    @if ($event->location)
                                        <p class="feed-card-desc">{{ $event->location }}</p>
                                    @endif
                                    <span class="feed-card-cta">View details →</span>
                                </div>
                            </x-card>
                        @empty
                            <p class="feed-empty">No upcoming events at this time.</p>
                        @endforelse
                    </div>
                    @if (! empty($data['link_label']))
                        <div class="mt-10 text-center">
                            <x-button href="{{ $resolveLink($data['link_url'] ?? route('events.index')) }}" variant="outline">
                                {{ $data['link_label'] }}
                            </x-button>
                        </div>
                    @endif
                </div>
            </section>
            @break

        @case('sermon_list')
            @php $items = $sermons->take($data['limit'] ?? 3); @endphp
            <section class="page-section page-section--compact">
                <div class="page-section-inner mx-auto max-w-7xl">
                    <x-section-heading :title="$data['heading'] ?? 'Recent Sermons'" />
                    <div class="sermon-stack mt-8">
                        @forelse ($items as $sermon)
                            <x-card class="sermon-card">
                                <div class="sermon-card-top">
                                    <div class="sermon-card-icon" aria-hidden="true">
                                        <svg class="h-7 w-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6.75 6.75 0 006.75-6.75v-1.5m-6.75 1.5c-1.357 0-2.573.516-3.5 1.35m0 0c-1.128 1.019-2.25 1.519-3.5 1.519m9 2.25c-1.357 0-2.573-.516-3.5-1.35m0 0c-1.128-1.019-2.25-1.519-3.5-1.519m0 0V21m0-3.375c0-1.357.516-2.573 1.35-3.5m0 0c1.019-1.128 1.519-2.25 1.519-3.5"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        @if ($sermon->bible_passage)
                                            <span class="feed-sticker feed-sticker--inline">{{ $sermon->bible_passage }}</span>
                                        @endif
                                        <h3 class="sermon-card-title">{{ $sermon->title }}</h3>
                                        <p class="sermon-card-meta">{{ $sermon->speaker }} · {{ $sermon->preached_at?->format('j M Y') }}</p>
                                    </div>
                                </div>
                                <div class="sermon-card-actions">
                                    @if ($sermon->youtube_url)
                                        <x-button href="{{ $sermon->youtube_url }}" variant="primary" class="!min-h-11 !text-sm" target="_blank" rel="noopener noreferrer">Watch</x-button>
                                    @endif
                                </div>
                            </x-card>
                        @empty
                            <p class="feed-empty">Sermons will appear here soon.</p>
                        @endforelse
                    </div>
                    @if (! empty($data['link_label']))
                        <div class="mt-10 text-center">
                            <x-button href="{{ $resolveLink($data['link_url'] ?? route('sermons.index')) }}" variant="outline">
                                {{ $data['link_label'] }}
                            </x-button>
                        </div>
                    @endif
                </div>
            </section>
            @break

        @case('gallery')
            @php $items = $albums->take($data['limit'] ?? 6); @endphp
            <section class="page-section page-section--compact">
                <div class="page-section-inner mx-auto max-w-7xl">
                    <x-section-heading :title="$data['heading'] ?? 'Gallery'" />
                    <div class="gallery-mosaic mt-8">
                        @forelse ($items as $album)
                            <x-card :href="route('gallery.show', $album->slug)" :padding="false" class="gallery-tile overflow-hidden">
                                <div class="gallery-tile-media">
                                    @php
                                        $coverVariant = str_contains(strtolower($album->slug), 'fellowship') ? 'fellowship' : 'worship';
                                    @endphp
                                    <img src="{{ galleryCoverUrl($album->cover_image, $coverVariant) }}" alt="{{ $album->title }}" loading="lazy" decoding="async" class="gallery-tile-image">
                                    <div class="gallery-tile-overlay">
                                        <span class="feed-sticker">Album</span>
                                        <h3 class="gallery-tile-title">{{ $album->title }}</h3>
                                    </div>
                                </div>
                            </x-card>
                        @empty
                            <p class="feed-empty col-span-full">Gallery albums coming soon.</p>
                        @endforelse
                    </div>
                    @if (! empty($data['link_label']))
                        <div class="mt-10 text-center">
                            <x-button href="{{ $resolveLink($data['link_url'] ?? route('gallery.index')) }}" variant="outline">
                                {{ $data['link_label'] }}
                            </x-button>
                        </div>
                    @endif
                </div>
            </section>
            @break

        @case('quote')
            <section class="page-section page-section--compact">
                <div class="mx-auto max-w-4xl page-section-inner">
                    <blockquote class="quote-gen-z">
                        <span class="quote-gen-z-mark" aria-hidden="true">"</span>
                        <p class="quote-gen-z-text">{{ $data['quote'] ?? '' }}</p>
                        @if (! empty($data['attribution']))
                            <footer class="quote-gen-z-footer">— {{ $data['attribution'] }}</footer>
                        @endif
                        @if (! empty($data['link_label']))
                            <div class="mt-8">
                                <x-button href="{{ $resolveLink($data['link_url'] ?? '#') }}" variant="outline">
                                    {{ $data['link_label'] }}
                                </x-button>
                            </div>
                        @endif
                    </blockquote>
                </div>
            </section>
            @break

        @case('faq')
                <section class="page-section page-section--blessed page-section--compact">
                <div class="page-section-inner mx-auto max-w-3xl">
                    @if (! empty($data['heading']))
                        <x-section-heading :title="$data['heading']" />
                    @endif
                    <div class="faq-heavenly space-y-3" x-data="{ open: 0 }">
                        @foreach ($data['items'] ?? [] as $index => $item)
                            <div class="faq-heavenly-item">
                                <button
                                    type="button"
                                    class="faq-heavenly-trigger"
                                    @click="open = open === {{ $index }} ? null : {{ $index }}"
                                    :aria-expanded="open === {{ $index }}"
                                >
                                    {{ $item['question'] ?? '' }}
                                    <svg class="h-5 w-5 shrink-0 text-brand transition" :class="open === {{ $index }} && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                                </button>
                                <div
                                    x-show="open === {{ $index }}"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    class="faq-heavenly-panel"
                                    x-cloak
                                >
                                    <div class="prose-church prose-church--compact pt-1">{!! safeHtml($item['answer'] ?? '') !!}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
            @break

        @case('location')
            @php
                $locationNames = collect($data['locations'] ?? [])
                    ->whenEmpty(fn () => $services->pluck('location')->filter()->unique());
                $serviceByLocation = $services->keyBy('location');
                $locationsData = $locationNames->map(function ($name) use ($serviceByLocation) {
                    $label = is_string($name) ? $name : (string) $name;

                    return [
                        'name' => $label,
                        'service' => $serviceByLocation->get($label),
                    ];
                })->values();
            @endphp
            @if ($locationsData->isNotEmpty())
                <section class="page-section page-section--blessed page-section--compact" data-location-tabs>
                    <div class="page-section-inner mx-auto max-w-7xl">
                        <x-section-heading :title="$data['heading'] ?? 'Locations'" :subtitle="$data['subheading'] ?? null" />

                        <div class="location-tabs mt-8" role="tablist" aria-label="UK worship locations">
                            @foreach ($locationsData as $index => $location)
                                <button
                                    type="button"
                                    role="tab"
                                    class="location-tab {{ $index === 0 ? 'is-active' : '' }}"
                                    data-location-tab
                                    data-location-index="{{ $index }}"
                                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                                    aria-controls="location-panel-{{ $block->id ?? 'block' }}-{{ $index }}"
                                    id="location-tab-{{ $block->id ?? 'block' }}-{{ $index }}"
                                >
                                    {{ $location['name'] }}
                                </button>
                            @endforeach
                        </div>

                        @foreach ($locationsData as $index => $location)
                            @php $service = $location['service']; @endphp
                            <div
                                role="tabpanel"
                                class="location-panel"
                                data-location-panel
                                data-location-index="{{ $index }}"
                                id="location-panel-{{ $block->id ?? 'block' }}-{{ $index }}"
                                aria-labelledby="location-tab-{{ $block->id ?? 'block' }}-{{ $index }}"
                                @if ($index !== 0) hidden @endif
                            >
                                <div class="flex items-start gap-4">
                                    <div class="location-panel-icon shrink-0">
                                        <svg class="h-7 w-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-bold text-2xl text-ink">{{ $location['name'] }}</h3>
                                        @if ($service?->frequency)
                                            <p class="mt-1 text-sm font-medium text-brand">{{ $service->frequency }}</p>
                                        @endif
                                        @if ($service?->description)
                                            <p class="mt-3 text-sm leading-relaxed text-ink-muted">{{ $service->description }}</p>
                                        @endif
                                    </div>
                                </div>

                                @if ($service && ($service->service_day || $service->service_time || $service->formattedAddress()))
                                    <dl class="mt-6 grid gap-3 sm:grid-cols-2">
                                        @if ($service->service_day || $service->service_time)
                                            <div class="rounded-xl bg-[var(--site-surface-2)] px-4 py-3">
                                                <dt class="text-xs font-semibold uppercase tracking-wider text-ink-muted">When</dt>
                                                <dd class="mt-1 text-sm font-medium text-ink">
                                                    {{ trim(($service->service_day ?? '') . ' · ' . ($service->service_time ?? ''), ' ·') }}
                                                </dd>
                                            </div>
                                        @endif
                                        @if ($service->formattedAddress())
                                            <div class="rounded-xl bg-[var(--site-surface-2)] px-4 py-3 sm:col-span-2">
                                                <dt class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Address</dt>
                                                <dd class="mt-1 text-sm text-ink">{{ $service->formattedAddress() }}</dd>
                                            </div>
                                        @endif
                                    </dl>
                                @endif

                                <div class="mt-6 flex flex-wrap gap-3">
                                    @if ($service?->map_link)
                                        <x-button href="{{ $service->map_link }}" variant="outline" class="!min-h-11 !w-auto !px-4 !py-2 !text-sm" target="_blank" rel="noopener noreferrer">View Map</x-button>
                                    @endif
                                    <x-button href="{{ $resolveLink($data['link_url'] ?? route('services.index')) }}" variant="secondary" class="!min-h-11 !w-auto !px-4 !py-2 !text-sm">
                                        {{ $data['link_label'] ?? 'All Service Times' }}
                                    </x-button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
            @break

        @case('youtube')
            @php $embed = $youtubeEmbed($data['url'] ?? null); @endphp
            @if ($embed)
                <section class="page-section page-section--compact">
                    <div class="page-section-inner mx-auto max-w-5xl">
                        @if (! empty($data['heading']))
                            <x-section-heading :title="$data['heading']" :subtitle="$data['subheading'] ?? null" />
                        @endif
                        <div class="aspect-video overflow-hidden rounded-2xl shadow-lg ring-1 border border-[var(--site-border)]">
                            <iframe
                                src="{{ $embed }}"
                                title="{{ $data['heading'] ?? 'Video' }}"
                                class="h-full w-full"
                                loading="lazy"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            ></iframe>
                        </div>
                    </div>
                </section>
            @endif
            @break

        @case('map')
            <section class="page-section page-section--compact">
                <div class="page-section-inner mx-auto max-w-7xl">
                    @if (! empty($data['heading']))
                        <x-section-heading :title="$data['heading']" />
                    @endif
                    <div class="overflow-hidden rounded-2xl shadow-lg ring-1 border border-[var(--site-border)]">
                        @if (! empty($data['embed']))
                            <div class="aspect-video">{!! safeEmbed($data['embed']) ?: '<div class="feed-empty !rounded-none !border-0">Map unavailable</div>' !!}</div>
                        @elseif ($googleMapsEmbed ?? null)
                            <div class="aspect-video">{!! safeEmbed($googleMapsEmbed) ?: '<div class="feed-empty !rounded-none !border-0">Map unavailable</div>' !!}</div>
                        @else
                            <div class="feed-empty !rounded-none !border-0">Map unavailable</div>
                        @endif
                    </div>
                </div>
            </section>
            @break

        @case('contact')
            <section class="page-section page-section--compact">
                <div class="page-section-inner mx-auto max-w-2xl">
                    @if (! empty($data['heading']))
                        <x-section-heading :title="$data['heading']" />
                    @endif
                    @livewire('forms.contact-form')
                </div>
            </section>
            @break

        @case('downloads')
            <section class="page-section page-section--compact">
                <div class="page-section-inner mx-auto max-w-7xl">
                    @if (! empty($data['heading']))
                        <x-section-heading :title="$data['heading']" />
                    @endif
                    <div class="text-center">
                        <x-button href="{{ $resolveLink($data['link_url'] ?? route('resources.index')) }}" variant="secondary">
                            {{ $data['link_label'] ?? 'Browse Resources' }}
                        </x-button>
                    </div>
                </div>
            </section>
            @break

        @default
            @if ($block->title)
                <section class="py-8 sm:py-10">
                    <div class="page-section-inner mx-auto max-w-7xl">
                        <x-section-heading :title="$block->title" />
                        @if (is_array($data) && ! empty($data['body']))
                            <div class="prose-church prose-church--page mx-auto max-w-3xl">{!! safeHtml($data['body']) !!}</div>
                        @endif
                    </div>
                </section>
            @endif
    @endswitch
@endforeach
