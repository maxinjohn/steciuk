@php
    use App\Support\ReferenceSiteContent;

    $facts = [
        [
            'label' => 'Website',
            'value' => parse_url(url('/'), PHP_URL_HOST) ?: 'steciuk.org',
            'href' => url('/'),
            'icon' => 'link',
        ],
        [
            'label' => 'Phone',
            'value' => $sitePhone ?: '07578 189530',
            'href' => $sitePhone ? 'tel:'.preg_replace('/\s+/', '', $sitePhone) : 'tel:07578189530',
            'icon' => 'phone',
        ],
        [
            'label' => 'Meets in',
            'value' => 'Manchester & four UK cities',
            'href' => route('services.index'),
            'icon' => 'map',
        ],
        [
            'label' => 'Membership no.',
            'value' => ReferenceSiteContent::EAUK_MEMBERSHIP_NUMBER,
            'href' => ReferenceSiteContent::EAUK_CHURCH_URL,
            'icon' => 'id',
        ],
    ];
@endphp

<section {{ $attributes->merge(['class' => 'eauk-member-panel']) }} aria-label="Parish profile and Evangelical Alliance membership">
    <div class="eauk-member-panel__hero">
        <div class="eauk-member-panel__hero-inner mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="eauk-member-panel__hero-grid">
                <div class="eauk-member-panel__intro">
                    <p class="eauk-member-panel__kicker">Evangelical Alliance member church</p>
                    <h2 class="eauk-member-panel__title">{{ $siteName }}</h2>
                    <p class="eauk-member-panel__summary">
                        An evangelical Oriental Protestant parish in the Saint Thomas Christian tradition — gathering monthly for Word, worship, and witness across the United Kingdom, in person and online.
                    </p>
                    <div class="eauk-member-panel__actions">
                        <a href="{{ ReferenceSiteContent::EAUK_CHURCH_URL }}" target="_blank" rel="noopener noreferrer" class="eauk-member-panel__action eauk-member-panel__action--primary">
                            View EAUK church profile
                        </a>
                        <a href="{{ ReferenceSiteContent::EAUK_BRAND_URL }}" target="_blank" rel="noopener noreferrer" class="eauk-member-panel__action">
                            EAUK logo guidelines
                        </a>
                    </div>
                </div>
                <div class="eauk-member-panel__mark-wrap">
                    <a
                        href="{{ ReferenceSiteContent::EAUK_CHURCH_URL }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="eauk-member-panel__mark"
                        aria-label="Member of the Evangelical Alliance"
                    >
                        <img
                            src="{{ asset('images/eauk/member-logo-medium.png') }}"
                            width="625"
                            height="141"
                            alt="Member of the Evangelical Alliance"
                            class="eauk-member-panel__mark-logo"
                            loading="eager"
                            decoding="async"
                        >
                    </a>
                    @if ($charityNumber)
                        <p class="eauk-member-panel__charity">Registered Charity No. {{ $charityNumber }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="eauk-member-panel__facts">
        <div class="eauk-member-panel__facts-inner mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <dl class="eauk-member-panel__facts-grid">
                @foreach ($facts as $fact)
                    <div class="eauk-member-panel__fact">
                        <dt class="eauk-member-panel__fact-label">
                            <span class="eauk-member-panel__fact-icon" aria-hidden="true">
                                @switch($fact['icon'])
                                    @case('phone')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                                        @break
                                    @case('map')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                        @break
                                    @case('id')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm3-7.5h.008v.008H7.5V12zm0 3h.008v.008H7.5V15z"/></svg>
                                        @break
                                    @default
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m9.86-2.54a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                                @endswitch
                            </span>
                            {{ $fact['label'] }}
                        </dt>
                        <dd class="eauk-member-panel__fact-value">
                            <a href="{{ $fact['href'] }}" @if (str_starts_with($fact['href'], 'http')) target="_blank" rel="noopener noreferrer" @endif>
                                {{ $fact['value'] }}
                            </a>
                        </dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </div>
</section>
