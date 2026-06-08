@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'robots' => null,
    'type' => 'website',
    'canonical' => null,
])

@php
    use App\Support\Seo;

    $metaTitle = $title ?? trim($__env->yieldContent('title')) ?: ($seoDefaultTitle ?? $siteName);
    $metaDescription = Seo::truncateDescription(
        $description
        ?? trim($__env->yieldContent('description'))
        ?: (isset($page) && $page?->seo_description ? $page->seo_description : null)
        ?: ($seoDefaultDescription ?? $siteMotto)
    );
    $robotsContent = $robots
        ?? (isset($page) && $page?->meta_robots ? $page->meta_robots : 'index, follow');
    $canonicalUrl = Seo::canonicalUrl($canonical);
    $ogImageUrl = $image
        ?? (trim($__env->yieldContent('og_image')) ?: null)
        ?: Seo::absoluteAsset(
            (isset($page) && $page?->og_image ? $page->og_image : null)
            ?? ($siteLogo ?? null)
        );
    $ogType = $type;
@endphp

<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
<meta name="robots" content="{{ $robotsContent }}">
<link rel="canonical" href="{{ $canonicalUrl }}">
<link rel="alternate" hreflang="en-gb" href="{{ $canonicalUrl }}">
<link rel="alternate" hreflang="x-default" href="{{ $canonicalUrl }}">
<link rel="sitemap" type="application/xml" title="Sitemap" href="{{ route('sitemap') }}">

<meta property="og:type" content="{{ $ogType }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:locale" content="en_GB">
<meta property="og:site_name" content="{{ $siteName }}">
@if ($ogImageUrl)
    <meta property="og:image" content="{{ $ogImageUrl }}">
    <meta property="og:image:alt" content="{{ $metaTitle }}">
@endif

<meta name="twitter:card" content="{{ $ogImageUrl ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
@if ($ogImageUrl)
    <meta name="twitter:image" content="{{ $ogImageUrl }}">
    <meta name="twitter:image:alt" content="{{ $metaTitle }}">
@endif
