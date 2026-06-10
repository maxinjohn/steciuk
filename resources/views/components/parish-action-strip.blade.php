@props([
    'variant' => 'default',
])

@php
    $actions = [
        [
            'label' => 'Holy Communion',
            'desc' => 'Monthly worship · 5 cities',
            'href' => url('/service-times'),
            'icon' => '✝',
            'tone' => 'gold',
        ],
        [
            'label' => 'Expository Preaching',
            'desc' => 'Sermons from Scripture',
            'href' => url('/sermons'),
            'icon' => '📖',
            'tone' => 'navy',
        ],
        [
            'label' => 'Intercessory Prayer',
            'desc' => 'Submit a request',
            'href' => url('/prayer-request'),
            'icon' => '🕊',
            'tone' => 'rose',
        ],
        [
            'label' => 'Our Beliefs',
            'desc' => 'Evangelical Oriental Protestant faith',
            'href' => url('/our-church'),
            'icon' => '⛪',
            'tone' => 'violet',
        ],
        [
            'label' => 'Online Worship',
            'desc' => 'Live stream & archive',
            'href' => url('/online-worship'),
            'icon' => '▶',
            'tone' => 'sky',
        ],
    ];
@endphp

<section {{ $attributes->merge(['class' => 'parish-action-strip parish-action-strip--' . $variant]) }} aria-label="Parish quick actions">
    <div class="parish-action-strip-inner mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <p class="parish-action-strip-kicker">
            <span class="genz-kicker-dot" aria-hidden="true"></span>
            Word · Worship · Witness
        </p>
        <div class="parish-action-strip-track" role="list">
            @foreach ($actions as $action)
                <a
                    href="{{ $action['href'] }}"
                    class="parish-action-card parish-action-card--{{ $action['tone'] }}"
                    role="listitem"
                >
                    <span class="parish-action-icon" aria-hidden="true">{{ $action['icon'] }}</span>
                    <span class="parish-action-copy">
                        <span class="parish-action-label">{{ $action['label'] }}</span>
                        <span class="parish-action-desc">{{ $action['desc'] }}</span>
                    </span>
                    <span class="parish-action-arrow" aria-hidden="true">→</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
