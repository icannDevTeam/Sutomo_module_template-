@props([
    'variant' => 'secondary',
    'size' => 'md',
    'icon' => null,
    'target' => null,
    'type' => 'button',
    'block' => false,
])
@php
    $classes = 'ap-btn ap-btn--' . $variant . ($size === 'sm' ? ' ap-btn--sm' : '') . ($block ? ' ap-btn--block' : '');
@endphp
<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => $classes]) }}
    @if($target)
        wire:loading.class="is-pending" wire:target="{{ $target }}"
        wire:loading.attr="disabled" wire:target="{{ $target }}"
    @endif
>
    @if($target)
        <span class="ap-btn__spinner" aria-hidden="true"><span class="ap-spinner"></span></span>
    @endif
    @if($icon)
        <span class="ap-btn__icon" aria-hidden="true"><x-filament::icon :icon="$icon" /></span>
    @endif
    <span>{{ $slot }}</span>
</button>
