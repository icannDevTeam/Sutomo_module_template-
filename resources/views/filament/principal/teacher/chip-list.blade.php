@php
    /** @var array $items */
    /** @var string $empty */
    /** @var string $class */
@endphp
@if (empty($items))
    <div class="sp-teacher-empty">{{ $empty }}</div>
@else
    <div class="sp-teacher-chips">
        @foreach ($items as $item)
            <span class="sp-teacher-chip {{ $class }}">{{ is_array($item) ? ($item['name'] ?? json_encode($item)) : $item }}</span>
        @endforeach
    </div>
@endif
