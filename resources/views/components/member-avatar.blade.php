@props([
    'user',
    'size' => 'md',
])

@php
    $sizeClass = match ($size) {
        'sm' => 'member-portal-avatar--sm',
        'lg' => 'member-portal-avatar--lg',
        'xl' => 'member-portal-avatar--xl',
        default => '',
    };
    $avatarUrl = $user->avatarUrl();
@endphp

<div {{ $attributes->class(['member-portal-avatar-wrap', $sizeClass]) }}>
    @if ($avatarUrl)
        <img
            src="{{ $avatarUrl }}"
            alt=""
            class="member-portal-avatar-img"
            width="144"
            height="144"
            loading="lazy"
            decoding="async"
            onerror="this.hidden = true; this.nextElementSibling.hidden = false;"
        >
    @endif
    <span @class(['member-portal-avatar', $sizeClass, 'member-portal-avatar--fallback' => (bool) $avatarUrl]) @if ($avatarUrl) hidden @endif aria-hidden="true">
        {{ $user->initials() }}
    </span>
</div>
