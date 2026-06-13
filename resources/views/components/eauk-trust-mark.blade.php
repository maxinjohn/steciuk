@php
    use App\Support\ReferenceSiteContent;

    $logoSrc = asset('images/eauk/member-logo-medium.png');
@endphp

<a
    {{ $attributes->class(['eauk-trust-mark']) }}
    href="{{ ReferenceSiteContent::EAUK_CHURCH_URL }}"
    target="_blank"
    rel="noopener noreferrer"
    aria-label="Member of the Evangelical Alliance — view our church profile on eauk.org"
>
    <span class="eauk-trust-mark__logo-shell" aria-hidden="true">
        <img
            src="{{ $logoSrc }}"
            width="1012"
            height="236"
            class="eauk-trust-mark__logo"
            alt=""
            loading="lazy"
            decoding="async"
        >
    </span>
    <span class="eauk-trust-mark__label">Member of the Evangelical Alliance</span>
</a>
