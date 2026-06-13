@php
    /** @var array<int, array{title: string, slug: string, url: string}> $pages */
    /** @var string|null $selectedPath */
@endphp

<div class="launch-pages-list">
    @if ($pages === [])
        <p class="launch-pages-list__empty">
            No published pages yet. Publish a page under Content → Pages first.
        </p>
    @else
        <ul class="launch-pages-list__items" role="list">
            @foreach ($pages as $page)
                @php
                    $isSelected = ($selectedPath ?? '') === $page['slug'];
                @endphp
                <li class="launch-pages-list__item @if ($isSelected) is-selected @endif">
                    <div class="launch-pages-list__meta">
                        <span class="launch-pages-list__title">{{ $page['title'] }}</span>
                        <code class="launch-pages-list__path">/{{ $page['slug'] }}</code>
                    </div>
                    <div class="launch-pages-list__actions">
                        <button
                            type="button"
                            class="launch-pages-list__use"
                            wire:click="$set('target_path', @js($page['slug']))"
                        >
                            Use path
                        </button>
                        <a href="{{ $page['url'] }}" class="launch-pages-list__view" target="_blank" rel="noopener">
                            View
                        </a>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
