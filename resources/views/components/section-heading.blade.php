@props([
    'title',
    'subtitle' => null,
    'align' => 'center',
    'light' => false,
    'kicker' => null,
])

@php
    $alignClasses = match ($align) {
        'left' => 'text-left items-start',
        'right' => 'text-right items-end',
        default => 'text-center items-center',
    };

    $titleClass = $light ? 'text-white' : '';
    $subtitleClass = $light ? 'text-white/85' : 'text-ink-muted';
@endphp

<div {{ $attributes->merge(['class' => 'section-head genz-head mb-10 sm:mb-12 flex flex-col ' . $alignClasses]) }}>
    @if ($kicker)
        <div class="genz-kicker {{ $light ? 'genz-kicker--light' : '' }}">
            <span class="genz-kicker-dot" aria-hidden="true"></span>
            {{ $kicker }}
        </div>
    @endif
    <h2 class="section-title {{ $titleClass }}">
        @if ($light)
            {{ $title }}
        @else
            <span class="text-gradient-gold">{{ $title }}</span>
        @endif
    </h2>
    <span class="genz-divider {{ $align === 'left' ? 'genz-divider--left' : ($align === 'right' ? 'genz-divider--right' : '') }}" aria-hidden="true"></span>
    @if ($subtitle)
        <p class="section-subtitle mt-3 sm:mt-4 {{ $subtitleClass }} {{ $align === 'center' ? 'mx-auto max-w-2xl' : 'max-w-2xl' }}">
            {{ $subtitle }}
        </p>
    @endif
</div>
