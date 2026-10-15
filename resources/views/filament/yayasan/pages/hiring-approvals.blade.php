<x-filament-panels::page>
    {{-- KPIs --}}
    <div class="sp-kpis" style="margin-bottom:1.5rem;">
        @php
            $kpis = [
                ['Awaiting Decision', $pending->count(), 'rose',  'heroicon-o-clock'],
                ['Approved (shown)', $approved->count(), 'green', 'heroicon-o-check-circle'],
                ['On Hold',          $held->count(),     'amber', 'heroicon-o-pause-circle'],
                ['Rejected (shown)', $rejected->count(), 'red',   'heroicon-o-x-circle'],
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

    {{-- Pending Queue --}}
    <div class="sp-section-title" style="margin-bottom:.75rem;">Pending Board Review</div>
    @forelse ($pending as $c)
    <div class="sp-card" style="margin-bottom:.75rem;">
        <div class="sp-card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;">
            <div>
                <span class="sp-font-semibold">{{ $c->name }}</span>
                <span class="sp-text-muted" style="margin-left:.5rem;">{{ $c->code }} · {{ $c->vacancy?->title ?? '—' }}</span>
            </div>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                <button wire:click="approve({{ $c->id }})" class="sp-btn sp-btn-sm sp-btn-success">Approve</button>
                <button wire:click="reject({{ $c->id }})" class="sp-btn sp-btn-sm sp-btn-danger">Reject</button>
                <button wire:click="openHoldModal({{ $c->id }})" class="sp-btn sp-btn-sm sp-btn-ghost">On Hold</button>
            </div>
        </div>
        @if(!empty($c->meta['yayasan']['hold_note']))
        <div style="padding:.5rem 1rem;font-size:.8rem;color:#92400e;background:#fef3c7;">
            Hold note: {{ $c->meta['yayasan']['hold_note'] }}
        </div>
        @endif
    </div>
    @empty
        <div class="sp-empty-state">No candidates awaiting Yayasan decision.</div>
    @endforelse

    {{-- On Hold --}}
    @if($held->isNotEmpty())
    <div class="sp-section-title" style="margin:1.5rem 0 .75rem;">On Hold</div>
    @foreach($held as $c)
    <div class="sp-card" style="margin-bottom:.5rem;opacity:.85;">
        <div class="sp-card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;">
            <div>
                <span class="sp-font-semibold">{{ $c->name }}</span>
                <span class="sp-text-muted" style="margin-left:.5rem;">{{ $c->code }}</span>
                @if(!empty($c->meta['yayasan']['hold_note']))
                    <span class="sp-text-muted" style="margin-left:.5rem;">— {{ $c->meta['yayasan']['hold_note'] }}</span>
                @endif
            </div>
            <button wire:click="resumeFromHold({{ $c->id }})" class="sp-btn sp-btn-sm sp-btn-ghost">Resume Review</button>
        </div>
    </div>
    @endforeach
    @endif

    {{-- Recent Decisions --}}
    @if($approved->isNotEmpty() || $rejected->isNotEmpty())
    <div class="sp-section-title" style="margin:1.5rem 0 .75rem;">Recent Decisions (last 30)</div>
    <table class="sp-table" style="width:100%;">
        <thead><tr><th>Candidate</th><th>Position</th><th>Decision</th><th>Date</th></tr></thead>
        <tbody>
            @foreach($approved as $c)
            <tr>
                <td>{{ $c->name }}</td>
                <td>{{ $c->vacancy?->title ?? '—' }}</td>
                <td><span class="sp-pill sp-pill-green">Approved</span></td>
                <td>{{ $c->meta['yayasan']['date'] ?? '—' }}</td>
            </tr>
            @endforeach
            @foreach($rejected as $c)
            <tr>
                <td>{{ $c->name }}</td>
                <td>{{ $c->vacancy?->title ?? '—' }}</td>
                <td><span class="sp-pill sp-pill-red">Rejected</span></td>
                <td>{{ $c->meta['yayasan']['date'] ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Hold Modal --}}
    @if($holdCandidateId)
    <div class="sp-modal-overlay" wire:click.self="$set('holdCandidateId',null)">
        <div class="sp-modal" style="max-width:400px;">
            <h3 class="sp-modal-title">Place Candidate On Hold</h3>
            <textarea wire:model="holdNote" class="sp-textarea" rows="3"
                placeholder="Reason for hold (optional)..."></textarea>
            <div class="sp-modal-actions">
                <button wire:click="$set('holdCandidateId',null)" class="sp-btn sp-btn-ghost">Cancel</button>
                <button wire:click="submitHold" class="sp-btn sp-btn-warning">Confirm Hold</button>
            </div>
        </div>
    </div>
    @endif
</x-filament-panels::page>
