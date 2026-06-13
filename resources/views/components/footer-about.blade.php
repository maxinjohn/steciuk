@props([
    'compact' => false,
])

<p @class([
    'text-sm leading-relaxed text-[var(--site-footer-muted)]',
    'pt-3' => $compact,
    'mt-3' => ! $compact,
])>
    {{ $footerAboutTagline }}
</p>

@if ($charityNumber)
    <p class="mt-3 text-xs text-[var(--site-footer-muted)]/80">Registered Charity No. {{ $charityNumber }}</p>
@endif

<x-eauk-trust-mark @class(['mt-4' => $compact, 'mt-5' => ! $compact]) />
