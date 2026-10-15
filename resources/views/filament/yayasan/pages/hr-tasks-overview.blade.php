<x-filament-panels::page>
    @if($overdue > 0)
    <div class="sp-alert sp-alert-danger" style="margin-bottom:1rem;">
        <strong>{{ $overdue }}</strong> task{{ $overdue > 1 ? 's are' : ' is' }} overdue.
    </div>
    @endif

    @forelse($tasks as $hrId => $requests)
        @php $hrName = $requests->first()?->assignedHr?->name ?? 'Unknown HR'; @endphp
        <div class="sp-section-card" style="margin-bottom:1.5rem;">
            <div class="sp-section-title">{{ $hrName }} ({{ $requests->count() }} task{{ $requests->count() > 1 ? 's' : '' }})</div>
            @foreach($requests as $req)
            <div class="sp-card" style="margin-top:.5rem;">
                <div class="sp-card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;">
                    <div>
                        <span class="sp-font-semibold">{{ $req->teacher?->name ?? '—' }}</span>
                        <span class="sp-text-muted" style="margin-left:.5rem;">
                            {{ \App\Models\ContractApprovalRequest::TYPES[$req->type] ?? $req->type }}
                        </span>
                    </div>
                    <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                        <span class="sp-pill sp-pill-{{ \App\Models\ContractApprovalRequest::STAGE_COLORS[$req->stage] ?? 'gray' }}">
                            {{ \App\Models\ContractApprovalRequest::STAGES[$req->stage] ?? $req->stage }}
                        </span>
                        @if($req->hr_preparation_due_at)
                            <span style="font-size:.75rem;{{ $req->hr_preparation_due_at->isPast() ? 'color:#dc2626;font-weight:600;' : 'color:#64748b;' }}">
                                Due {{ $req->hr_preparation_due_at->format('d M Y') }}
                            </span>
                        @endif
                        <button wire:click="openReassignModal({{ $req->id }})" class="sp-btn sp-btn-sm sp-btn-ghost">
                            Reassign
                        </button>
                    </div>
                </div>
                @if($req->hr_instructions)
                <div style="padding:.5rem 1rem;font-size:.8rem;color:#475569;border-top:1px solid #e2e8f0;">
                    Instructions: {{ $req->hr_instructions }}
                </div>
                @endif
                @if($req->latestDocument)
                <div style="padding:.5rem 1rem;font-size:.8rem;color:#059669;border-top:1px solid #e2e8f0;">
                    v{{ $req->latestDocument->version_number }}: {{ $req->latestDocument->original_filename }}
                    (uploaded {{ $req->latestDocument->uploaded_at->diffForHumans() }})
                </div>
                @endif
            </div>
            @endforeach
        </div>
    @empty
        <div class="sp-empty-state">No active HR tasks.</div>
    @endforelse

    {{-- Reassign Modal --}}
    @if($reassignRequestId)
    <div class="sp-modal-overlay" wire:click.self="$set('reassignRequestId',null)">
        <div class="sp-modal" style="max-width:420px;">
            <h3 class="sp-modal-title">Reassign HR Task</h3>
            <div style="display:flex;flex-direction:column;gap:.75rem;margin-top:1rem;">
                <div>
                    <label class="sp-label">New HR Staff Member</label>
                    <select wire:model="reassignHrUserId" class="sp-select">
                        <option value="">— Select HR —</option>
                        @foreach($hrUsers as $hr)
                            <option value="{{ $hr->id }}">{{ $hr->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sp-label">Reason (optional)</label>
                    <textarea wire:model="reassignReason" class="sp-textarea" rows="2"></textarea>
                </div>
            </div>
            <div class="sp-modal-actions">
                <button wire:click="$set('reassignRequestId',null)" class="sp-btn sp-btn-ghost">Cancel</button>
                <button wire:click="submitReassign" class="sp-btn sp-btn-primary">Reassign</button>
            </div>
        </div>
    </div>
    @endif
</x-filament-panels::page>
