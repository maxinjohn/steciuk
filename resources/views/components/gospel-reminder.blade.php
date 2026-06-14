@props([
    'reference' => null,
])

<aside {{ $attributes->merge(['class' => 'gospel-reminder']) }} aria-label="Parish witness">
    <div class="gospel-reminder-glow" aria-hidden="true"></div>
    <div class="gospel-reminder-inner mx-auto max-w-7xl">
        <span class="gospel-reminder-cross" aria-hidden="true">✝</span>
        <div class="gospel-reminder-copy">
            @if ($gospelReminderKicker ?? null)
                <p class="gospel-reminder-kicker">{{ $gospelReminderKicker }}</p>
            @endif
            <p class="gospel-reminder-text">{{ $siteMotto ?? 'For the Word of God and for the testimony of Jesus Christ' }}</p>
        </div>
        <span class="gospel-reminder-ref">{{ $reference ?? ($gospelReminderReference ?? 'Revelation 1:9') }}</span>
    </div>
</aside>
