@props([
    'whispers' => [],
])

@php
    $pool = collect($whispers)->filter(fn ($item) => filled($item['text'] ?? null))->values();

    if ($pool->isEmpty()) {
        $pool = collect(\App\Support\ContextScripture::divineWhispers());
    }

    $start = (int) now()->format('G') % max($pool->count(), 1);
@endphp

@if ($pool->isNotEmpty())
    <aside class="divine-whisper-bar" aria-label="Scripture whispers" data-divine-whisper-bar>
        <div class="divine-whisper-bar__halo" aria-hidden="true"></div>
        <div class="divine-whisper-bar__inner mx-auto max-w-7xl">
            <span class="divine-whisper-bar__icon" aria-hidden="true">🕊</span>
            <div class="divine-whisper-bar__track" aria-live="polite">
                @foreach ($pool as $index => $item)
                    <p
                        @class([
                            'divine-whisper-bar__line',
                            'is-active' => $index === $start,
                        ])
                        data-divine-whisper-line
                    >
                        <span class="divine-whisper-bar__text">&ldquo;{{ $item['text'] }}&rdquo;</span>
                        <span class="divine-whisper-bar__ref">{{ $item['ref'] ?? '' }}</span>
                    </p>
                @endforeach
            </div>
        </div>
    </aside>
@endif
