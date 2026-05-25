@php
    $hours = (int) ($hours ?? 0);
    $target = (int) ($target ?? 40);
    $pct = $target > 0 ? min(100, round(($hours / $target) * 100)) : 0;
    $color = $pct >= 100 ? '#16a34a' : ($pct >= 50 ? '#d97706' : '#dc2626');
@endphp
<div class="sp-cpd">
    <div class="sp-cpd__ring" style="background: conic-gradient({{ $color }} {{ $pct * 3.6 }}deg, #e2e8f0 0);">
        <div class="sp-cpd__center">
            <div class="sp-cpd__count">{{ $hours }}</div>
            <div class="sp-cpd__unit">hrs</div>
        </div>
    </div>
    <div class="sp-cpd__body">
        <div class="sp-cpd__title">CPD Hours {{ $academicYear ?? '' }}</div>
        <div class="sp-cpd__meta">Target: {{ $target }} hrs · {{ $pct }}% complete</div>
        @if($pct < 100)
            <div class="sp-cpd__hint">Needs {{ $target - $hours }} more hours.</div>
        @else
            <div class="sp-cpd__hint sp-cpd__hint--ok">✓ Target met</div>
        @endif
    </div>
</div>
