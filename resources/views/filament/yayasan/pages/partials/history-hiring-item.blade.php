@php
    $status  = $c->meta['yayasan']['status'] ?? 'approved';
    $decDate = $c->meta['yayasan']['date'] ?? null;
    $decBy   = $c->meta['yayasan']['committee'] ?? $c->meta['yayasan']['by'] ?? null;
    $cls     = $status === 'approved' ? 'approved' : 'rejected';
@endphp
<div class="hy-item is-{{ $cls }}">
    <div class="hy-dot hiring">
        @if($cls === 'approved')
            <svg fill="none" stroke="#6366f1" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        @else
            <svg fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        @endif
    </div>
    <div class="hy-content">
        <div class="hy-content__top">
            <div>
                <div class="hy-content__name">{{ $c->name }}</div>
                <div class="hy-content__sub">
                    Hiring · {{ $c->vacancy?->title ?? '—' }}
                    @if($c->vacancy?->campus) · {{ strtoupper($c->vacancy->campus) }}@endif
                </div>
            </div>
            <span class="hy-badge hiring">{{ ucfirst($status) }}</span>
        </div>
        <div class="hy-meta">
            @if($decBy)<span>by {{ $decBy }}</span>@endif
            @if($decDate)<span>{{ \Carbon\Carbon::parse($decDate)->format('d M Y') }}</span>@endif
            @if($c->score_written)<span>Written: {{ $c->score_written }}</span>@endif
            @if($c->score_interview)<span>Interview: {{ $c->score_interview }}</span>@endif
        </div>
    </div>
</div>
