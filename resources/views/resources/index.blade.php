@extends('layouts.app')

@section('title', \App\Support\Seo::documentTitle($page?->seo_title ?? 'Resources', null, $siteName))
@section('description', $page?->seo_description ?? 'Downloads and parish documents')

@section('content')
    <x-page-shell :page="$page" suppress-content suppress-hero>
        <x-breadcrumbs :items="[['label' => 'Resources', 'current' => true]]" />
        <x-page-intro
            :title="$page?->hero_title ?? 'Resources & Downloads'"
            :subtitle="$page?->hero_subtitle ?? 'Liturgy, prayer guides, and parish documents'"
            kicker="Evangelical Oriental Protestant · Resources"
            scripture="Your word is a lamp to my feet and a light to my path."
            scripture-ref="Psalm 119:105"
            art-slug="resources"
            :art-title="$page?->hero_title ?? 'Resources & Downloads'"
            art-context="resource"
            :show-strips="true"
            :show-trust-bar="true"
        />

        <section class="page-section page-section--compact">
            <div class="page-section-inner mx-auto max-w-7xl">
                @forelse ($resources as $category => $items)
                    @php
                        $categoryLabel = $category instanceof \App\Enums\ResourceCategory
                            ? ucfirst(str_replace('_', ' ', $category->value))
                            : ucfirst(str_replace('_', ' ', (string) $category));
                    @endphp
                    <div @class(['resource-group', 'mt-12' => ! $loop->first])>
                        <x-section-heading
                            :title="$categoryLabel"
                            align="left"
                            class="!mb-8"
                        />
                        <div class="feed-grid feed-grid--resources">
                            @foreach ($items as $resource)
                                @php
                                    $downloadUrl = $resource->external_url
                                        ?: ($resource->file_path ? public_upload_url($resource->file_path) : null);
                                @endphp
                                <x-resource-card :resource="$resource" :download-url="$downloadUrl" />
                            @endforeach
                        </div>
                    </div>
                @empty
                    <x-heavenly-empty title="Resources coming soon" context="resources">
                        Liturgy, prayer guides, and parish documents will appear here soon.
                    </x-heavenly-empty>
                @endforelse
            </div>
        </section>
    </x-page-shell>
@endsection
