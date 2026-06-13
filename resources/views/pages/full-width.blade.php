@extends('layouts.app')

@section('title', $page->seo_title ?? $page->title . ' | ' . $siteName)
@section('description', $page->seo_description ?? strip_tags($page->content))

@section('content')
    <x-hero
        :title="$page->hero_title ?? $page->title"
        :subtitle="$page->hero_subtitle"
        :image="$page->featured_image"
        size="small"
    />

    @if ($page->content)
        <section class="page-section page-section--article py-10 sm:py-12 md:py-16">
            <div class="page-section-inner mx-auto max-w-5xl">
                <div class="prose-church prose-church--page">{!! safeHtml($page->content) !!}</div>
            </div>
        </section>
    @endif

    @if ($page->contentBlocks->isNotEmpty())
        <x-content-blocks :blocks="$page->contentBlocks" />
    @endif
@endsection
