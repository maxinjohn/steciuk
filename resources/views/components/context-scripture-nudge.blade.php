@props([
    'scripture' => null,
])

@php
    $verse = $scripture ?? ($contextScripture ?? null);

    if (! is_array($verse) || blank($verse['text'] ?? null)) {
        return;
    }
@endphp

<aside {{ $attributes->merge(['class' => 'context-scripture-nudge']) }} aria-label="Scripture for this page">
    <div class="context-scripture-nudge__glow" aria-hidden="true"></div>
    <div class="context-scripture-nudge__inner mx-auto max-w-7xl">
        <span class="context-scripture-nudge__cross" aria-hidden="true">✝</span>
        <div class="context-scripture-nudge__copy">
            @if (! empty($verse['kicker']))
                <p class="context-scripture-nudge__kicker">{{ $verse['kicker'] }}</p>
            @endif
            <p class="context-scripture-nudge__text">&ldquo;{{ $verse['text'] }}&rdquo;</p>
        </div>
        <span class="context-scripture-nudge__ref">{{ $verse['ref'] ?? '' }}</span>
    </div>
</aside>
