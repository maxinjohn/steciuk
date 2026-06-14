@props([
    'page' => null,
    'suppressContent' => false,
    'suppressHero' => false,
    'services' => collect(),
    'ministries' => collect(),
    'events' => collect(),
    'news' => collect(),
    'sermons' => collect(),
    'albums' => collect(),
])

@if ($page?->custom_css)
    @push('head')
        <style>{!! safeCustomCss($page->custom_css) !!}</style>
    @endpush
@endif

@if ($page?->custom_css === null && $page?->accent_color)
    @push('head')
        <style>
            :root {
                --page-accent: var(--color-{{ $page->accent_color }});
            }
        </style>
    @endpush
@endif

{{-- Custom JS is disabled on the public site for security. Store in admin only if re-enabled via ALLOW_PAGE_CUSTOM_JS. --}}

@php
    $hasBlockHero = $page?->hasHeroContentBlock() ?? false;
    $showPageHero = ! $suppressHero
        && $page
        && $page->show_hero
        && ! $hasBlockHero
        && ($page->hero_title || $page->hero_subtitle || $page->featured_image);
    $showPageContent = ! $suppressContent
        && $page?->content
        && ! ($page->is_home && ($page->contentBlocks?->isNotEmpty() ?? false));
@endphp

@if ($showPageHero)
    @if ($page && ! $page->is_home)
        <x-breadcrumbs :items="[['label' => $page->title, 'current' => true]]" />
    @endif
    <x-hero
        :title="$page->hero_title ?? $page->title"
        :subtitle="$page->hero_subtitle"
        :image="$page->featured_image"
        :style="$page->hero_style ?? 'gradient'"
        :accent="$page->accent_color ?? 'gold'"
        size="{{ ($page->layout_variant ?? 'standard') === 'immersive' ? 'immersive' : 'small' }}"
        badge="UK Parish"
        :art-slug="$page->slug"
        :art-title="$page->hero_title ?? $page->title"
        :art-context="\App\Support\PageTopicArt::contextForPage($page)"
        :art-content="\App\Support\PageTopicArt::contentHintForPage($page)"
    />
    <x-faith-page-bridge />
@endif

@if ($page && $page->contentBlocks->isNotEmpty())
    <x-content-blocks
        :blocks="$page->contentBlocks"
        :services="$services ?? collect()"
        :ministries="$ministries ?? collect()"
        :events="$events ?? collect()"
        :news="$news ?? collect()"
        :sermons="$sermons ?? collect()"
        :albums="$albums ?? collect()"
        :accent="$page->accent_color ?? 'gold'"
    />
@endif

@if ($page?->is_home)
    <x-faith-whispers variant="home" class="page-section page-section--compact" />
@endif

{{ $slot }}

@if ($showPageContent)
    <section class="page-section page-section--compact page-section--article page-section--cms-body">
        <div @class([
            'page-section-inner mx-auto',
            'max-w-3xl' => ($page->layout_variant ?? 'standard') === 'standard',
            'max-w-5xl' => ($page->layout_variant ?? 'standard') === 'minimal',
            'max-w-7xl' => in_array($page->layout_variant ?? 'standard', ['bento', 'immersive'], true),
        ])>
            <div class="prose-church prose-church--page animate-fade-up">{!! safeHtml($page->content) !!}</div>
        </div>
    </section>
@endif
