@php
    $w = $workload;
    $pct = min(100, $w['pct']);
    $duty = $dutyHours ?? 0;
@endphp

<div class="sp-load">
    <div class="sp-load__row">
        <div class="sp-load__label">Teaching periods this week</div>
        <div class="sp-load__value sp-load__value--{{ $w['tone'] }}">
            {{ $w['count'] }} / {{ $w['cap'] }}
            <span class="sp-load__pct">({{ $w['pct'] }}%)</span>
        </div>
    </div>
    <div class="sp-load__bar">
        <div class="sp-load__fill sp-load__fill--{{ $w['tone'] }}" style="width: {{ $pct }}%;"></div>
    </div>
    <div class="sp-load__hint">
        @if($w['tone']==='danger') Heavy workload — consider redistributing.
        @elseif($w['tone']==='warning') Approaching cap — monitor closely.
        @else Comfortable load. @endif
    </div>

    <div class="sp-load__duty">
        <span class="sp-load__duty-label">Duty hours/week (active)</span>
        <span class="sp-load__duty-value">{{ $duty }}</span>
    </div>
</div>
