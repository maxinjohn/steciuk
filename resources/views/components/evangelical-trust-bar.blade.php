@props([
    'variant' => 'default',
])

@php
    $signals = [
        ['label' => 'Holy Scripture', 'ref' => '2 Tim 3:16'],
        ['label' => 'Gospel of Christ', 'ref' => 'John 14:6'],
        ['label' => 'Grace by Faith', 'ref' => 'Eph 2:8–9'],
        ['label' => 'Holy Communion', 'ref' => '1 Cor 11:26'],
        ['label' => 'Prayer & Mission', 'ref' => 'Matt 28:19'],
        ['label' => 'STECI Motto', 'ref' => 'Rev 1:9'],
    ];
@endphp

<div {{ $attributes->merge(['class' => 'evangelical-trust-bar evangelical-trust-bar--' . $variant]) }} aria-label="Evangelical faith markers">
    <div class="evangelical-trust-bar-inner mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="evangelical-trust-bar-track">
            @foreach ($signals as $signal)
                <span class="evangelical-trust-chip">
                    <span class="evangelical-trust-cross" aria-hidden="true">✝</span>
                    <span class="evangelical-trust-label">{{ $signal['label'] }}</span>
                    <span class="evangelical-trust-ref">{{ $signal['ref'] }}</span>
                </span>
            @endforeach
        </div>
    </div>
</div>
