@extends('layouts.app')

@section('title', $page?->seo_title ?? 'Gallery')
@section('description', $page?->seo_description ?? 'Photo gallery from STECI UK Parish')

@section('content')
    <x-page-shell :page="$page" suppress-content>
        <x-breadcrumbs :items="[['label' => 'Gallery', 'current' => true]]" />
        <x-page-intro
            title="Worship & Fellowship"
            subtitle="Holy Communion, prayer, and parish life in pictures"
            kicker="Saint Thomas heritage"
            scripture="Come, let us bow down in worship, let us kneel before the Lord our Maker."
            scripture-ref="Psalm 95:6"
            art-slug="gallery"
            art-title="Worship & Fellowship"
            art-context="gallery"
        />

        <section class="page-section page-section--compact">
            <div class="page-section-inner mx-auto max-w-7xl">
                <div class="gallery-mosaic">
                    @forelse ($albums as $album)
                        <x-gallery-tile-card :album="$album" />
                    @empty
                        <p class="feed-empty">Gallery albums coming soon.</p>
                    @endforelse
                </div>

                @if ($albums->hasPages())
                    <div class="site-pagination">{{ $albums->links() }}</div>
                @endif
            </div>
        </section>
    </x-page-shell>
@endsection
