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
        <section class="py-12 sm:py-16">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="prose-church">{!! safeHtml($page->content) !!}</div>
            </div>
        </section>
    @endif

    @if ($page->contentBlocks->isNotEmpty())
        <x-content-blocks :blocks="$page->contentBlocks" />
    @endif
@endsection
