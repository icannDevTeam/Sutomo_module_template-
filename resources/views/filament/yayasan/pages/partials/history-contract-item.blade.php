@php
    $dec = $req->decision ?? $req->stage;
    $cls = in_array($dec, ['approved','completed']) ? 'approved' : ($dec === 'rejected' ? 'rejected' : 'cancelled');
    $rev = $req->latestRevision;
@endphp
<div class="hy-item is-{{ $cls }}">
    <div class="hy-dot {{ $cls }}">
        @if($cls === 'approved')
            <svg fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        @elseif($cls === 'rejected')
            <svg fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        @else
            <svg fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        @endif
    </div>
    <div class="hy-content">
        <div class="hy-content__top">
            <div>
                <div class="hy-content__name">{{ $req->teacher?->name ?? '—' }}</div>
                <div class="hy-content__sub">
                    {{ $typeLabels[$req->type] ?? $req->type }}
                    @if($rev?->academic_year) · AY {{ $rev->academic_year }}@endif
                    @if($rev?->current_type && $rev?->proposed_type)
                        · {{ $tierLabels[$rev->current_type] ?? $rev->current_type }} → {{ $tierLabels[$rev->proposed_type] ?? $rev->proposed_type }}
                    @endif
                </div>
            </div>
            <span class="hy-badge {{ $cls }}">{{ $cls === 'approved' || $cls === 'completed' ? 'Approved' : ucfirst($cls) }}</span>
        </div>
        <div class="hy-meta">
            @if($req->decider)<span>by {{ $req->decider->name }}</span>@endif
            @if($req->decided_at)<span>{{ $req->decided_at->format('d M Y') }}</span>@endif
            @if($req->submitter)<span>submitted by {{ $req->submitter->name }}</span>@endif
        </div>
        @if($req->rejection_reason && $cls !== 'approved')
        <div class="hy-reason {{ $cls }}">{{ $req->rejection_reason }}</div>
        @endif
    </div>
</div>
