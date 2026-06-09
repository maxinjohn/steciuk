@props([
    'variant' => 'default',
])

@php
    $pillars = [
        ['label' => 'Holy Scripture', 'desc' => 'The authoritative Word of God', 'ref' => '2 Tim 3:16'],
        ['label' => 'Jesus Christ', 'desc' => 'Lord and Saviour', 'ref' => 'John 14:6'],
        ['label' => 'Grace & Faith', 'desc' => 'Salvation in Christ alone', 'ref' => 'Eph 2:8–9'],
        ['label' => 'Worship & Communion', 'desc' => 'Evangelical Episcopal liturgy', 'ref' => '1 Cor 11:26'],
        ['label' => 'Prayer & Mission', 'desc' => 'Witness to the Gospel', 'ref' => 'Matt 28:19'],
    ];
@endphp

<section {{ $attributes->merge(['class' => 'faith-pillars faith-pillars--' . $variant]) }} aria-label="What we believe">
    <div class="faith-pillars-inner mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <p class="faith-pillars-kicker">
            <span class="genz-kicker-dot" aria-hidden="true"></span>
            Evangelical faith · Saint Thomas heritage
        </p>
        <div class="faith-pillars-track" role="list">
            @foreach ($pillars as $pillar)
                <article class="faith-pillar" role="listitem">
                    <h3 class="faith-pillar-label">{{ $pillar['label'] }}</h3>
                    <p class="faith-pillar-desc">{{ $pillar['desc'] }}</p>
                    <p class="faith-pillar-ref">{{ $pillar['ref'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
