@props([
    'reference' => null,
])

<aside class="gospel-reminder" aria-label="Parish witness">
    <div class="gospel-reminder-glow" aria-hidden="true"></div>
    <div class="gospel-reminder-inner mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <span class="gospel-reminder-cross" aria-hidden="true">✝</span>
        <div class="gospel-reminder-copy">
            @if ($gospelReminderKicker ?? null)
                <p class="gospel-reminder-kicker">{{ $gospelReminderKicker }}</p>
            @else
                <p class="gospel-reminder-kicker">For the Word of God · and the testimony of Jesus Christ</p>
            @endif
            <p class="gospel-reminder-text">{{ $siteMotto ?? 'For the Word of God and for the testimony of Jesus Christ' }}</p>
        </div>
        <span class="gospel-reminder-ref">{{ $reference ?? ($gospelReminderReference ?? 'Revelation 19:10') }}</span>
    </div>
</aside>
