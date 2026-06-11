@props([
    'class' => '',
])

<div @class(['site-member-chip', $class]) data-member-chip>
    <button
        type="button"
        class="site-member-chip-btn"
        data-member-chip-trigger
        aria-haspopup="true"
        aria-expanded="false"
        aria-controls="site-member-chip-panel"
    >
        <svg class="site-member-chip-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
        </svg>
        <span class="site-member-chip-label">Members</span>
        <svg class="site-member-chip-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
        </svg>
    </button>

    <div
        id="site-member-chip-panel"
        class="site-member-chip-panel"
        data-member-chip-panel
        role="menu"
        aria-label="Member area"
        hidden
    >
        <p class="site-member-chip-panel-kicker">Member area</p>
        <a href="{{ route('login') }}" class="site-member-chip-link" role="menuitem" data-member-chip-link>
            <span class="site-member-chip-link-label">Sign in</span>
            <span class="site-member-chip-link-desc">Existing parish account</span>
        </a>
        <a href="{{ route('register') }}" class="site-member-chip-link site-member-chip-link--featured" role="menuitem" data-member-chip-link>
            <span class="site-member-chip-link-label">Join the parish</span>
            <span class="site-member-chip-link-desc">Create your member account</span>
        </a>
    </div>
</div>
