@props([
    'heading' => 'Our Worship Rhythm',
    'subheading' => 'Evangelical Oriental Protestant gathered worship across Britain',
])

@php
    $steps = [
        ['step' => '01', 'title' => 'Gathered Praise', 'text' => 'Hymns and worship centred on the glory of God — in spirit and truth.', 'slug' => 'gathered-praise', 'context' => 'worship'],
        ['step' => '02', 'title' => 'Expository Preaching', 'text' => 'The Word of God proclaimed — for the testimony of Jesus Christ.', 'slug' => 'expository-preaching', 'context' => 'sermon'],
        ['step' => '03', 'title' => 'Holy Communion', 'text' => 'The sacrament of the Lord’s Supper in STECI’s Scripture-centred worship.', 'slug' => 'holy-communion', 'context' => 'communion'],
        ['step' => '04', 'title' => 'Fellowship & Prayer', 'text' => 'Intercession, fellowship, and mission — bearing witness to the Gospel.', 'slug' => 'fellowship-prayer', 'context' => 'fellowship'],
    ];
@endphp

<section {{ $attributes->merge(['class' => 'worship-rhythm']) }} aria-label="Worship rhythm">
    <div class="worship-rhythm-inner mx-auto max-w-7xl">
        <div class="worship-rhythm-header">
            <p class="genz-kicker">
                <span class="genz-kicker-dot" aria-hidden="true"></span>
                Evangelical Oriental Protestant · Monthly
            </p>
            <h2 class="worship-rhythm-title">{{ $heading }}</h2>
            <p class="worship-rhythm-subtitle">{{ $subheading }}</p>
        </div>
        <div class="worship-rhythm-grid">
            @foreach ($steps as $index => $item)
                <article @class([
                    'worship-rhythm-card topic-card wow-card overflow-hidden',
                    'worship-rhythm-card--primary' => $index === 0,
                ])>
                    <div class="worship-rhythm-card-media topic-card-media wow-card-media">
                        <x-card-media
                            :image="null"
                            :slug="$item['slug']"
                            :title="$item['title']"
                            :context="$item['context']"
                            :alt="$item['title']"
                            class="worship-rhythm-card-image"
                        />
                        <div class="topic-card-shade" aria-hidden="true"></div>
                        <div class="topic-card-vignette" aria-hidden="true"></div>
                        <span class="worship-rhythm-step">{{ $item['step'] }}</span>
                    </div>
                    <div class="worship-rhythm-card-body">
                        <h3 class="worship-rhythm-card-title">{{ $item['title'] }}</h3>
                        <p class="worship-rhythm-card-text">{{ $item['text'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
