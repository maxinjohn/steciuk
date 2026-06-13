@props([
    'variant' => 'default',
])

<section {{ $attributes->merge(['class' => 'faith-whispers faith-whispers--' . $variant]) }} aria-label="Daily faith reminders">
    <div class="faith-whispers__inner mx-auto max-w-7xl">
        <div class="faith-whispers__head">
            <p class="faith-whispers__kicker">
                <span class="genz-kicker-dot" aria-hidden="true"></span>
                Heaven-minded · Scripture-fed
            </p>
            <h2 class="faith-whispers__title">Faith for the journey</h2>
        </div>
        <div class="faith-whispers__grid" role="list">
            @foreach ([
                ['icon' => '📖', 'title' => 'Live by the Word', 'text' => 'Every doctrine, every prayer, every sermon — shaped by Holy Scripture.', 'ref' => 'Psalm 119:105'],
                ['icon' => '🙏', 'title' => 'Pray without ceasing', 'text' => 'Bring joys and burdens to the Lord; our parish intercedes with you.', 'ref' => '1 Thess 5:17', 'link' => url('/prayer-request'), 'linkLabel' => 'Pray with us'],
                ['icon' => '✝', 'title' => 'Hope in the Cross', 'text' => 'Christ died for our sins, rose again, and reigns — our anchor in every season.', 'ref' => '1 Cor 15:3–4', 'link' => url('/our-church'), 'linkLabel' => 'Our beliefs'],
                ['icon' => '🕊', 'title' => 'Peace that guards', 'text' => 'His peace steadies heart and mind when we draw near in worship and trust.', 'ref' => 'Phil 4:7', 'link' => url('/service-times'), 'linkLabel' => 'Join worship'],
            ] as $item)
                <article class="faith-whisper-card" role="listitem">
                    <span class="faith-whisper-card__icon" aria-hidden="true">{{ $item['icon'] }}</span>
                    <h3 class="faith-whisper-card__title">{{ $item['title'] }}</h3>
                    <p class="faith-whisper-card__text">{{ $item['text'] }}</p>
                    <p class="faith-whisper-card__ref">{{ $item['ref'] }}</p>
                    @if (! empty($item['link']))
                        <a href="{{ $item['link'] }}" class="faith-whisper-card__link">{{ $item['linkLabel'] ?? 'Learn more' }} →</a>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
