<x-filament-panels::page>
    {{-- KPIs --}}
    <div class="sp-kpis">
        @php
            $kpis = [
                ['Pending Review',        $pendingReview,      'rose',    'heroicon-o-clock'],
                ['Correction Pending',    $correctionPending,  'amber',   'heroicon-o-exclamation-triangle'],
                ['Awaiting HR Assignment',$pendingHrAssign,    'sky',     'heroicon-o-user-plus'],
                ['HR In Progress',        $hrInProgress,       'indigo',  'heroicon-o-cog-6-tooth'],
                ['Hiring Queue',          $pendingHiring,      'violet',  'heroicon-o-building-library'],
            ];
        @endphp
        @foreach ($kpis as [$label, $value, $tone, $icon])
            <div class="sp-kpi sp-kpi--{{ $tone }}">
                <div class="sp-kpi-head">
                    <span class="sp-kpi-label">{{ $label }}</span>
                    <span class="sp-kpi-icon"><x-filament::icon :icon="$icon" style="width:16px;height:16px;" /></span>
                </div>
                <div class="sp-kpi-value">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    {{-- Breakdown by type --}}
    <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:.5rem;">
        <div class="sp-info-card" style="flex:1;min-width:200px;">
            <div class="sp-info-label">PKWT Renewals Pending</div>
            <div class="sp-info-value" style="font-size:1.5rem;font-weight:700;color:var(--rose-600,#e11d48);">{{ $renewalPending }}</div>
        </div>
        <div class="sp-info-card" style="flex:1;min-width:200px;">
            <div class="sp-info-label">Continuations Pending</div>
            <div class="sp-info-value" style="font-size:1.5rem;font-weight:700;color:var(--sky-600,#0284c7);">{{ $continuationPending }}</div>
        </div>
    </div>

    {{-- Recent decisions --}}
    @if ($recentDecisions->isNotEmpty())
    <div class="sp-section-card" style="margin-top:1.5rem;">
        <div class="sp-section-title">Recent Decisions</div>
        <table class="sp-table" style="width:100%;">
            <thead>
                <tr>
                    <th>Teacher</th><th>Type</th><th>Decision</th><th>By</th><th>When</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recentDecisions as $req)
                <tr>
                    <td>{{ $req->teacher?->name ?? '—' }}</td>
                    <td>{{ \App\Models\ContractApprovalRequest::TYPES[$req->type] ?? $req->type }}</td>
                    <td>
                        <span class="sp-pill sp-pill-{{ $req->decision === 'approved' ? 'green' : 'red' }}">
                            {{ ucfirst($req->decision ?? '—') }}
                        </span>
                    </td>
                    <td>{{ $req->decider?->name ?? '—' }}</td>
                    <td>{{ $req->decided_at?->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</x-filament-panels::page>
