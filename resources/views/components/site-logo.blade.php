@props([
    'variant' => 'header',
])

@php
    use App\Support\SiteBrandingAssets;

    $logoPath = $siteLogo ?? null;
    $usesParishLogo = SiteBrandingAssets::usesHeaderLockup($logoPath);
    $useFullParishLogo = $usesParishLogo;
    $logoUrl = SiteBrandingAssets::fullLogoUrl($logoPath);
    $logoMain = trim(preg_replace('/\s*[–—-]\s*UK Parish\s*$/iu', '', $siteName ?? 'STECI') ?: 'STECI');
@endphp

<span @class([
    'site-logo',
    'site-logo--' . $variant,
    'site-logo--parish-full' => $useFullParishLogo,
])>
    <img
        src="{{ $logoUrl }}"
        alt="{{ $usesParishLogo ? ($siteName ?? 'St. Thomas Evangelical Church of India – UK Parish') : '' }}"
        @class([
            'site-logo-mark',
            'site-logo-mark--parish-full' => $useFullParishLogo,
        ])
        width="170"
        height="200"
        loading="eager"
        decoding="async"
        fetchpriority="high"
        @if (! $usesParishLogo) aria-hidden="true" @endif
    >
    @if (! $usesParishLogo || $useFullParishLogo)
        <span class="site-logo-stack">
            <span class="site-logo-line site-logo-line--title">{{ $logoMain }}</span>
            <span class="site-logo-sub">UK Parish</span>
        </span>
    @endif
</span>
