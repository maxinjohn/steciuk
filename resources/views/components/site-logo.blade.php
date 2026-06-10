@props([
    'variant' => 'header',
])

@php
    $logoUrl = asset('images/steci-mark.svg');

    if ($siteLogo ?? null) {
        $logoUrl = str_starts_with($siteLogo, 'http')
            ? $siteLogo
            : (str_starts_with($siteLogo, '/')
                ? asset(ltrim($siteLogo, '/'))
                : asset('storage/' . ltrim($siteLogo, '/')));
    }

    $logoMain = trim(preg_replace('/\s*[–—-]\s*UK Parish\s*$/iu', '', $siteName ?? 'STECI') ?: 'STECI');
@endphp

<span {{ $attributes->merge(['class' => 'site-logo site-logo--' . $variant]) }}>
    <img
        src="{{ $logoUrl }}"
        alt=""
        class="site-logo-mark"
        width="48"
        height="48"
        loading="eager"
        decoding="async"
        fetchpriority="high"
        aria-hidden="true"
    >
    <span class="site-logo-stack">
        <span class="site-logo-line site-logo-line--title">
            <span class="site-logo-full min-[520px]:hidden" aria-hidden="false">
                <span class="site-logo-row">St. Thomas Evangelical</span>
                <span class="site-logo-row">Church of India</span>
            </span>
            <span class="hidden min-[520px]:inline lg:hidden">{{ $logoMain }}</span>
            <span class="hidden lg:inline 2xl:hidden">STECI</span>
            <span class="hidden 2xl:inline">{{ $logoMain }}</span>
        </span>
        <span class="site-logo-sub">UK Parish</span>
    </span>
</span>
