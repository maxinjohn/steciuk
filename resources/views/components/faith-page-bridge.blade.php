@props([
    'trustVariant' => 'compact',
    'showTrustBar' => true,
    'showStrip' => true,
    'stripClass' => 'parish-action-strip--compact !py-3',
])

@if ($showTrustBar)
    <x-evangelical-trust-bar :variant="$trustVariant" />
@endif

@if ($showStrip)
    <x-parish-action-strip @class([$stripClass]) />
@endif
