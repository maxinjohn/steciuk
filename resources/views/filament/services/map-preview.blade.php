@php
    $hasMap = filled($embedUrl ?? null) || filled($mapLink ?? null);
@endphp

<div @class([
    'service-map-preview rounded-2xl border border-amber-200/70 bg-gradient-to-br from-amber-50/80 via-white to-orange-50/60 p-4 dark:border-amber-500/20 dark:from-amber-500/10 dark:via-slate-900 dark:to-indigo-950/40',
    'service-map-preview--empty' => ! $hasMap,
])>
    @if ($hasMap)
        @if ($embedUrl)
            <div class="overflow-hidden rounded-xl border border-white/70 shadow-inner dark:border-white/10">
                <iframe
                    title="Map preview"
                    src="{{ $embedUrl }}"
                    class="service-map-preview__frame h-44 w-full sm:h-52 md:h-56"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
            </div>
        @endif

        <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-800 dark:text-amber-300">Map preview</p>
                @if (filled($address ?? null))
                    <p class="mt-1 text-sm text-stone-600 dark:text-slate-300">{{ $address }}</p>
                @endif
            </div>

            @if (filled($mapLink ?? null))
                <a
                    href="{{ $mapLink }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 rounded-full bg-stone-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-700 dark:bg-amber-500 dark:text-stone-950 dark:hover:bg-amber-400"
                >
                    Open in Google Maps
                </a>
            @endif
        </div>
    @else
        <div class="flex items-start gap-3">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-stone-800 dark:text-white">Map preview appears here</p>
                <p class="mt-1 text-sm text-stone-500 dark:text-slate-400">Find the address with a postcode, then save or tap Generate to build the map link.</p>
            </div>
        </div>
    @endif
</div>
