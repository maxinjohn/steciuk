@props([
    'variant' => 'default',
    'kicker' => null,
    'actions' => [],
])

@php
    $defaults = \App\Support\PublicUiContent::actionStrip();
    $kicker = filled($kicker) ? $kicker : $defaults['kicker'];
    $actions = collect($actions)->filter(fn ($item) => ! empty($item['label']))->values()->all();

    if ($actions === []) {
        $actions = $defaults['items'];
    }
@endphp

<section {{ $attributes->merge(['class' => 'parish-action-strip parish-action-strip--' . $variant]) }} aria-label="Parish quick actions">
    <div class="parish-action-strip-inner mx-auto max-w-7xl">
        <p class="parish-action-strip-kicker">
            <span class="genz-kicker-dot" aria-hidden="true"></span>
            {{ $kicker }}
        </p>
        <div class="parish-action-strip-track" role="list">
            @foreach ($actions as $action)
                <a
                    href="{{ url($action['href']) }}"
                    data-prefetch-link
                    class="parish-action-card parish-action-card--{{ $action['tone'] ?? 'gold' }}"
                    role="listitem"
                >
                    <span class="parish-action-icon" aria-hidden="true">{{ $action['icon'] ?? '✝' }}</span>
                    <span class="parish-action-copy">
                        <span class="parish-action-label">{{ $action['label'] }}</span>
                        <span class="parish-action-desc">{{ $action['desc'] ?? '' }}</span>
                    </span>
                    <span class="parish-action-arrow" aria-hidden="true">→</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
