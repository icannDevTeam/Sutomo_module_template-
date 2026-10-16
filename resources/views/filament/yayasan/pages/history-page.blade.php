<x-filament-panels::page>
<style>
/* Filters toolbar */
.hy-toolbar { display:flex; flex-wrap:wrap; align-items:center; gap:.5rem; margin-bottom:1.5rem; }
.hy-pill {
    padding:.35rem .85rem; border-radius:9999px; font-size:.8rem; font-weight:600;
    border:1.5px solid #e2e8f0; background:#fff; color:#64748b; cursor:pointer;
    transition:border-color .15s,background .15s,color .15s;
}
.hy-pill.active { background:#4f46e5; border-color:#4f46e5; color:#fff; }
.hy-pill:hover:not(.active) { background:#f8fafc; border-color:#c7d2fe; color:#4338ca; }
.hy-divider { width:1px; height:20px; background:#e2e8f0; }

/* KPI row */
.hy-kpis { display:grid; grid-template-columns:repeat(auto-fit,minmax(130px,1fr)); gap:.75rem; margin-bottom:1.5rem; }
.hy-kpi  { background:#fff; border:1.5px solid #e2e8f0; border-radius:12px; padding:1rem 1.1rem; }
.hy-kpi__val  { font-size:1.6rem; font-weight:800; line-height:1; }
.hy-kpi__lbl  { font-size:.74rem; color:#64748b; margin-top:.3rem; }

/* Domain tabs */
.hy-tabs { display:flex; gap:0; border-bottom:2px solid #e2e8f0; margin-bottom:1.25rem; }
.hy-tab  { padding:.55rem 1rem; font-size:.85rem; font-weight:600; color:#64748b; cursor:pointer; border:none; background:none; border-bottom:2.5px solid transparent; margin-bottom:-2px; transition:color .15s; }
.hy-tab.active { color:#4f46e5; border-bottom-color:#4f46e5; }

/* Timeline */
.hy-timeline { display:flex; flex-direction:column; gap:0; }
.hy-month    { margin-bottom:.25rem; }
.hy-month__hd { font-size:.72rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.06em; padding:.6rem 0 .4rem; border-bottom:1px solid #f1f5f9; margin-bottom:.6rem; }
.hy-items     { display:flex; flex-direction:column; gap:.5rem; }
.hy-item {
    display:flex; gap:.9rem; align-items:flex-start;
    background:#fff; border:1.5px solid #e2e8f0; border-radius:11px;
    padding:.85rem 1rem; transition:box-shadow .15s;
}
.hy-item:hover { box-shadow:0 2px 10px rgba(0,0,0,.07); }
.hy-item.is-approved { border-left:4px solid #059669; }
.hy-item.is-rejected { border-left:4px solid #dc2626; }
.hy-item.is-cancelled { border-left:4px solid #94a3b8; }
.hy-dot { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:.1rem; }
.hy-dot.approved  { background:#d1fae5; }
.hy-dot.rejected  { background:#fee2e2; }
.hy-dot.cancelled { background:#f1f5f9; }
.hy-dot.hiring    { background:#ede9fe; }
.hy-dot svg       { width:16px; height:16px; }
.hy-content { flex:1; min-width:0; }
.hy-content__top  { display:flex; justify-content:space-between; align-items:flex-start; gap:.5rem; flex-wrap:wrap; }
.hy-content__name { font-weight:700; font-size:.93rem; color:#0f172a; }
.hy-content__sub  { font-size:.78rem; color:#64748b; margin-top:.15rem; }
.hy-badge { font-size:.68rem; font-weight:700; padding:.2rem .55rem; border-radius:9999px; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap; }
.hy-badge.approved  { background:#d1fae5; color:#065f46; }
.hy-badge.rejected  { background:#fee2e2; color:#991b1b; }
.hy-badge.cancelled { background:#f1f5f9; color:#475569; }
.hy-badge.hiring    { background:#ede9fe; color:#4338ca; }
.hy-meta { display:flex; flex-wrap:wrap; gap:.5rem 1rem; margin-top:.4rem; font-size:.76rem; color:#64748b; }
.hy-meta span { display:flex; align-items:center; gap:.25rem; }
.hy-reason { margin-top:.4rem; background:#fef2f2; border-radius:6px; padding:.35rem .65rem; font-size:.78rem; color:#991b1b; }
.hy-reason.cancelled { background:#f8fafc; color:#475569; }

/* empty */
.hy-empty { text-align:center; padding:3rem 1rem; color:#94a3b8; }
.hy-empty__icon { font-size:2.5rem; margin-bottom:.75rem; }
.hy-empty__title { font-size:.95rem; font-weight:600; color:#475569; }
.hy-empty__body  { font-size:.83rem; margin-top:.3rem; }
</style>

@php
    $tierLabels = [
        'pkwt_1' => 'PKWT-I',  'pkwt_2' => 'PKWT-II', 'pkwt_3' => 'PKWT-III',
        'guru_sk' => 'Guru SK', 'annual_continuation' => 'Continuation',
    ];
    $typeLabels  = \App\Models\ContractApprovalRequest::TYPES ?? [
        'pkwt_renewal'       => 'PKWT Renewal',
        'annual_continuation'=> 'Annual Continuation',
    ];
    $totalApproved = $contractStats['approved'] + $hiringStats['approved'];
    $totalRejected = $contractStats['rejected'] + $hiringStats['rejected'];
@endphp

{{-- KPIs --}}
<div class="hy-kpis">
    <div class="hy-kpi">
        <div class="hy-kpi__val" style="color:#0f172a;">{{ $contractStats['total'] + $hiringStats['total'] }}</div>
        <div class="hy-kpi__lbl">Total Decisions</div>
    </div>
    <div class="hy-kpi">
        <div class="hy-kpi__val" style="color:#059669;">{{ $totalApproved }}</div>
        <div class="hy-kpi__lbl">Approved</div>
    </div>
    <div class="hy-kpi">
        <div class="hy-kpi__val" style="color:#dc2626;">{{ $totalRejected }}</div>
        <div class="hy-kpi__lbl">Rejected</div>
    </div>
    <div class="hy-kpi">
        <div class="hy-kpi__val" style="color:#6366f1;">{{ $contractStats['total'] }}</div>
        <div class="hy-kpi__lbl">Contract Reviews</div>
    </div>
    <div class="hy-kpi">
        <div class="hy-kpi__val" style="color:#7c3aed;">{{ $hiringStats['total'] }}</div>
        <div class="hy-kpi__lbl">Hiring Decisions</div>
    </div>
</div>

{{-- Domain tabs --}}
<div class="hy-tabs" id="hy-tabbar">
    <button class="hy-tab active" data-pane="hy-all">All</button>
    <button class="hy-tab" data-pane="hy-contracts">Contracts</button>
    <button class="hy-tab" data-pane="hy-hiring">Hiring</button>
</div>

{{-- Contract filters (shown in All + Contracts panes) --}}
<div id="hy-contract-filters" class="hy-toolbar">
    @foreach(['all' => 'All Types', 'pkwt_renewal' => 'PKWT Renewal', 'annual_continuation' => 'Continuation'] as $val => $lbl)
        <button class="hy-pill {{ $typeFilter === $val ? 'active' : '' }}"
            wire:click="$set('typeFilter', '{{ $val }}')">{{ $lbl }}</button>
    @endforeach
    <div class="hy-divider"></div>
    @foreach(['all' => 'All Outcomes', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $val => $lbl)
        <button class="hy-pill {{ $decisionFilter === $val ? 'active' : '' }}"
            wire:click="$set('decisionFilter', '{{ $val }}')">{{ $lbl }}</button>
    @endforeach
</div>

{{-- ALL pane --}}
<div id="hy-all">
    @php
        $allItems = collect();
        foreach($contracts as $req) {
            $allItems->push(['type' => 'contract', 'date' => $req->decided_at ?? $req->updated_at, 'obj' => $req]);
        }
        foreach($hiring as $c) {
            $allItems->push(['type' => 'hiring', 'date' => \Carbon\Carbon::parse($c->meta['yayasan']['date'] ?? $c->updated_at), 'obj' => $c]);
        }
        $allItems = $allItems->sortByDesc('date');
        $grouped  = $allItems->groupBy(fn($i) => $i['date'] ? \Carbon\Carbon::parse($i['date'])->format('F Y') : 'Unknown');
    @endphp
    @if($allItems->isEmpty())
        <div class="hy-empty"><div class="hy-empty__icon">📋</div><div class="hy-empty__title">No decisions recorded yet</div><div class="hy-empty__body">Completed and rejected approvals will appear here.</div></div>
    @else
        <div class="hy-timeline">
            @foreach($grouped as $month => $items)
            <div class="hy-month">
                <div class="hy-month__hd">{{ $month }} <span style="font-weight:400;color:#94a3b8;">· {{ $items->count() }} decision{{ $items->count() > 1 ? 's' : '' }}</span></div>
                <div class="hy-items">
                    @foreach($items as $item)
                        @if($item['type'] === 'contract')
                            @include('filament.yayasan.pages.partials.history-contract-item', ['req' => $item['obj'], 'tierLabels' => $tierLabels, 'typeLabels' => $typeLabels])
                        @else
                            @include('filament.yayasan.pages.partials.history-hiring-item', ['c' => $item['obj']])
                        @endif
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

{{-- CONTRACTS pane --}}
<div id="hy-contracts" style="display:none;">
    @if($contracts->isEmpty())
        <div class="hy-empty"><div class="hy-empty__icon">📝</div><div class="hy-empty__title">No contract decisions</div><div class="hy-empty__body">Approved and rejected contract requests appear here.</div></div>
    @else
        @php $grouped2 = $contracts->groupBy(fn($r) => $r->decided_at ? $r->decided_at->format('F Y') : 'Unknown'); @endphp
        <div class="hy-timeline">
            @foreach($grouped2 as $month => $reqs)
            <div class="hy-month">
                <div class="hy-month__hd">{{ $month }} · {{ $reqs->count() }} decision{{ $reqs->count() > 1 ? 's' : '' }}</div>
                <div class="hy-items">
                    @foreach($reqs as $req)
                        @include('filament.yayasan.pages.partials.history-contract-item', ['req' => $req, 'tierLabels' => $tierLabels, 'typeLabels' => $typeLabels])
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

{{-- HIRING pane --}}
<div id="hy-hiring" style="display:none;">
    @if($hiring->isEmpty())
        <div class="hy-empty"><div class="hy-empty__icon">👥</div><div class="hy-empty__title">No hiring decisions</div><div class="hy-empty__body">Approved and rejected hiring candidates appear here.</div></div>
    @else
        @php $grouped3 = $hiring->groupBy(function($c) {
            $d = $c->meta['yayasan']['date'] ?? null;
            return $d ? \Carbon\Carbon::parse($d)->format('F Y') : 'Unknown';
        }); @endphp
        <div class="hy-timeline">
            @foreach($grouped3 as $month => $candidates)
            <div class="hy-month">
                <div class="hy-month__hd">{{ $month }} · {{ $candidates->count() }}</div>
                <div class="hy-items">
                    @foreach($candidates as $c)
                        @include('filament.yayasan.pages.partials.history-hiring-item', ['c' => $c])
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

<script>
document.querySelectorAll('#hy-tabbar .hy-tab').forEach((tab) => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('#hy-tabbar .hy-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        const paneId = tab.dataset.pane;
        ['hy-all','hy-contracts','hy-hiring'].forEach(id => {
            document.getElementById(id).style.display = id === paneId ? '' : 'none';
        });
        const contractFilters = document.getElementById('hy-contract-filters');
        if (contractFilters) contractFilters.style.display = paneId === 'hy-hiring' ? 'none' : '';
    });
});
</script>
</x-filament-panels::page>
