@props([
    'kicker' => null,
    'items' => [],
])

@php
    $defaults = \App\Support\PublicUiContent::sparkStrip();
    $kicker = filled($kicker) ? $kicker : $defaults['kicker'];
    $sparks = collect($items)->filter(fn ($item) => ! empty($item['label']))->values();

    if ($sparks->isEmpty()) {
        $sparks = collect($defaults['items']);
    }
@endphp

<aside {{ $attributes->merge(['class' => 'faith-spark-strip']) }} aria-label="Faith anchors">
    <div class="faith-spark-strip__inner mx-auto max-w-7xl">
        <p class="faith-spark-strip__kicker">
            <span class="genz-kicker-dot" aria-hidden="true"></span>
            {{ $kicker }}
        </p>
        <div class="faith-spark-strip__track scroll-x-mobile" role="list">
            @foreach ($sparks as $item)
                <a
                    href="{{ url($item['href'] ?? '/our-church') }}"
                    class="faith-spark-chip"
                    @if (! preg_match('#^https?://#i', (string) ($item['href'] ?? ''))) data-prefetch-link @endif
                    role="listitem"
                >
                    <span class="faith-spark-chip__icon" aria-hidden="true">✝</span>
                    <span class="faith-spark-chip__copy">
                        <span class="faith-spark-chip__label">{{ $item['label'] }}</span>
                        <span class="faith-spark-chip__ref">{{ $item['ref'] ?? '' }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</aside>
