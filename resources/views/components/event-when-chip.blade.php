@props([
    'at',
])

@php
    use Carbon\Carbon;

    $date = $at instanceof Carbon ? $at : Carbon::parse($at);
    $now = Carbon::now();

    if ($date->isPast()) {
        $label = null;
        $variant = null;
    } elseif ($date->isToday()) {
        $label = 'Today';
        $variant = 'today';
    } elseif ($date->isTomorrow()) {
        $label = 'Tomorrow';
        $variant = 'tomorrow';
    } elseif ($date->lte($now->copy()->addDays(6))) {
        $days = max(1, (int) $now->diffInDays($date));
        $label = 'In '.$days.' '.str('day')->plural($days);
        $variant = 'soon';
    } else {
        $label = $date->format('D j M');
        $variant = 'later';
    }
@endphp

@if (filled($label))
    <span {{ $attributes->class([
        'event-when-chip',
        'event-when-chip--' . $variant => filled($variant),
    ]) }}>
        {{ $label }}
    </span>
@endif
