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
                <time datetime="{{ $article->published_at->toIso8601String() }}" class="inline-flex items-center rounded-xl bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm">
                    {{ $article->published_at->format('j F Y') }}
                </time>
            @endif
        </x-hero>

        <section class="py-12 sm:py-16">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                @if ($article->category)
                    <span class="mb-6 inline-block rounded-full bg-gold/15 px-4 py-1 text-sm font-medium text-brand-dark">{{ $article->category }}</span>
                @endif

                <div class="prose-church">{!! safeHtml($article->content) !!}</div>

                <div class="mt-10 border-t border-navy/10 pt-8">
                    <x-button href="{{ route('news.index') }}" variant="outline">← Back to News</x-button>
                </div>
            </div>
        </section>
    </article>
@endsection
