@extends('layouts.app')

@section('title', $page?->seo_title ?? 'News')
@section('description', $page?->seo_description ?? 'Latest news from STECI UK Parish')

@section('content')
    <x-page-shell :page="$page" suppress-content>
        <x-page-intro
            title="Parish News"
            subtitle="Gospel-centred news — worship, prayer, mission, and fellowship across Britain"
            kicker="Evangelical Oriental Protestant · UK"
            scripture="They devoted themselves to the apostles' teaching and to fellowship, to the breaking of bread and to prayer."
            scripture-ref="Acts 2:42"
        />

        <section class="page-section py-10 sm:py-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="feed-grid feed-grid--news">
                    @forelse ($articles as $article)
                        <x-card
                            :href="route('news.show', $article->slug)"
                            :padding="false"
                            @class([
                                'feed-card overflow-hidden',
                                'feed-card--featured' => $loop->first,
                            ])
                        >
                            <div class="feed-card-media">
                                @if ($article->featured_image)
                                    <img src="{{ asset('storage/' . ltrim($article->featured_image, '/')) }}" alt="{{ $article->title }}" loading="lazy" decoding="async" class="feed-card-image">
                                @else
                                    <div class="feed-card-fallback feed-card-fallback--news">
                                        <span class="feed-date-day">{{ $article->published_at?->format('d') }}</span>
                                        <span class="feed-date-month">{{ $article->published_at?->format('M') }}</span>
                                    </div>
                                @endif
                                @if ($article->category)
                                    <span class="feed-sticker feed-sticker--violet">{{ $article->category }}</span>
                                @else
                                    <span class="feed-sticker feed-sticker--violet">News</span>
                                @endif
                            </div>
                            <div class="feed-card-body">
                                <time datetime="{{ $article->published_at?->toIso8601String() }}" class="feed-meta">
                                    {{ $article->published_at?->format('j F Y') }}
                                </time>
                                <h2 class="feed-card-title">{{ $article->title }}</h2>
                                @if ($article->excerpt)
                                    <p class="feed-card-desc line-clamp-3">{{ $article->excerpt }}</p>
                                @endif
                                <span class="feed-card-cta">Read more →</span>
                            </div>
                        </x-card>
                    @empty
                        <p class="feed-empty">No news articles yet.</p>
                    @endforelse
                </div>

                @if ($articles->hasPages())
                    <div class="mt-10">{{ $articles->links() }}</div>
                @endif
            </div>
        </section>
    </x-page-shell>
@endsection
