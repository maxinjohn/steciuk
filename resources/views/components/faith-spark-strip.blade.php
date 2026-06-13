@props([
    'items' => [],
])

@php
    $defaultItems = [
        ['label' => 'Word', 'ref' => 'Rev 1:9', 'href' => url('/our-church')],
        ['label' => 'Worship', 'ref' => 'John 4:24', 'href' => url('/service-times')],
        ['label' => 'Witness', 'ref' => 'Matt 28:19', 'href' => url('/our-church#what-we-believe')],
        ['label' => 'Grace', 'ref' => 'Eph 2:8–9', 'href' => url('/our-church#what-we-believe')],
        ['label' => 'Prayer', 'ref' => 'Phil 4:6', 'href' => url('/prayer-request')],
        ['label' => 'Peace', 'ref' => 'John 14:27', 'href' => url('/our-church')],
        ['label' => 'Scripture', 'ref' => '2 Tim 3:16', 'href' => url('/sermons')],
        ['label' => 'Communion', 'ref' => '1 Cor 11:26', 'href' => url('/service-times')],
    ];

    $sparks = collect($items)->filter(fn ($item) => ! empty($item['label']))->values();
    if ($sparks->isEmpty()) {
        $sparks = collect($defaultItems);
    }
@endphp

<aside {{ $attributes->merge(['class' => 'faith-spark-strip']) }} aria-label="Faith anchors">
    <div class="faith-spark-strip__inner mx-auto max-w-7xl">
        <p class="faith-spark-strip__kicker">
            <span class="genz-kicker-dot" aria-hidden="true"></span>
            Anchored in Christ
        </p>
        <div class="faith-spark-strip__track scroll-x-mobile" role="list">
            @foreach ($sparks as $item)
                <a
                    href="{{ $item['href'] ?? url('/our-church') }}"
                    class="faith-spark-chip"
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
