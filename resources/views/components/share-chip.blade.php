@props([
    'url',
    'title',
    'label' => 'Share',
])

<button
    type="button"
    class="share-chip"
    data-share
    data-share-url="{{ $url }}"
    data-share-title="{{ $title }}"
    aria-label="Share {{ $title }}"
>
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.935-2.186 2.25 2.25 0 00-3.935 2.186z"/>
    </svg>
    <span>{{ $label }}</span>
</button>
