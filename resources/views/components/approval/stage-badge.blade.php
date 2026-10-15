@props(['presenter', 'size' => 'md'])
@php
    $p = $presenter instanceof \App\Support\ContractRequestPresenter
        ? $presenter
        : \App\Support\ContractRequestPresenter::for($presenter);
@endphp
<span {{ $attributes->merge(['class' => 'ap-badge ap-tone-' . $p->stageTone() . ($size === 'lg' ? ' ap-badge--lg' : '')]) }}
      role="status">
    <x-filament::icon :icon="$p->stageIcon()" aria-hidden="true" />
    <span>{{ $p->stageLabel() }}</span>
</span>
