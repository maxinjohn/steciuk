@extends('layouts.app')

@section('title', \App\Support\Seo::documentTitle($page?->seo_title ?? 'Service Times', null, $siteName))
@section('description', $page?->seo_description ?? 'Worship locations across the UK Parish')

@section('content')
    <x-page-shell :page="$page" suppress-content suppress-hero>
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
            :show-strips="true"
            :show-trust-bar="true"
        />
        <div class="page-section-inner mx-auto max-w-7xl px-4 pb-2 lg:hidden">
            <x-next-worship-chip :chip="$nextWorshipChip ?? null" />
        </div>
        <x-worship-rhythm />

        <section class="page-section page-section--compact">
            <div class="page-section-inner mx-auto max-w-7xl">
                <div class="location-grid">
                    @forelse ($services as $service)
                        <x-service-location-card :service="$service" />
                    @empty
                        <x-heavenly-empty title="Service times coming soon" context="services" class="col-span-full">
                            Worship locations and Holy Communion times will be published here soon.
                        </x-heavenly-empty>
                    @endforelse
                </div>
            </div>
        </section>
    </x-page-shell>
@endsection
