@php
    $dockItems = \App\Support\AdminMobileDock::items();
@endphp

<div
    class="admin-dock-wrap lg:hidden"
    x-data
    x-cloak
    x-show="! $store.sidebar.isOpen"
>
    <nav
        class="admin-dock"
        aria-label="Quick admin navigation"
        style="--admin-dock-columns: {{ count($dockItems) }}"
    >
        @foreach ($dockItems as $item)
            @if ($item['type'] === 'menu')
                <button
                    type="button"
                    class="admin-dock-item admin-dock-item--menu"
                    x-on:click="$store.sidebar.open()"
                    aria-label="Open full admin menu"
                >
                    <span class="admin-dock-item__icon" aria-hidden="true">
                        @include('filament.admin.partials.dock-icon', ['icon' => $item['icon']])
                    </span>
                    <span class="admin-dock-label">{{ $item['label'] }}</span>
                </button>
            @else
                <a
                    href="{{ $item['url'] }}"
                    @class([
                        'admin-dock-item',
                        'is-active' => $item['isActive'],
                    ])
                    @if ($item['isActive']) aria-current="page" @endif
                >
                    <span class="admin-dock-item__icon" aria-hidden="true">
                        @include('filament.admin.partials.dock-icon', ['icon' => $item['icon']])
                    </span>
                    <span class="admin-dock-label">{{ $item['label'] }}</span>
                </a>
            @endif
        @endforeach
    </nav>
</div>
