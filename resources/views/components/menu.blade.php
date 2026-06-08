@props([
    'items',
    'variant' => 'desktop',
])

@php
    $resolveUrl = function ($item): string {
        if (! empty($item->url) && empty($item->page_id)) {
            return $item->url;
        }

        if ($item->page_id) {
            $slug = $item->relationLoaded('page') ? $item->page?->slug : $item->page()->value('slug');

            if ($slug === 'home') {
                return route('home');
            }

            return $slug ? route('pages.show', $slug) : '#';
        }

        if ($item->url) {
            if ($item->is_external || str_starts_with($item->url, 'http')) {
                return $item->url;
            }

            return url($item->url);
        }

        return '#';
    };

    $resolveTarget = fn ($item): ?string => $item->target ?: ($item->is_external ? '_blank' : null);

    $iconFor = fn (string $label): string => match (true) {
        str_contains(strtolower($label), 'about'), str_contains(strtolower($label), 'welcome'), str_contains(strtolower($label), 'heritage') => 'book',
        str_contains(strtolower($label), 'worship'), str_contains(strtolower($label), 'sermon'), str_contains(strtolower($label), 'service') => 'music',
        str_contains(strtolower($label), 'ministr'), str_contains(strtolower($label), 'youth'), str_contains(strtolower($label), 'school'), str_contains(strtolower($label), 'choir'), str_contains(strtolower($label), 'prayer') => 'people',
        str_contains(strtolower($label), 'event'), str_contains(strtolower($label), 'news') => 'calendar',
        str_contains(strtolower($label), 'resource'), str_contains(strtolower($label), 'gallery'), str_contains(strtolower($label), 'liturgy') => 'folder',
        str_contains(strtolower($label), 'contact'), str_contains(strtolower($label), 'member') => 'mail',
        default => 'link',
    };
@endphp

@if ($variant === 'desktop')
    <div class="desktop-nav-shell">
    <ul class="desktop-nav-list" role="menubar">
        @foreach ($items as $item)
            @php
                $hasChildren = $item->children->isNotEmpty();
                $url = $resolveUrl($item);
                $target = $resolveTarget($item);
                $useMega = $hasChildren && $item->children->count() >= 4;
                $path = trim(parse_url($url, PHP_URL_PATH) ?: '/', '/');
                $isActive = $path === ''
                    ? request()->routeIs('home')
                    : request()->is($path, $path . '/*');
            @endphp
            <li class="relative" role="none" @if ($hasChildren) x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @endif>
                @if ($hasChildren)
                    <button
                        type="button"
                        class="menu-link-desktop {{ $isActive ? 'is-active' : '' }}"
                        :aria-expanded="open"
                        aria-haspopup="true"
                    >
                        {{ $item->label }}
                        <svg class="menu-chevron" :class="open && 'is-open'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </button>
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        @class(['menu-mega' => $useMega, 'menu-dropdown absolute left-0 top-full z-50 mt-1 py-2' => ! $useMega])
                        role="menu"
                        x-cloak
                    >
                        <div @class(['menu-mega-grid' => $useMega])>
                            @foreach ($item->children as $child)
                                <a
                                    href="{{ $resolveUrl($child) }}"
                                    @if ($resolveTarget($child)) target="{{ $resolveTarget($child) }}" @if ($resolveTarget($child) === '_blank') rel="noopener noreferrer" @endif @endif
                                    @class(['menu-mega-link' => $useMega])
                                    role="menuitem"
                                >
                                    {{ $child->label }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a
                        href="{{ $url }}"
                        @if ($target) target="{{ $target }}" @if ($target === '_blank') rel="noopener noreferrer" @endif @endif
                        class="menu-link-desktop {{ $isActive ? 'is-active' : '' }}"
                        role="menuitem"
                    >
                        {{ $item->label }}
                    </a>
                @endif
            </li>
        @endforeach
    </ul>
    </div>
@elseif ($variant === 'mobile')
    <div class="mobile-nav-list" role="menu">
        @foreach ($items as $item)
            @php
                $hasChildren = $item->children->isNotEmpty();
                $url = $resolveUrl($item);
                $target = $resolveTarget($item);
                $icon = $iconFor($item->label);
            @endphp
            <div class="mobile-nav-section" role="none" @if ($hasChildren) x-data="{ expanded: false }" @endif>
                @if ($hasChildren)
                    <button
                        type="button"
                        @click="expanded = !expanded"
                        class="menu-link-mobile w-full justify-between"
                        :aria-expanded="expanded"
                    >
                        <span class="flex items-center gap-3">
                            <span class="menu-link-mobile-icon" aria-hidden="true">
                                @include('components.partials.menu-icon', ['name' => $icon])
                            </span>
                            {{ $item->label }}
                        </span>
                        <svg class="menu-link-mobile-chevron h-4 w-4" :class="expanded && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </button>
                    <div
                        x-show="expanded"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-cloak
                    >
                        @foreach ($item->children as $child)
                            <a
                                href="{{ $resolveUrl($child) }}"
                                @if ($resolveTarget($child)) target="{{ $resolveTarget($child) }}" @if ($resolveTarget($child) === '_blank') rel="noopener noreferrer" @endif @endif
                                @click="$dispatch('close-mobile-menu')"
                                class="menu-link-mobile-sub"
                                role="menuitem"
                            >
                                {{ $child->label }}
                            </a>
                        @endforeach
                    </div>
                @else
                    <a
                        href="{{ $url }}"
                        @if ($target) target="{{ $target }}" @if ($target === '_blank') rel="noopener noreferrer" @endif @endif
                        @click="$dispatch('close-mobile-menu')"
                        class="menu-link-mobile"
                        role="menuitem"
                    >
                        <span class="menu-link-mobile-icon" aria-hidden="true">
                            @include('components.partials.menu-icon', ['name' => $icon])
                        </span>
                        {{ $item->label }}
                    </a>
                @endif
            </div>
        @endforeach
    </div>
@else
    <ul class="space-y-2.5" role="list">
        @foreach ($items as $item)
            <li>
                <a
                    href="{{ $resolveUrl($item) }}"
                    @if ($resolveTarget($item)) target="{{ $resolveTarget($item) }}" @endif
                    class="text-sm font-medium text-[var(--site-footer-muted)] transition hover:text-[var(--site-accent)]"
                >
                    {{ $item->label }}
                </a>
            </li>
        @endforeach
    </ul>
@endif
