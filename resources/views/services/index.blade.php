@extends('layouts.app')

@section('title', $page?->seo_title ?? 'Service Times')
@section('description', $page?->seo_description ?? 'Worship locations across the UK Parish')

@section('content')
    <x-page-shell :page="$page" suppress-content>
        <x-breadcrumbs :items="[['label' => 'Service times', 'current' => true]]" />
        <x-page-intro
            title="Holy Communion & Worship"
            subtitle="Monthly evangelical Oriental Protestant worship across five UK cities"
            kicker="Plan your visit"
            scripture="God is spirit, and his worshippers must worship in the Spirit and in truth."
            scripture-ref="John 4:24"
            art-slug="service-times"
            art-title="Holy Communion & Worship"
            art-context="service"
        />
        <x-worship-rhythm />

        <section class="page-section page-section--compact">
            <div class="page-section-inner mx-auto max-w-7xl">
                <div class="location-grid">
                    @forelse ($services as $service)
                        <x-service-location-card :service="$service" />
                    @empty
                        <p class="feed-empty">Service times will be published here soon.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </x-page-shell>
@endsection
