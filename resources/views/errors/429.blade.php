@php
    $retryAfter = $retryAfter ?? null;
    $retryMessage = $retryAfter
        ? 'Please wait '.($retryAfter >= 60 ? ceil($retryAfter / 60).' minute(s)' : $retryAfter.' second(s)').' before trying again.'
        : 'Please wait a moment before trying again.';
@endphp

<x-error-page
    code="429"
    title="Too many requests"
    :message="$retryMessage"
    primary-label="Back to home"
    :primary-url="url('/')"
    secondary-label="Try again"
    :secondary-url="url()->current()"
/>
