@extends('layouts.app')

@section('title', $page?->seo_title ?? 'Sermons')
@section('description', $page?->seo_description ?? 'Biblical teaching from STECI UK Parish')

@section('content')
    <x-page-shell :page="$page" suppress-content>
        <x-breadcrumbs :items="[['label' => 'Sermons', 'current' => true]]" />
        <x-page-intro
            title="Expository Preaching"
            subtitle="Sermons from Holy Scripture — for the testimony of Jesus Christ"
            kicker="Word of God"
            scripture="Faith comes from hearing the message, and the message is heard through the word about Christ."
            scripture-ref="Romans 10:17"
            art-slug="sermons"
            art-title="Expository Preaching"
            art-context="sermon"
        />

        <section class="page-section page-section--compact">
            <div class="page-section-inner mx-auto max-w-7xl">
                <div class="sermon-stack">
                    @forelse ($sermons as $sermon)
                        <x-card class="sermon-card topic-card wow-card overflow-hidden">
                            <div class="sermon-card-layout">
                                <div class="sermon-card-media wow-card-media">
                                    <x-feed-card-media
                                        :image="$sermon->featured_image ?? null"
                                        :slug="$sermon->slug"
                                        :title="$sermon->title"
                                        context="sermon"
                                        :alt="$sermon->title"
                                        :sticker="$sermon->bible_passage"
                                        :day="$sermon->preached_at?->format('d')"
                                        :month="$sermon->preached_at?->format('M')"
                                        :weekday="$sermon->preached_at?->format('D')"
                                        :category="$sermon->category"
                                        :content="\App\Support\PageTopicArt::contentHintForRecord($sermon->description ?? null, $sermon->summary ?? null, null, null, $sermon->bible_passage)"
                                        :priority="$loop->first ? 'high' : 'lazy'"
                                    />
                                </div>
                                <div class="sermon-card-content">
                                    <div class="sermon-card-top">
                                        <div class="min-w-0 flex-1">
                                            @if ($sermon->bible_passage)
                                                <span class="feed-sticker feed-sticker--inline">{{ $sermon->bible_passage }}</span>
                                            @endif
                                            <h2 class="sermon-card-title">{{ $sermon->title }}</h2>
                                            <p class="sermon-card-meta">{{ $sermon->speaker }} · {{ $sermon->preached_at?->format('j F Y') }}</p>
                                        </div>
                                        @if ($sermon->youtube_url)
                                            <x-share-chip
                                                :url="$sermon->youtube_url"
                                                :title="$sermon->title"
                                            />
                                        @endif
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
                                </div>
                            </div>
                        </x-card>
                    @empty
                        <div class="feed-empty feed-empty--rich">
                            <p class="feed-empty__title">Sermons coming soon</p>
                            <p class="feed-empty__text">Recent messages will be listed here once published.</p>
                            <x-button href="{{ url('/sermons') }}" variant="outline" class="feed-empty__action">Browse sermons</x-button>
                        </div>
                    @endforelse
                </div>

                @if ($sermons->hasPages())
                    <div class="site-pagination">{{ $sermons->links() }}</div>
                @endif
            </div>
        </section>
    </x-page-shell>
@endsection
