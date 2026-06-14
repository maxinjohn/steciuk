@props([
    'href' => null,
    'padding' => true,
])

@php
    $baseClasses = 'card-modern card-gen-z group flex w-full min-w-0 flex-col active:scale-[0.99]';
    $paddingClass = $padding ? 'p-5 sm:p-6 md:p-8' : '';
    $prefetch = false;

    if (filled($href)) {
        if (! preg_match('#^https?://#i', (string) $href)) {
            $prefetch = true;
        } elseif (str_starts_with((string) $href, url('/'))) {
            $prefetch = true;
        }
    }
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        @if ($prefetch) data-prefetch-link @endif
        {{ $attributes->merge(['class' => $baseClasses . ' ' . $paddingClass]) }}
    >
        {{ $slot }}
    </a>
@else
    <div {{ $attributes->merge(['class' => $baseClasses . ' ' . $paddingClass]) }}>
        {{ $slot }}
    </div>
@endif
