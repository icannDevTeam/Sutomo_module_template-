@props(['presenter'])
@php
    $p = $presenter;
    $age = $p->ageLabel();
    $urgency = $p->urgency();
    $due = $p->dueLabel();
@endphp
@if($due)
    <span {{ $attributes->merge(['class' => 'ap-age ap-age--' . $urgency]) }}>
        <x-filament::icon icon="heroicon-m-calendar-days" aria-hidden="true" />
        <span>{{ $due }}</span>
    </span>
@elseif($age)
    <span {{ $attributes->merge(['class' => 'ap-age ap-age--' . $urgency]) }}>
        <x-filament::icon icon="heroicon-m-clock" aria-hidden="true" />
        <span>{{ $age }} waiting</span>
    </span>
@endif
