@extends('layouts.app')

@section('title', $page?->seo_title ?? 'Sermons')
@section('description', $page?->seo_description ?? 'Biblical teaching from STECI UK Parish')

@section('content')
    <x-page-shell :page="$page">
        @if (! $page?->hero_title)
            <x-page-band title="Sermons & Messages" subtitle="Biblical teaching from our parish preachers" kicker="Watch & listen" />
        @endif

        <section class="page-section py-10 sm:py-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="sermon-stack">
                    @forelse ($sermons as $sermon)
                        <x-card class="sermon-card">
                            <div class="sermon-card-top">
                                <div class="sermon-card-icon" aria-hidden="true">
                                    <svg class="h-7 w-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6.75 6.75 0 006.75-6.75v-1.5m-6.75 1.5c-1.357 0-2.573.516-3.5 1.35m0 0c-1.128 1.019-2.25 1.519-3.5 1.519m9 2.25c-1.357 0-2.573-.516-3.5-1.35m0 0c-1.128-1.019-2.25-1.519-3.5-1.519m0 0V21m0-3.375c0-1.357.516-2.573 1.35-3.5m0 0c1.019-1.128 1.519-2.25 1.519-3.5"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    @if ($sermon->bible_passage)
                                        <span class="feed-sticker feed-sticker--inline">{{ $sermon->bible_passage }}</span>
                                    @endif
                                    <h2 class="sermon-card-title">{{ $sermon->title }}</h2>
                                    <p class="sermon-card-meta">{{ $sermon->speaker }} · {{ $sermon->preached_at?->format('j F Y') }}</p>
                                </div>
                            </div>

                            @if ($sermon->description)
                                <div class="sermon-card-desc line-clamp-2">{!! safeHtml($sermon->description) !!}</div>
                            @endif

                            <div class="sermon-card-actions">
                                @if ($sermon->youtube_url)
                                    <x-button href="{{ $sermon->youtube_url }}" variant="primary" class="!min-h-11 !text-sm" target="_blank" rel="noopener noreferrer">Watch</x-button>
                                @endif
                                @if ($sermon->getFirstMediaUrl('audio'))
                                    <x-button href="{{ $sermon->getFirstMediaUrl('audio') }}" variant="outline" class="!min-h-11 !text-sm">Listen</x-button>
                                @endif
                                @if ($sermon->getFirstMediaUrl('pdf'))
                                    <x-button href="{{ $sermon->getFirstMediaUrl('pdf') }}" variant="ghost" class="!min-h-11 !text-sm" target="_blank">Notes</x-button>
                                @endif
                            </div>
                        </x-card>
                    @empty
                        <p class="feed-empty">Sermons will appear here soon.</p>
                    @endforelse
                </div>

                @if ($sermons->hasPages())
                    <div class="mt-10">{{ $sermons->links() }}</div>
                @endif
            </div>
        </section>
    </x-page-shell>
@endsection
