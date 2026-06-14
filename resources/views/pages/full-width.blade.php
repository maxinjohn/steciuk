@extends('layouts.app')

@section('title', \App\Support\Seo::documentTitle($page->seo_title ?? $page->title, null, $siteName))
@section('description', $page->seo_description ?? strip_tags($page->content))

@section('content')
    <x-hero
        :title="$page->hero_title ?? $page->title"
        :subtitle="$page->hero_subtitle"
        :image="$page->featured_image"
        size="small"
        :art-slug="$page->slug"
        :art-title="$page->hero_title ?? $page->title"
        :art-context="\App\Support\PageTopicArt::contextForPage($page)"
        :art-content="\App\Support\PageTopicArt::contentHintForPage($page)"
    />

    <x-faith-page-bridge />
    <x-scripture-ribbon
        text="Your word is a lamp to my feet and a light to my path."
        reference="Psalm 119:105"
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
