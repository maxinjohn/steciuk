@extends('layouts.app')

@section('title', \App\Support\Seo::documentTitle($page?->seo_title ?? 'Ministries', null, $siteName))
@section('description', $page?->seo_description ?? 'Discover ministries at STECI UK Parish')

@section('content')
    <x-page-shell :page="$page" suppress-content suppress-hero>
        <x-breadcrumbs :items="[['label' => 'Ministries', 'current' => true]]" />
        <x-page-intro
            :title="$page?->hero_title ?? 'Our Ministries'"
            :subtitle="$page?->hero_subtitle ?? 'Sunday School, prayer, choir, and outreach — serving Christ together'"
            kicker="Evangelical Oriental Protestant · Serve"
            scripture="To equip his people for works of service, so that the body of Christ may be built up."
            scripture-ref="Ephesians 4:12"
            art-slug="ministries"
            :art-title="$page?->hero_title ?? 'Our Ministries'"
            art-context="ministry"
            :show-strips="true"
            :show-trust-bar="true"
        />

        <section class="page-section page-section--compact">
            <div class="page-section-inner mx-auto max-w-7xl">
                <div class="bento-grid bento-grid--ministries">
                    @forelse ($ministries as $ministry)
                        <x-ministry-card :ministry="$ministry" heading-tag="h2" />
                    @empty
                        <x-heavenly-empty
                            title="Ministries coming soon"
                            context="ministries"
                            :action-href="route('ministries.index')"
                            action-label="View ministries"
                        >
                            Explore how you can serve, connect, and grow in parish life.
                        </x-heavenly-empty>
                    @endforelse
                </div>
            </div>
        </section>
    </x-page-shell>
@endsection
