@php $tags = $tags ?? collect(); @endphp
@if($tags->isEmpty())
    <span class="sp-tag-empty">No tags</span>
@else
    <div class="sp-tag-list">
        @foreach($tags as $t)
            <span class="sp-tag" @if($t->color) style="background:{{ $t->color }};border-color:{{ $t->color }};color:#fff;" @endif>
                {{ $t->name }}
            </span>
        @endforeach
    </div>
@endif
