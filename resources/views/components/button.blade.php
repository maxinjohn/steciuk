@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
    'hero' => false,
])

@php
    $classes = match (true) {
        $hero && $variant === 'outline' => 'btn btn-hero-outline',
        $hero => 'btn btn-hero',
        $variant === 'secondary' => 'btn btn-secondary',
        $variant === 'outline' => 'btn btn-outline',
        $variant === 'ghost' => 'btn btn-ghost',
        default => 'btn btn-primary',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
