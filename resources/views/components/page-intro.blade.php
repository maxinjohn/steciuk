@props([
    'title',
    'subtitle' => null,
    'kicker' => null,
    'showStrips' => false,
    'showTrustBar' => false,
    'showScripture' => true,
    'scripture' => null,
    'scriptureRef' => null,
    'artSlug' => null,
    'artTitle' => null,
    'artContext' => 'page',
    'artContent' => null,
    'artCategory' => null,
    'showTopicArt' => true,
])

@php
    $introDefaults = \App\Support\PublicUiContent::pageIntroDefaults();
    $kicker = filled($kicker) ? $kicker : $introDefaults['kicker'];
    $scripture = filled($scripture) ? $scripture : $introDefaults['scripture'];
    $scriptureRef = filled($scriptureRef) ? $scriptureRef : $introDefaults['scripture_ref'];
@endphp

<x-page-band
    :title="$title"
    :subtitle="$subtitle"
    :kicker="$kicker"
    :art-slug="$artSlug"
    :art-title="$artTitle ?? $title"
    :art-context="$artContext"
    :art-content="$artContent"
    :art-category="$artCategory"
    :show-topic-art="$showTopicArt"
/>

@if ($showTrustBar)
    <x-evangelical-trust-bar variant="compact" />
@endif

@if ($showStrips)
    <x-parish-action-strip class="parish-action-strip--compact !py-3" />
@endif

@if ($showScripture)
    <x-scripture-ribbon :text="$scripture" :reference="$scriptureRef" class="scripture-ribbon--page-intro" />
@endif
