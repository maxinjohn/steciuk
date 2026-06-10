@props([
    'kicker' => null,
    'note' => null,
    'verses' => [],
])

@php
    $defaultVerses = [
        ['text' => 'Be still, and know that I am God.', 'ref' => 'Psalm 46:10'],
        ['text' => 'Come to me, all you who are weary and burdened, and I will give you rest.', 'ref' => 'Matthew 11:28'],
        ['text' => 'Peace I leave with you; my peace I give you.', 'ref' => 'John 14:27'],
        ['text' => 'The Lord is my shepherd; I shall not want.', 'ref' => 'Psalm 23:1'],
        ['text' => 'Cast all your anxiety on him because he cares for you.', 'ref' => '1 Peter 5:7'],
        ['text' => 'The Lord bless you and keep you; the Lord make his face shine on you.', 'ref' => 'Numbers 6:24–25'],
        ['text' => 'Do not be anxious about anything, but in every situation, by prayer and petition, with thanksgiving, present your requests to God.', 'ref' => 'Philippians 4:6'],
    ];

    $pool = collect($verses)->filter(fn ($item) => ! empty($item['text']))->values();
    if ($pool->isEmpty()) {
        $pool = collect($defaultVerses);
    }

    $verse = $pool[(int) now()->format('w') % $pool->count()];
    $kickerText = $kicker ?: 'Abide in Christ';
    $noteText = $note ?: 'Go in peace — the Lord goes with you. Grace and peace from our parish family.';
@endphp

<aside {{ $attributes->merge(['class' => 'sanctuary-peace']) }} aria-label="Scripture of peace">
    <div class="sanctuary-peace-glow" aria-hidden="true"></div>
    <div class="sanctuary-peace-inner mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <p class="sanctuary-peace-kicker">
            <span class="sanctuary-peace-cross" aria-hidden="true">✝</span>
            {{ $kickerText }}
        </p>
        <blockquote class="sanctuary-peace-quote">
            <p>&ldquo;{{ $verse['text'] }}&rdquo;</p>
            <footer class="sanctuary-peace-ref">{{ $verse['ref'] }}</footer>
        </blockquote>
        <p class="sanctuary-peace-note">{{ $noteText }}</p>
    </div>
</aside>
