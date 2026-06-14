@props([
    'at',
])

@php
    use Carbon\Carbon;

    $date = $at instanceof Carbon ? $at : Carbon::parse($at);
    $now = Carbon::now();

    if ($date->isPast()) {
        $label = null;
    } elseif ($date->isToday()) {
        $label = 'Today';
    } elseif ($date->isTomorrow()) {
        $label = 'Tomorrow';
    } elseif ($date->lte($now->copy()->addDays(6))) {
        $days = max(1, (int) $now->diffInDays($date));
        $label = 'In '.$days.' '.str('day')->plural($days);
    } else {
        $label = $date->format('D j M');
    }
@endphp

@if (filled($label))
    <span {{ $attributes->merge(['class' => 'event-when-chip']) }}>
        {{ $label }}
    </span>
@endif
