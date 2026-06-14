@props([
    'chip' => null,
    'class' => '',
])

@if (filled($chip))
    <a
        href="{{ $chip['url'] ?? url('/service-times') }}"
        data-prefetch-link
        @class([
            'next-worship-chip',
            'next-worship-chip--live' => ! empty($chip['is_live']),
            $class,
        ])
    >
        @if (! empty($chip['is_live']))
            <span class="next-worship-chip__dot" aria-hidden="true"></span>
        @else
            <span class="next-worship-chip__cross" aria-hidden="true">✝</span>
        @endif
        <span class="next-worship-chip__copy">
            <span class="next-worship-chip__label">{{ $chip['label'] ?? 'Worship' }}</span>
            @if (! empty($chip['detail']))
                <span class="next-worship-chip__detail">{{ $chip['detail'] }}</span>
            @endif
        </span>
    </a>
@endif
