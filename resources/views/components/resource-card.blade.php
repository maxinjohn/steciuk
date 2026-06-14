@props(['resource', 'downloadUrl' => null])

@php
    $categoryLabel = $resource->category instanceof \App\Enums\ResourceCategory
        ? ucfirst(str_replace('_', ' ', $resource->category->value))
        : ucfirst(str_replace('_', ' ', (string) $resource->category));
@endphp

<x-card
    :padding="false"
    class="feed-card topic-card wow-card resource-card overflow-hidden"
>
    <x-feed-card-media
        :image="null"
        :slug="$resource->slug"
        :title="$resource->title"
        context="resource"
        :category="$categoryLabel"
        :alt="$resource->title"
        :sticker="$categoryLabel"
        sticker-class="feed-sticker--violet"
    />
    <div class="feed-card-body">
        <span class="topic-card-kicker">Resource</span>
        <h3 class="feed-card-title">{{ $resource->title }}</h3>
        @if ($resource->description)
            <p class="feed-card-desc line-clamp-3">{{ $resource->description }}</p>
        @endif
        @if ($downloadUrl)
            <x-button
                href="{{ $downloadUrl }}"
                variant="primary"
                class="resource-card-btn !min-h-11 !text-sm"
                target="_blank"
                rel="noopener noreferrer"
            >
                Download
            </x-button>
        @else
            <span class="feed-card-cta feed-card-cta--muted">Available soon</span>
        @endif
    </div>
</x-card>
