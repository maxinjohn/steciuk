@props([
    'members',
])

@php
    use Illuminate\Support\Facades\Storage;
@endphp

<section class="leadership-grid-section page-section py-10 sm:py-14" aria-label="Parish leadership">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="leadership-grid-header">
            <p class="leadership-grid-kicker">
                <span class="genz-kicker-dot" aria-hidden="true"></span>
                Called to serve Christ and His Church
            </p>
            <h2 class="leadership-grid-title">Those who shepherd our parish</h2>
            <p class="leadership-grid-desc">Spiritual oversight, pastoral care, and faithful governance across our five UK worship locations — under the Word of God and for the testimony of Jesus Christ.</p>
        </div>

        @if ($members->isNotEmpty())
            <div class="leadership-grid" role="list">
                @foreach ($members as $member)
                    <article class="leadership-card" role="listitem">
                        <div class="leadership-card-photo" aria-hidden="true">
                            @if ($member->photo && Storage::disk('public')->exists(ltrim($member->photo, '/')))
                                <img src="{{ asset('storage/' . ltrim($member->photo, '/')) }}" alt="" loading="lazy" decoding="async">
                            @else
                                <div class="leadership-card-photo-fallback">
                                    <span class="leadership-card-cross">✝</span>
                                </div>
                            @endif
                        </div>
                        <div class="leadership-card-body">
                            <h3 class="leadership-card-name">{{ $member->name }}</h3>
                            <p class="leadership-card-role">{{ $member->role }}</p>
                            @if ($member->bio)
                                <p class="leadership-card-bio">{{ $member->bio }}</p>
                            @endif
                            @if ($member->email || $member->phone)
                                <ul class="leadership-card-contact" role="list">
                                    @if ($member->email)
                                        <li><a href="mailto:{{ $member->email }}">{{ $member->email }}</a></li>
                                    @endif
                                    @if ($member->phone)
                                        <li><a href="tel:{{ preg_replace('/\s+/', '', $member->phone) }}">{{ $member->phone }}</a></li>
                                    @endif
                                </ul>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <p class="feed-empty">Leadership profiles will appear here once published.</p>
        @endif
    </div>
</section>
