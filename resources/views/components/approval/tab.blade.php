{{-- Accessible tab button. Parent wraps in <div class="ap-tabs" role="tablist">. --}}
@props(['active' => false, 'count' => null, 'alert' => false, 'target' => null])
<button
    type="button"
    role="tab"
    aria-selected="{{ $active ? 'true' : 'false' }}"
    tabindex="{{ $active ? '0' : '-1' }}"
    {{ $attributes->merge(['class' => 'ap-tab']) }}
    @if($target) wire:loading.attr="disabled" wire:target="{{ $target }}" @endif
    x-on:keydown.arrow-right.prevent="$el.nextElementSibling?.focus(); $el.nextElementSibling?.click()"
    x-on:keydown.arrow-left.prevent="$el.previousElementSibling?.focus(); $el.previousElementSibling?.click()"
>
    <span>{{ $slot }}</span>
    @if($count !== null && $count > 0)
        <span class="ap-tab__count {{ $alert ? 'is-alert' : '' }}">{{ $count }}</span>
    @endif
</button>
