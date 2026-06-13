@props([
    'title',
    'subtitle' => null,
    'kicker' => 'Evangelical Oriental Protestant Parish',
    'showStrips' => false,
    'showScripture' => true,
    'scripture' => 'Your word is a lamp to my feet and a light to my path.',
    'scriptureRef' => 'Psalm 119:105',
    'artSlug' => null,
    'artTitle' => null,
    'artContext' => 'page',
    'artContent' => null,
    'artCategory' => null,
    'showTopicArt' => true,
])

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

@if ($showStrips)
    <x-parish-action-strip />
@endif

@if ($showScripture)
    <x-scripture-ribbon :text="$scripture" :reference="$scriptureRef" />
@endif
