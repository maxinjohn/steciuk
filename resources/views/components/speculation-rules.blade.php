@if (\App\Support\FutureSiteConfig::speculationEnabled())
    @php
        $prefetch = \App\Support\FutureSiteConfig::speculationPrefetchPaths();
        $prerender = \App\Support\FutureSiteConfig::speculationPrerenderPaths();
        $rules = array_filter([
            'prefetch' => $prefetch !== [] ? [[
                'source' => 'list',
                'urls' => array_map(fn (string $path) => url($path), $prefetch),
            ]] : null,
            'prerender' => $prerender !== [] ? [[
                'source' => 'list',
                'urls' => array_map(fn (string $path) => url($path), $prerender),
                'eagerness' => 'moderate',
            ]] : null,
        ]);
    @endphp
    @if ($rules !== [])
        <script type="speculationrules">
            {!! json_encode($rules, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endif
@endif
