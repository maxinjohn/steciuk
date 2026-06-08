@props([
    'href' => null,
    'padding' => true,
])

@php
    $baseClasses = 'card-modern card-gen-z group flex w-full min-w-0 flex-col active:scale-[0.99]';
    $paddingClass = $padding ? 'p-5 sm:p-6 md:p-8' : '';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClasses . ' ' . $paddingClass]) }}>
        {{ $slot }}
    </a>
@else
    <div {{ $attributes->merge(['class' => $baseClasses . ' ' . $paddingClass]) }}>
        {{ $slot }}
    </div>
@endif
