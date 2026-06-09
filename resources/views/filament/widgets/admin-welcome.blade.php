@php
    use App\Models\Setting;
    use App\Support\AdminPanelConfig;

    $heading = Setting::get('admin_welcome_heading', 'Welcome — manage your parish with peace');
    $body = Setting::get('admin_welcome_body', 'Everything is grouped in the sidebar — tap a section header to expand or collapse it. Edit pages, worship times, photos, messages, and settings from here.');
    $verse = Setting::get('admin_dashboard_verse', 'Be still, and know that I am God.');
    $verseRef = Setting::get('admin_dashboard_verse_ref', 'Psalm 46:10');
@endphp

<x-filament-widgets::widget>
    <div class="admin-sanctuary-banner overflow-hidden rounded-2xl border border-amber-200/80 bg-gradient-to-br from-white via-amber-50/40 to-[#f5f0e8] p-6 shadow-lg shadow-stone-900/5 sm:p-8 dark:border-amber-500/20 dark:from-slate-900 dark:via-indigo-950/60 dark:to-slate-950 dark:shadow-black/30">
        <div class="admin-sanctuary-glow pointer-events-none absolute inset-0 opacity-70" aria-hidden="true"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <p class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-amber-800/90 dark:text-amber-300/90">
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-amber-100 text-[0.65rem] dark:bg-amber-500/20" aria-hidden="true">✝</span>
                    {{ AdminPanelConfig::name() }}
                </p>
                <h2 class="mt-2 text-2xl font-bold tracking-tight text-stone-900 sm:text-3xl dark:text-white">
                    {{ $heading }}
                </h2>
                <p class="mt-2 text-sm leading-relaxed text-stone-600 dark:text-slate-300">
                    {{ $body }}
                </p>
            </div>
            <blockquote class="max-w-sm rounded-xl border border-amber-200/70 bg-white/80 px-4 py-3 text-sm italic text-stone-700 backdrop-blur dark:border-white/10 dark:bg-white/5 dark:text-amber-100/90">
                “{{ $verse }}”
                <footer class="mt-1 text-xs not-italic text-amber-800/75 dark:text-amber-300/70">{{ $verseRef }}</footer>
            </blockquote>
        </div>
    </div>
</x-filament-widgets::widget>
