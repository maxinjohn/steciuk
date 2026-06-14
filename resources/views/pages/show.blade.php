@extends('layouts.app')

@section('title', \App\Support\Seo::documentTitle($page?->seo_title ?? $page?->title, null, $siteName))
@section('description', $page?->seo_description ?? $siteMotto)

@section('content')
    @if (! $page->show_hero)
        <x-page-intro
            :title="$page->hero_title ?? $page->title"
            :subtitle="$page->hero_subtitle"
            :art-slug="$page->slug"
            :art-title="$page->hero_title ?? $page->title"
            :art-context="\App\Support\PageTopicArt::contextForPage($page)"
            :art-content="\App\Support\PageTopicArt::contentHintForPage($page)"
            :show-strips="true"
            :show-trust-bar="true"
        />
    @endif

    <x-page-shell :page="$page" />
@endsection
