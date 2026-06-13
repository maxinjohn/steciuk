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
            art-slug="resources"
            art-title="Liturgy & Parish Forms"
            art-context="resource"
        />

        <section class="page-section page-section--compact">
            <div class="page-section-inner mx-auto max-w-4xl">
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
                                        ?: ($resource->file_path ? public_upload_url($resource->file_path) : null);
                                @endphp
                                <x-card class="resource-row wow-card">
                                    <div class="resource-row-media wow-card-media">
                                        <x-card-media
                                            :image="null"
                                            :slug="$resource->slug"
                                            :title="$resource->title"
                                            context="resource"
                                            :alt="$resource->title"
                                            class="resource-row-thumb"
                                        />
                                        <div class="topic-card-shade" aria-hidden="true"></div>
                                        <div class="topic-card-vignette" aria-hidden="true"></div>
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
