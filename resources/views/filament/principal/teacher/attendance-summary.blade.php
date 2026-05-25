@php
    // $summary: ['present'=>n,'absent'=>n,'late'=>n,'leave'=>n,'rate'=>%]
    $s = $summary ?? [];
@endphp
<div class="sp-att-summary">
    <div class="sp-att-card sp-att-card--present">
        <div class="sp-att-card__count">{{ $s['present'] ?? 0 }}</div>
        <div class="sp-att-card__label">Present</div>
    </div>
    <div class="sp-att-card sp-att-card--late">
        <div class="sp-att-card__count">{{ $s['late'] ?? 0 }}</div>
        <div class="sp-att-card__label">Late</div>
    </div>
    <div class="sp-att-card sp-att-card--absent">
        <div class="sp-att-card__count">{{ $s['absent'] ?? 0 }}</div>
        <div class="sp-att-card__label">Absent</div>
    </div>
    <div class="sp-att-card sp-att-card--leave">
        <div class="sp-att-card__count">{{ $s['leave'] ?? 0 }}</div>
        <div class="sp-att-card__label">Leave</div>
    </div>
    <div class="sp-att-card sp-att-card--rate">
        <div class="sp-att-card__count">{{ $s['rate'] ?? 0 }}%</div>
        <div class="sp-att-card__label">Present rate</div>
    </div>
</div>
