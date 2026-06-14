@if (\App\Support\FutureSiteConfig::speculationEnabled())
    @php
        $rules = \App\Support\FutureSiteConfig::speculationRulesPayload();
    @endphp
    @if ($rules !== [])
        <script type="speculationrules">
            {!! json_encode($rules, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endif
@endif
