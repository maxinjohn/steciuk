@php
    use App\Models\Setting;
    use App\Support\AdminPanelConfig;

    $panelName = AdminPanelConfig::name();
    $headline = str_ends_with($panelName, ' Admin')
        ? substr($panelName, 0, -6)
        : $panelName;
    $subtitle = AdminPanelConfig::shortName();
    $useChurchLogo = Setting::get('admin_use_church_logo', '1') !== '0';
    $logo = Setting::get('logo');
    $logoUrl = $logo ? Setting::assetUrl($logo) : null;
@endphp

<div class="flex items-center gap-3">
    @if ($useChurchLogo && $logoUrl)
        <img
            src="{{ $logoUrl }}"
            alt=""
            class="h-9 w-9 shrink-0 rounded-xl border border-amber-900/10 bg-white object-contain p-0.5 shadow-sm dark:border-amber-500/20 dark:bg-slate-900"
        >
    @else
        <div class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 text-sm font-bold text-white shadow-md shadow-amber-500/25">
            <span aria-hidden="true">✝</span>
        </div>
    @endif
    <div class="min-w-0 leading-tight">
        <span class="block truncate text-sm font-bold tracking-tight text-stone-900 dark:text-white">{{ $headline }}</span>
        <span class="block truncate text-[0.65rem] font-medium uppercase tracking-[0.14em] text-amber-800/80 dark:text-amber-300/90">{{ $subtitle }}</span>
    </div>
</div>
