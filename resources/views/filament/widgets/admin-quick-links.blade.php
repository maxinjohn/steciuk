@php
    $links = \App\Support\AdminQuickLinks::sections();
@endphp

<x-filament-widgets::widget>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($links as $section)
            <details class="admin-quick-card rounded-2xl border border-stone-200/90 bg-white/95 p-4 shadow-md shadow-stone-900/5 sm:p-5 dark:border-white/10 dark:bg-slate-900/70 dark:shadow-black/20" @if ($loop->first) open @endif>
                <summary class="text-xs font-semibold uppercase tracking-wider text-amber-800 dark:text-amber-400">
                    {{ $section['group'] }}
                    <span class="mt-1 block text-[0.68rem] font-normal normal-case tracking-normal text-stone-500 dark:text-slate-400">{{ $section['hint'] }}</span>
                </summary>
                <div class="admin-quick-card__body mt-3 sm:mt-4">
                    <ul class="space-y-2 sm:space-y-3">
                        @foreach ($section['items'] as $item)
                            <li>
                                <a href="{{ $item['url'] }}" class="group block min-h-11 rounded-xl border border-transparent px-3 py-2.5 transition hover:border-amber-200 hover:bg-amber-50 dark:hover:border-amber-500/30 dark:hover:bg-amber-500/10">
                                    <span class="font-medium text-stone-900 group-hover:text-amber-900 dark:text-white dark:group-hover:text-amber-200">{{ $item['label'] }}</span>
                                    <span class="mt-0.5 block text-xs leading-relaxed text-stone-500 dark:text-slate-400">{{ $item['desc'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </details>
        @endforeach
    </div>
</x-filament-widgets::widget>
