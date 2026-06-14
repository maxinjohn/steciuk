@props([
    'chip' => null,
    'class' => '',
])

@if (filled($chip))
    <a
        href="{{ $chip['url'] ?? url('/service-times') }}"
        @class([
            'next-worship-chip',
            'next-worship-chip--live' => ! empty($chip['is_live']),
            $class,
        ])
    >
        <span class="next-worship-chip__dot" aria-hidden="true"></span>
        <span class="next-worship-chip__copy">
            <span class="next-worship-chip__label">{{ $chip['label'] ?? 'Worship' }}</span>
            @if (! empty($chip['detail']))
                <span class="next-worship-chip__detail">{{ $chip['detail'] }}</span>
            @endif
        </span>
    </a>
@endif
