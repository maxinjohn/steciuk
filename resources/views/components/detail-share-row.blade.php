@props([
    'title',
    'label' => 'Share',
])

<div {{ $attributes->merge(['class' => 'detail-share-row']) }}>
    <x-share-chip
        variant="hero"
        :url="url()->current()"
        :title="$title"
        :label="$label"
    />
</div>
