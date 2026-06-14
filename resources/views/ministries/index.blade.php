@extends('layouts.app')

@section('title', $page?->seo_title ?? 'Ministries')
@section('description', $page?->seo_description ?? 'Discover ministries at STECI UK Parish')

@section('content')
    <x-page-shell :page="$page" suppress-content>
        <x-breadcrumbs :items="[['label' => 'Ministries', 'current' => true]]" />
        <x-page-intro
            title="Ministries & Mission"
            subtitle="Sunday School, prayer, choir, and outreach — serving Christ together"
            kicker="Evangelical Oriental Protestant · Serve"
            scripture="To equip his people for works of service, so that the body of Christ may be built up."
            scripture-ref="Ephesians 4:12"
            art-slug="ministries"
            art-title="Ministries & Mission"
            art-context="ministry"
        />

        <section class="page-section page-section--compact">
            <div class="page-section-inner mx-auto max-w-7xl">
                <div class="bento-grid bento-grid--ministries">
                    @forelse ($ministries as $ministry)
                        <x-ministry-card :ministry="$ministry" heading-tag="h2" />
                    @empty
                        <div class="feed-empty feed-empty--rich col-span-full">
                            <p class="feed-empty__title">Ministries coming soon</p>
                            <p class="feed-empty__text">Explore how you can serve, connect, and grow in parish life.</p>
                            <x-button href="{{ route('ministries.index') }}" variant="outline" class="feed-empty__action">View ministries</x-button>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </x-page-shell>
@endsection
