@extends('layouts.app')

@section('title', $page?->seo_title ?? $page?->title ?? 'Page')
@section('description', $page?->seo_description ?? $siteMotto)

@section('content')
    @if (! $page->show_hero && ($page->hero_title || $page->hero_subtitle))
        <x-page-intro
            :title="$page->hero_title ?? $page->title"
            :subtitle="$page->hero_subtitle"
        />
    @endif

    <x-page-shell :page="$page" />
@endsection
