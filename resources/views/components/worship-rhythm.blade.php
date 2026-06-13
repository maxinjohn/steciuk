@props([
    'heading' => 'Our Worship Rhythm',
    'subheading' => 'Evangelical Oriental Protestant gathered worship across Britain',
])

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
            <article class="worship-rhythm-card worship-rhythm-card--primary">
                <span class="worship-rhythm-step">01</span>
                <h3 class="worship-rhythm-card-title">Gathered Praise</h3>
                <p class="worship-rhythm-card-text">Hymns and worship centred on the glory of God — in spirit and truth.</p>
            </article>
            <article class="worship-rhythm-card">
                <span class="worship-rhythm-step">02</span>
                <h3 class="worship-rhythm-card-title">Expository Preaching</h3>
                <p class="worship-rhythm-card-text">The Word of God proclaimed — for the testimony of Jesus Christ.</p>
            </article>
            <article class="worship-rhythm-card">
                <span class="worship-rhythm-step">03</span>
                <h3 class="worship-rhythm-card-title">Holy Communion</h3>
                <p class="worship-rhythm-card-text">The sacrament of the Lord’s Supper in STECI’s Scripture-centred worship.</p>
            </article>
            <article class="worship-rhythm-card">
                <span class="worship-rhythm-step">04</span>
                <h3 class="worship-rhythm-card-title">Fellowship & Prayer</h3>
                <p class="worship-rhythm-card-text">Intercession, fellowship, and mission — bearing witness to the Gospel.</p>
            </article>
        </div>
    </div>
</section>
