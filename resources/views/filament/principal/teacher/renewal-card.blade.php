@php
    $end = $teacher->contract_end;
    if (!$end) { $days = null; $color = '#94a3b8'; $msg = 'No contract end date set.'; }
    else {
        $days = (int) now()->diffInDays($end, false);
        if ($days < 0)       { $color = '#dc2626'; $msg = 'Contract expired '.abs($days).' day(s) ago'; }
        elseif ($days <= 30) { $color = '#ea580c'; $msg = "Renews in {$days} day(s) — URGENT"; }
        elseif ($days <= 60) { $color = '#d97706'; $msg = "Renews in {$days} day(s)"; }
        elseif ($days <= 90) { $color = '#ca8a04'; $msg = "Renews in {$days} day(s)"; }
        else                 { $color = '#16a34a'; $msg = "Renews in {$days} day(s) (healthy)"; }
    }
@endphp
<div class="sp-renewal" style="border-left-color:{{ $color }}">
    <div class="sp-renewal__icon" style="background:{{ $color }}">⏳</div>
    <div class="sp-renewal__body">
        <div class="sp-renewal__msg" style="color:{{ $color }}">{{ $msg }}</div>
        @if($end)<div class="sp-renewal__date">Contract end: {{ $end->format('d M Y') }}</div>@endif
    </div>
</div>
