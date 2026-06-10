@extends('layouts.app')

@section('title', $page?->seo_title ?? 'Resources')
@section('description', $page?->seo_description ?? 'Downloads and parish documents')

@section('content')
    <x-page-shell :page="$page" suppress-content>
        <x-page-intro
            title="Liturgy & Parish Forms"
            subtitle="Worship resources, prayer guides, and parish documents"
            kicker="Evangelical Oriental Protestant · Resources"
            scripture="Your word is a lamp to my feet and a light to my path."
            scripture-ref="Psalm 119:105"
        />

        <section class="page-section py-10 sm:py-14">
            <div class="page-section-inner mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                @forelse ($resources as $category => $items)
                    <div class="resource-group">
                        <div class="genz-kicker mb-4">
                            <span class="genz-kicker-dot" aria-hidden="true"></span>
                            @if ($category instanceof \App\Enums\ResourceCategory)
                                {{ ucfirst(str_replace('_', ' ', $category->value)) }}
                            @else
                                {{ ucfirst(str_replace('_', ' ', (string) $category)) }}
                            @endif
                        </div>

                        <div class="resource-list">
                            @foreach ($items as $resource)
                                @php
                                    $downloadUrl = $resource->external_url
                                        ?: ($resource->file_path ? asset('storage/' . ltrim($resource->file_path, '/')) : null);
                                @endphp
                                <x-card class="resource-row">
                                    <div class="resource-row-icon" aria-hidden="true">
                                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                    </div>
                                    <div class="resource-row-copy">
                                        <h3 class="resource-row-title">{{ $resource->title }}</h3>
                                        @if ($resource->description)
                                            <p class="resource-row-desc">{{ $resource->description }}</p>
                                        @endif
                                    </div>
                                    @if ($downloadUrl)
                                        <x-button href="{{ $downloadUrl }}" variant="primary" class="resource-row-btn !min-h-11 !text-sm" target="_blank" rel="noopener noreferrer">
                                            Download
                                        </x-button>
                                    @endif
                                </x-card>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="feed-empty">Resources will be available here soon.</p>
                @endforelse
            </div>
        </section>
    </x-page-shell>
@endsection
