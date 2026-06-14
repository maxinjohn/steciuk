@props([
    'variant' => 'default',
])

@php
    $verses = \App\Support\FaithContent::trustBarVerses();
    $duration = max(120, min(480, count($verses) * 32));
@endphp

@if ($verses !== [])
    <div
        {{ $attributes->merge(['class' => 'evangelical-trust-bar evangelical-trust-bar--' . $variant]) }}
        aria-label="Scripture from our parish library"
    >
        <div class="evangelical-trust-bar-inner mx-auto max-w-7xl">
            <div class="sr-only" role="list">
                @foreach ($verses as $verse)
                    <p role="listitem">{{ $verse['text'] }} — {{ $verse['ref'] }}</p>
                @endforeach
            </div>

            <div class="evangelical-trust-bar-marquee" aria-hidden="true">
                <div
                    class="evangelical-trust-bar-marquee__track"
                    style="--trust-marquee-duration: {{ $duration }}s;"
                >
                    @foreach ([1, 2] as $pass)
                        @foreach ($verses as $verse)
                            <span class="evangelical-trust-verse">
                                <span class="evangelical-trust-cross" aria-hidden="true">✝</span>
                                <span class="evangelical-trust-text">{{ $verse['text'] }}</span>
                                <span class="evangelical-trust-ref">{{ $verse['ref'] }}</span>
                            </span>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif
