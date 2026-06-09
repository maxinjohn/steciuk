@props([
    'title',
    'subtitle' => null,
    'kicker' => 'Evangelical Episcopal Parish',
    'showStrips' => true,
    'showScripture' => true,
    'scripture' => 'Your word is a lamp to my feet and a light to my path.',
    'scriptureRef' => 'Psalm 119:105',
])

<x-page-band :title="$title" :subtitle="$subtitle" :kicker="$kicker" />

@if ($showStrips)
    <x-parish-action-strip />
    <x-evangelical-trust-bar />
@endif

@if ($showScripture)
    <x-scripture-ribbon :text="$scripture" :reference="$scriptureRef" />
@endif
