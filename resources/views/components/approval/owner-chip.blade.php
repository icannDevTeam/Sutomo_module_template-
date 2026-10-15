@props(['presenter', 'user' => null])
@php
    $p = $presenter;
    $label = $p->waitingOn();
    $mine = $user ? $p->isWaitingOn($user) : false;
@endphp
@if($label)
<span {{ $attributes->merge(['class' => 'ap-owner' . ($mine ? ' ap-owner--you' : '')]) }}>
    <x-filament::icon icon="heroicon-m-user-circle" aria-hidden="true" />
    <span>Waiting on <strong>{{ $mine ? 'you' : $label }}</strong></span>
</span>
@endif
