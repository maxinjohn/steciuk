@props([
    'text' => null,
    'reference' => 'Revelation 19:10',
])

@php
    $motto = $text ?? ($siteMotto ?? 'For the Word of God and for the testimony of Jesus Christ');
@endphp

<aside {{ $attributes->merge(['class' => 'scripture-ribbon']) }} aria-label="Parish motto">
    <div class="scripture-ribbon-inner mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <span class="scripture-ribbon-cross" aria-hidden="true">✝</span>
        <blockquote class="scripture-ribbon-text">
            <p>&ldquo;{{ $motto }}&rdquo;</p>
            <footer class="scripture-ribbon-ref">{{ $reference }}</footer>
        </blockquote>
    </div>
</aside>
