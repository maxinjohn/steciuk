@extends('layouts.app')

@section('title', ($article->seo_title ?? $article->title) . ' | ' . $siteName)
@section('description', $article->seo_description ?? $article->excerpt ?? strip_tags($article->content))
@section('og_type', 'article')
@if ($article->featured_image)
    @section('og_image', \App\Support\Seo::absoluteAsset($article->featured_image))
@endif

@push('head')
    @php
        $articleSchema = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $article->title,
            'description' => \App\Support\Seo::truncateDescription($article->seo_description ?? $article->excerpt ?? strip_tags($article->content)),
            'datePublished' => $article->published_at?->toIso8601String(),
            'dateModified' => $article->updated_at->toIso8601String(),
            'url' => url()->current(),
            'image' => \App\Support\Seo::absoluteAsset($article->featured_image),
            'publisher' => [
                '@type' => 'Organization',
                'name' => $siteName,
            ],
        ], fn ($value) => $value !== null && $value !== '');
    @endphp
    <script type="application/ld+json">
        {!! json_encode($articleSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
    <article>
        <x-hero :title="$article->title" :subtitle="$article->excerpt" :image="$article->featured_image" size="small">
            @if ($article->published_at)
                <time datetime="{{ $article->published_at->toIso8601String() }}" class="hero-meta-chip">
                    {{ $article->published_at->format('j F Y') }}
                </time>
            @endif
        </x-hero>

        <section class="page-section page-section--article py-10 sm:py-12 md:py-16">
            <div class="page-section-inner mx-auto max-w-3xl">
                @if ($article->category)
                    <span class="site-category-pill">{{ $article->category }}</span>
                @endif

                <div class="prose-church prose-church--page">{!! safeHtml($article->content) !!}</div>

                <div class="site-divider mt-10 pt-8">
                    <x-button href="{{ route('news.index') }}" variant="outline">← Back to News</x-button>
                </div>
            </div>
        </section>
    </article>
@endsection
