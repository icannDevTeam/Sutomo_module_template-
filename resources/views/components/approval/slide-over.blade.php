{{-- Right-hand slide-over (used for the teacher dossier). Focus-trapped, Esc closes. --}}
@props(['open' => false, 'close' => 'closeSlideOver', 'title', 'subtitle' => null, 'loading' => false])
@php $id = 'ap-slide-' . \Illuminate\Support\Str::slug($title); @endphp
@if($open)
<div class="ap-slide-backdrop" x-data x-on:click.self="$wire.{{ $close }}()" x-on:keydown.escape.window="$wire.{{ $close }}()">
    <aside class="ap-slide" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title" x-trap.inert.noscroll="true" tabindex="-1">
        <div class="ap-slide__head">
            <div style="min-width:0;flex:1">
                <h2 class="ap-slide__title" id="{{ $id }}-title">{{ $title }}</h2>
                @if($subtitle)<div class="ap-slide__sub">{{ $subtitle }}</div>@endif
            </div>
            @isset($actions){{ $actions }}@endisset
            <button type="button" class="ap-sheet__close" aria-label="Close" x-on:click="$wire.{{ $close }}()">
                <x-filament::icon icon="heroicon-m-x-mark" aria-hidden="true" />
            </button>
        </div>
        <div class="ap-slide__body">
            @if($loading)
                <div aria-hidden="true" class="ap-stack">
                    <div class="ap-facts">@for($i=0;$i<4;$i++)<div class="ap-fact"><span class="ap-skel ap-skel-line" style="width:4rem;display:block"></span><span class="ap-skel" style="width:3rem;height:1.4rem;display:block;margin-top:.4rem"></span></div>@endfor</div>
                    <span class="ap-skel" style="display:block;height:7rem"></span>
                    <span class="ap-skel" style="display:block;height:9rem"></span>
                </div>
                {{ $slot }}
            @else
                {{ $slot }}
            @endif
        </div>
    </aside>
</div>
@endif
