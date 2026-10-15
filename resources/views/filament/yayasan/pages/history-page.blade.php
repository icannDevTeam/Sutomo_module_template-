<x-filament-panels::page>
    {{-- Filters --}}
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1rem;">
        @foreach(['all'=>'All Types','pkwt_renewal'=>'PKWT Renewals','annual_continuation'=>'Continuations'] as $val=>$lbl)
            <button wire:click="$set('typeFilter','{{ $val }}')"
                class="sp-btn sp-btn-sm {{ $typeFilter === $val ? 'sp-btn-primary' : 'sp-btn-ghost' }}">
                {{ $lbl }}
            </button>
        @endforeach
        <div style="width:1px;background:#e2e8f0;margin:0 .25rem;"></div>
        @foreach(['all'=>'All Decisions','approved'=>'Approved','rejected'=>'Rejected'] as $val=>$lbl)
            <button wire:click="$set('decisionFilter','{{ $val }}')"
                class="sp-btn sp-btn-sm {{ $decisionFilter === $val ? 'sp-btn-rose' : 'sp-btn-ghost' }}">
                {{ $lbl }}
            </button>
        @endforeach
    </div>

    @forelse ($requests as $req)
    <div class="sp-card" style="margin-bottom:.75rem;opacity:.9;">
        <div class="sp-card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;">
            <div>
                <span class="sp-font-semibold">{{ $req->teacher?->name ?? '—' }}</span>
                <span class="sp-text-muted" style="margin-left:.5rem;">
                    {{ \App\Models\ContractApprovalRequest::TYPES[$req->type] ?? $req->type }}
                    @if($req->latestRevision?->academic_year)
                        · {{ $req->latestRevision->academic_year }}
                    @endif
                </span>
            </div>
            <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                <span class="sp-pill sp-pill-{{ $req->decision === 'approved' ? 'green' : ($req->decision === 'rejected' ? 'red' : 'gray') }}">
                    {{ ucfirst($req->decision ?? $req->stage) }}
                </span>
                <span class="sp-text-muted" style="font-size:.75rem;">
                    by {{ $req->decider?->name ?? '—' }} · {{ $req->decided_at?->diffForHumans() }}
                </span>
            </div>
        </div>
        @if($req->decision_reason)
        <div style="padding:.5rem 1rem;font-size:.8rem;color:#dc2626;border-top:1px solid #e2e8f0;">
            Reason: {{ $req->decision_reason }}
        </div>
        @endif
        <div style="padding:.5rem 1rem;font-size:.75rem;color:#94a3b8;border-top:1px solid #e2e8f0;">
            Submitted by {{ $req->submitter?->name ?? '—' }} · {{ $req->submitted_at?->format('d M Y') }}
            @if($req->completed_at)
                · Completed {{ $req->completed_at->format('d M Y') }}
            @endif
        </div>
    </div>
    @empty
        <div class="sp-empty-state">No completed or rejected requests.</div>
    @endforelse
</x-filament-panels::page>
