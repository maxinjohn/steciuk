@extends('layouts.app')

@section('title', $page?->seo_title ?? 'News')
@section('description', $page?->seo_description ?? 'Latest news from STECI UK Parish')

@section('content')
    <x-page-shell :page="$page" suppress-content>
        <x-breadcrumbs :items="[['label' => 'News', 'current' => true]]" />
        <x-page-intro
            title="Parish News"
            subtitle="Gospel-centred news — worship, prayer, mission, and fellowship across Britain"
            kicker="Evangelical Oriental Protestant · UK"
            scripture="They devoted themselves to the apostles' teaching and to fellowship, to the breaking of bread and to prayer."
            scripture-ref="Acts 2:42"
            art-slug="news"
            art-title="Parish News"
            art-context="news"
        />

        <section class="page-section page-section--compact">
            <div class="page-section-inner mx-auto max-w-7xl">
                <div class="feed-grid feed-grid--news feed-rail">
                    @forelse ($articles as $article)
                        <x-card
                            :href="route('news.show', $article->slug)"
                            :padding="false"
                            @class([
                                'feed-card topic-card wow-card overflow-hidden',
                                'feed-card--featured' => $loop->first,
                            ])
                        >
                            <x-feed-card-media
                                :image="$article->featured_image"
                                :slug="$article->slug"
                                :title="$article->title"
                                context="news"
                                :alt="$article->title"
                                :sticker="$article->category ?: 'News'"
                                sticker-class="feed-sticker--violet"
                                :day="$article->published_at?->format('d')"
                                :month="$article->published_at?->format('M')"
                                :weekday="$article->published_at?->format('D')"
                                :category="$article->category"
                                :content="\App\Support\PageTopicArt::contentHintForRecord($article->body ?? null, $article->excerpt, null, null, $article->category)"
                                :priority="$loop->first ? 'high' : 'lazy'"
                            />
                            <div class="feed-card-body">
                                <div class="feed-card-head">
                                    <time datetime="{{ $article->published_at?->toIso8601String() }}" class="feed-meta">
                                        {{ $article->published_at?->format('j F Y') }}
                                    </time>
                                    <x-share-chip
                                        :url="route('news.show', $article->slug)"
                                        :title="$article->title"
                                    />
                                </div>
                                <h2 class="feed-card-title">{{ $article->title }}</h2>
                                @if ($article->excerpt)
                                    <p class="feed-card-desc line-clamp-3">{{ $article->excerpt }}</p>
                                @endif
                                <span class="feed-card-cta">Read more →</span>
                            </div>
                        </x-card>
                    @empty
                        <x-heavenly-empty title="News coming soon" context="news">
                            Parish updates and stories will appear here as they are published.
                        </x-heavenly-empty>
                    @endforelse
                </div>

                @if ($articles->hasPages())
                    <div class="site-pagination">{{ $articles->links() }}</div>
                @endif
            </div>
        </section>
    </x-page-shell>
@endsection
