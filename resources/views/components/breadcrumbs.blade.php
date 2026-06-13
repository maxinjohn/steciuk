@props(['items' => []])

@if (count($items) > 0)
    <nav class="site-breadcrumbs page-section-inner mx-auto max-w-7xl" aria-label="Breadcrumb">
        <ol class="site-breadcrumbs__list">
            <li>
                <a href="{{ route('home') }}" class="breadcrumb-link">Home</a>
            </li>
            @foreach ($items as $item)
                <li class="breadcrumb-sep" aria-hidden="true">/</li>
                <li>
                    @if (! empty($item['url']) && ! ($loop->last && ($item['current'] ?? false)))
                        <a href="{{ $item['url'] }}" class="breadcrumb-link">{{ $item['label'] }}</a>
                    @else
                        <span class="breadcrumb-current" @if ($loop->last) aria-current="page" @endif>{{ $item['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
