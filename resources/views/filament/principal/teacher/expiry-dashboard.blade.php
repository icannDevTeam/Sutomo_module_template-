@php
    // $buckets: ['expired'=>n,'30'=>n,'60'=>n,'90'=>n]
@endphp
<div class="sp-expiry-grid">
    <div class="sp-expiry-card sp-expiry-card--danger">
        <div class="sp-expiry-card__count">{{ $buckets['expired'] ?? 0 }}</div>
        <div class="sp-expiry-card__label">Expired</div>
    </div>
    <div class="sp-expiry-card sp-expiry-card--warn30">
        <div class="sp-expiry-card__count">{{ $buckets['30'] ?? 0 }}</div>
        <div class="sp-expiry-card__label">≤ 30 days</div>
    </div>
    <div class="sp-expiry-card sp-expiry-card--warn60">
        <div class="sp-expiry-card__count">{{ $buckets['60'] ?? 0 }}</div>
        <div class="sp-expiry-card__label">≤ 60 days</div>
    </div>
    <div class="sp-expiry-card sp-expiry-card--warn90">
        <div class="sp-expiry-card__count">{{ $buckets['90'] ?? 0 }}</div>
        <div class="sp-expiry-card__label">≤ 90 days</div>
    </div>
</div>
