<x-filament-panels::page>
@php
    $user    = auth()->user();
    $counts  = $this->counts;
    $rows    = $this->rows->take($this->limit);
    $queues  = \App\Filament\Yayasan\Pages\ApprovalInbox::QUEUES;
    $sel     = $selectedRequest;
    $p       = $presenter;
    $rev     = $sel?->latestRevision;
    $doc     = $sel?->latestDocument;
    $min     = (int) config('approvals.reason_min_length', 20);
    $hrUsers = $this->hrUsers;
    $tierLabel = fn (?string $t) => $p?->tierLabel($t) ?? '—';
    $sheetReq = $this->sheetFor ? ($sel && $sel->id === $this->sheetFor ? $sel : \App\Models\ContractApprovalRequest::with(['teacher','latestRevision','latestDocument','assignedHr'])->find($this->sheetFor)) : null;
    $sheetRev = $sheetReq?->latestRevision;
@endphp

<div
    x-data
    x-on:keydown.window="
        if (['INPUT','TEXTAREA','SELECT'].includes(document.activeElement?.tagName) || document.querySelector('.ap-sheet-backdrop, .ap-slide-backdrop')) return;
        const k = event.key.toLowerCase();
        if (event.key === 'ArrowDown') { event.preventDefault(); $wire.moveSelection(1) }
        else if (event.key === 'ArrowUp') { event.preventDefault(); $wire.moveSelection(-1) }
        else if (k === 'r') $wire.shortcut('start_review');
        else if (k === 'a') $wire.shortcut('approve');
        else if (k === 'c') $wire.shortcut('request_correction');
        else if (k === 'x') $wire.shortcut('reject');
        else if (k === 'h') $wire.shortcut('assign_hr');
    "
>
    @if(($counts['hiring'] ?? 0) > 0)
        <div class="ap-notice ap-notice--neutral" style="margin-bottom:1rem">
            <x-filament::icon icon="heroicon-m-user-group" aria-hidden="true" />
            <div style="flex:1"><div class="ap-notice__title">{{ $counts['hiring'] }} hiring {{ \Illuminate\Support\Str::plural('candidate', $counts['hiring']) }} awaiting your decision</div>Candidate approvals are handled on their own page.</div>
            <a class="ap-btn ap-btn--secondary ap-btn--sm" href="{{ \App\Filament\Yayasan\Pages\HiringApprovals::getUrl(panel: 'yayasan') }}">Open hiring</a>
        </div>
    @endif

    <div class="ap-toolbar">
        <div class="ap-tabs" role="tablist" aria-label="Queues">
            @foreach($queues as $key => $def)
                <x-approval.tab :active="$queue === $key" :count="$counts[$key] ?? 0" :alert="$key === 'decision'"
                    wire:click="setQueue('{{ $key }}')" target="setQueue" :id="'q-' . $key">{{ $def['label'] }}</x-approval.tab>
            @endforeach
        </div>
        <div class="ap-cluster">
            <div class="ap-segment" role="group" aria-label="Request type">
                @foreach(['all' => 'All', 'pkwt_renewal' => 'PKWT', 'annual_continuation' => 'Continuation'] as $k => $l)
                    <button type="button" class="ap-segment__btn" aria-pressed="{{ $typeFilter === $k ? 'true' : 'false' }}" wire:click="setType('{{ $k }}')" wire:loading.attr="disabled" wire:target="setType">{{ $l }}</button>
                @endforeach
            </div>
            @if($queue !== 'closed')
            <div class="ap-segment" role="group" aria-label="Sort">
                <button type="button" class="ap-segment__btn" aria-pressed="{{ $sort === 'oldest' ? 'true' : 'false' }}" wire:click="setSort('oldest')">Oldest first</button>
                <button type="button" class="ap-segment__btn" aria-pressed="{{ $sort === 'urgent' ? 'true' : 'false' }}" wire:click="setSort('urgent')">Most urgent</button>
            </div>
            @endif
        </div>
    </div>

    <div class="ap-shell {{ $sel ? 'is-detail-open' : '' }}">
        {{-- ── List pane ─────────────────────────────────────────────── --}}
        <div class="ap-shell__list" role="region" aria-label="{{ $queues[$queue]['label'] }}">
            <div class="ap-shortcuts" aria-hidden="true">
                <span><kbd>↑</kbd><kbd>↓</kbd> move</span><span><kbd>R</kbd> review</span><span><kbd>A</kbd> approve</span><span><kbd>C</kbd> correction</span><span><kbd>X</kbd> reject</span><span><kbd>H</kbd> assign</span>
            </div>

            <div wire:loading.delay.shorter wire:target="setQueue, setType, setSort"><x-approval.skeleton-rows :count="5" /></div>
            <div wire:loading.remove wire:target="setQueue, setType, setSort">
                @if($rows->isEmpty())
                    @php
                        [$t, $b] = match ($queue) {
                            'decision' => ['Queue is clear', 'New submissions from principals will appear here, oldest first.'],
                            'review'   => ['Nothing under review', 'Use Start review on a submission to claim it.'],
                            'returned' => ['Nothing awaiting principals', 'Requests you sent back for correction wait here until resubmitted.'],
                            'assign'   => ['No approvals waiting for HR', 'Approved requests stay here until you assign an HR officer.'],
                            'prep'     => ['Nothing in preparation', 'Assigned tasks, uploads and signing progress show here.'],
                            default    => ['No closed requests', 'Completed, rejected and withdrawn requests are kept here.'],
                        };
                    @endphp
                    <x-approval.empty-state :title="$t" :body="$b" icon="heroicon-o-inbox" />
                @else
                    <div class="ap-list">
                        @foreach($rows as $req)
                            <x-approval.request-row :request="$req" :user="$user" :open="'select(' . $req->id . ')'" :selected="$selectedId === $req->id" :show-submitter="true" wire:key="inbox-row-{{ $req->id }}" />
                        @endforeach
                    </div>
                    @if($this->hasMore())
                        <div class="ap-loadmore"><x-approval.btn variant="secondary" size="sm" target="loadMore" wire:click="loadMore" icon="heroicon-m-arrow-down">Load more</x-approval.btn></div>
                    @endif
                @endif
            </div>
        </div>

        {{-- ── Detail pane ───────────────────────────────────────────── --}}
        <div class="ap-shell__detail">
            <div wire:loading.delay.shorter wire:target="select, moveSelection"><x-approval.skeleton-detail /></div>
            <div wire:loading.remove wire:target="select, moveSelection">
                @if(! $sel)
                    <x-approval.empty-state title="Select a request" body="Choose a request from the list to see its submission, evidence, documents and history. Use the arrow keys to move through the queue." icon="heroicon-o-cursor-arrow-rays" />
                @else
                    <button type="button" class="ap-back ap-back--narrow-only" wire:click="clearSelection" style="margin-bottom:.5rem">
                        <x-filament::icon icon="heroicon-m-arrow-left" aria-hidden="true" /> Back to list
                    </button>

                    <x-approval.request-detail :request="$sel" :user="$user" :can-download="true" wire:key="detail-{{ $sel->id }}-{{ $sel->updated_at?->timestamp }}">
                        <x-slot:actions>
                            @if($sel->teacher)
                                <x-approval.btn variant="ghost" size="sm" icon="heroicon-m-identification" :target="'openDossier(' . $sel->teacher_id . ')'" wire:click="openDossier({{ $sel->teacher_id }})">Teacher dossier</x-approval.btn>
                            @endif
                            @if($sel->submitter)
                                <span class="ap-muted" style="font-size:.76rem">Principal: {{ $sel->submitter->name }}</span>
                            @endif
                        </x-slot:actions>

                        <x-slot:decision>
                            @if($actions->isNotEmpty())
                                @php
                                    $byKey = $actions->keyBy('key');
                                    $isMine = $sel->stage === 'under_review' && $sel->review_started_by === $user->id;
                                @endphp
                                <div class="ap-decision" role="group" aria-label="Decision">
                                    @if($sel->stage === 'under_review' && $sel->reviewer)
                                        <div class="ap-decision__note">
                                            <x-filament::icon icon="heroicon-m-magnifying-glass" aria-hidden="true" />
                                            In review by <strong style="margin:0 .25rem">{{ $isMine ? 'you' : $sel->reviewer->name }}</strong> since {{ $sel->review_started_at?->format('d M, H:i') }}
                                        </div>
                                    @elseif(in_array($sel->stage, ['hr_assigned','hr_in_progress','document_correction']))
                                        <div class="ap-decision__note"><x-filament::icon icon="heroicon-m-information-circle" aria-hidden="true" />HR is preparing the document. You can remind or reassign; the principal reviews the upload.</div>
                                    @elseif($sel->stage === 'approved')
                                        <div class="ap-decision__note"><x-filament::icon icon="heroicon-m-information-circle" aria-hidden="true" />Approved {{ $sel->decided_at?->diffForHumans() }} — preparation stays locked until you assign an HR officer.</div>
                                    @endif
                                    <div class="ap-actionbar" wire:loading.class="is-busy" wire:target="startReview, openSheet, nudgeHr">
                                        @if($byKey->has('start_review'))
                                            <x-approval.btn variant="ghost" icon="heroicon-m-magnifying-glass" :target="'startReview(' . $sel->id . ')'" wire:click="startReview({{ $sel->id }})">Start review</x-approval.btn>
                                        @endif
                                        @if($byKey->has('take_over_review'))
                                            <x-approval.btn variant="ghost" icon="heroicon-m-arrow-path-rounded-square" :target="'openSheet(' . $sel->id . ', \'take_over_review\')'" wire:click="openSheet({{ $sel->id }}, 'take_over_review')">Take over review</x-approval.btn>
                                        @endif
                                        @if($byKey->has('approve'))
                                            <x-approval.btn :variant="$sel->stage === 'under_review' && ! $isMine ? 'secondary' : 'approve-solid'" icon="heroicon-m-check-circle" :target="'openSheet(' . $sel->id . ', \'approve\')'" wire:click="openSheet({{ $sel->id }}, 'approve')">Approve</x-approval.btn>
                                        @endif
                                        @if($byKey->has('request_correction'))
                                            <x-approval.btn variant="secondary" icon="heroicon-m-arrow-uturn-left" :target="'openSheet(' . $sel->id . ', \'request_correction\')'" wire:click="openSheet({{ $sel->id }}, 'request_correction')">Request correction</x-approval.btn>
                                        @endif
                                        @if($byKey->has('assign_hr'))
                                            <x-approval.btn variant="primary" icon="heroicon-m-user-plus" :target="'openSheet(' . $sel->id . ', \'assign_hr\')'" wire:click="openSheet({{ $sel->id }}, 'assign_hr')">Assign HR</x-approval.btn>
                                        @endif
                                        @if($byKey->has('nudge_hr'))
                                            <x-approval.btn variant="ghost" icon="heroicon-m-bell-alert" :target="'nudgeHr(' . $sel->id . ')'" wire:click="nudgeHr({{ $sel->id }})">Send reminder</x-approval.btn>
                                        @endif
                                        @if($byKey->has('reassign_hr'))
                                            <x-approval.btn variant="secondary" icon="heroicon-m-arrows-right-left" :target="'openSheet(' . $sel->id . ', \'reassign_hr\')'" wire:click="openSheet({{ $sel->id }}, 'reassign_hr')">Reassign HR</x-approval.btn>
                                        @endif
                                        @if($byKey->has('reject'))
                                            <span class="ap-actionbar__spacer"></span>
                                            <x-approval.btn variant="danger" icon="heroicon-m-x-circle" :target="'openSheet(' . $sel->id . ', \'reject\')'" wire:click="openSheet({{ $sel->id }}, 'reject')">Reject</x-approval.btn>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </x-slot:decision>

                        @if($sel->teacher)
                            <div class="ap-section">
                                <h3 class="ap-section-h">Teacher <button type="button" class="ap-back" style="font-size:.74rem" wire:click="openDossier({{ $sel->teacher_id }})">Full dossier</button></h3>
                                <div class="ap-terms">
                                    <div><div class="ap-term__k">Status</div><div class="ap-term__v">{{ \App\Models\Teacher::STATUSES[$sel->teacher->status] ?? ucfirst(str_replace('_',' ',(string)$sel->teacher->status)) }}</div></div>
                                    @if($sel->teacher->subject)<div><div class="ap-term__k">Subject</div><div class="ap-term__v">{{ $sel->teacher->subject }}</div></div>@endif
                                    @if($sel->teacher->campus)<div><div class="ap-term__k">Campus</div><div class="ap-term__v">{{ strtoupper($sel->teacher->campus) }}</div></div>@endif
                                    @if($sel->teacher->joined_at)<div><div class="ap-term__k">Joined</div><div class="ap-term__v">{{ \Illuminate\Support\Carbon::parse($sel->teacher->joined_at)->format('M Y') }}</div></div>@endif
                                </div>
                            </div>
                        @endif
                    </x-approval.request-detail>
                @endif
            </div>
        </div>
    </div>

    {{-- Undo toast for optimistic Start review --}}
    @if($undoReviewId)
        <div class="ap-undo" role="status" aria-live="polite" x-data x-init="setTimeout(() => $wire.dismissUndo(), 8000)">
            <span>Review started.</span>
            <button type="button" wire:click="undoStartReview" wire:loading.attr="disabled" wire:target="undoStartReview">Undo</button>
        </div>
    @endif
</div>

{{-- ══ Sheets ═══════════════════════════════════════════════════════════ --}}

{{-- Take over (Tier 1) --}}
<x-approval.confirm-sheet :open="$this->isSheet('take_over_review')" submit="confirmTakeOver" title="Take over this review" :tier="1" tone="primary" icon="heroicon-o-arrow-path-rounded-square" confirm-label="Take over"
    description="{{ $sheetReq?->reviewer?->name ?? 'Another reviewer' }} started this review. Taking over makes you the reviewer; nothing else changes and the history keeps both names."
    :error="$sheetError" :stale="$sheetStale" :success="$sheetSuccess" />

{{-- Approve (Tier 3, optional inline assign) --}}
<x-approval.confirm-sheet :open="$this->isSheet('approve')" submit="confirmApprove" title="Approve this submission" :tier="3" tone="approved" icon="heroicon-o-check-circle"
    confirm-label="Approve" acknowledge="I have reviewed the submission and the teacher's evidence for this request." wide
    :error="$sheetError" :stale="$sheetStale" :success="$sheetSuccess">
    <x-slot:summary>
        <div class="ap-summary__row"><div class="ap-summary__k">Teacher</div><div class="ap-summary__v">{{ $sheetReq?->teacher?->name }}</div></div>
        <div class="ap-summary__row"><div class="ap-summary__k">Request</div><div class="ap-summary__v">{{ \App\Models\ContractApprovalRequest::TYPES[$sheetReq?->type] ?? '' }} · revision {{ $sheetRev?->revision_number }}</div></div>
        @if($sheetRev?->proposed_type)<div class="ap-summary__row"><div class="ap-summary__k">Tier</div><div class="ap-summary__v">{{ $tierLabel($sheetRev->current_type) }} to {{ $tierLabel($sheetRev->proposed_type) }}</div></div>@endif
        @if($sheetRev?->proposed_starts_at)<div class="ap-summary__row"><div class="ap-summary__k">Period</div><div class="ap-summary__v">{{ $sheetRev->proposed_starts_at->format('d M Y') }} to {{ $sheetRev->proposed_ends_at?->format('d M Y') ?? 'open-ended' }}</div></div>@endif
        <div class="ap-summary__row"><div class="ap-summary__k">Academic year</div><div class="ap-summary__v">{{ $sheetRev?->academic_year ?? '—' }}</div></div>
        <div class="ap-summary__row"><div class="ap-summary__k">Recommendation</div><div class="ap-summary__v">{{ $sheetRev?->recommendation ? ucfirst(str_replace('_',' ',$sheetRev->recommendation)) : '—' }}</div></div>
    </x-slot:summary>
    <ul class="ap-consequences">
        <li class="is-irreversible"><x-filament::icon icon="heroicon-m-lock-closed" aria-hidden="true" /><span>Approval is recorded under your name against revision {{ $sheetRev?->revision_number }} and cannot be undone.</span></li>
        <li><x-filament::icon icon="heroicon-m-user-plus" aria-hidden="true" /><span>This authorises HR to prepare a contract document. It does not change the teacher's employment yet.</span></li>
        <li><x-filament::icon icon="heroicon-m-bell" aria-hidden="true" /><span>The principal is notified immediately.</span></li>
    </ul>
    <label class="ap-check"><input type="checkbox" wire:model.live="assignAfterApprove"><span><strong>Assign HR now</strong> — skip the separate assignment step.</span></label>
    @if($assignAfterApprove)
        <div class="ap-stack" style="padding:.75rem;border:1px solid var(--ap-border);border-radius:var(--ap-radius-sm);background:var(--ap-surface-2)">
            <div class="ap-field">
                <label class="ap-label" for="ap-hr">HR officer <span class="req">required</span></label>
                <select id="ap-hr" class="ap-select" wire:model="hrUserId">
                    <option value="">Choose…</option>
                    @foreach($hrUsers as $u)<option value="{{ $u->id }}">{{ $u->name }} — {{ $u->open_tasks }} open {{ \Illuminate\Support\Str::plural('task', $u->open_tasks) }}</option>@endforeach
                </select>
            </div>
            <div class="ap-field">
                <label class="ap-label" for="ap-instr">Instructions <span class="req">required</span></label>
                <textarea id="ap-instr" class="ap-textarea" rows="3" wire:model="hrInstructions" placeholder="What to prepare, template to use, anything unusual about this case."></textarea>
            </div>
            <div class="ap-field">
                <label class="ap-label" for="ap-due">Preparation due</label>
                <input id="ap-due" type="date" class="ap-input" wire:model="hrDueAt">
            </div>
        </div>
    @endif
</x-approval.confirm-sheet>

{{-- Reject (Tier 3, typed arm word) --}}
<x-approval.confirm-sheet :open="$this->isSheet('reject')" submit="confirmReject" title="Reject this submission" :tier="3" tone="danger" icon="heroicon-o-x-circle"
    confirm-label="Reject submission" arm-word="REJECT"
    :error="$sheetError" :stale="$sheetStale" :success="$sheetSuccess">
    <x-slot:summary>
        <div class="ap-summary__row"><div class="ap-summary__k">Teacher</div><div class="ap-summary__v">{{ $sheetReq?->teacher?->name }}</div></div>
        <div class="ap-summary__row"><div class="ap-summary__k">Request</div><div class="ap-summary__v">{{ \App\Models\ContractApprovalRequest::TYPES[$sheetReq?->type] ?? '' }} · revision {{ $sheetRev?->revision_number }}</div></div>
        @if($sheetRev?->proposed_type)<div class="ap-summary__row"><div class="ap-summary__k">Proposed</div><div class="ap-summary__v">{{ $tierLabel($sheetRev->current_type) }} to {{ $tierLabel($sheetRev->proposed_type) }}</div></div>@endif
    </x-slot:summary>
    <div class="ap-field">
        <label class="ap-label" for="rj-reason">Reason given to the principal <span class="req">required</span></label>
        <textarea id="rj-reason" class="ap-textarea" rows="4" wire:model="reason" placeholder="Be specific; this is the only explanation the principal receives."></textarea>
        <div class="ap-help">At least {{ $min }} characters.</div>
    </div>
    <ul class="ap-consequences">
        <li class="is-irreversible"><x-filament::icon icon="heroicon-m-lock-closed" aria-hidden="true" /><span>Closes this request permanently. The principal may submit a new one.</span></li>
        <li><x-filament::icon icon="heroicon-m-shield-check" aria-hidden="true" /><span>The teacher's current contract and employment status are <strong>not</strong> changed by this action.</span></li>
    </ul>
    <p class="ap-muted" style="font-size:.78rem;margin:0">Prefer <em>Request correction</em> if the principal can fix the problem.</p>
</x-approval.confirm-sheet>

{{-- Request correction (Tier 2) --}}
<x-approval.confirm-sheet :open="$this->isSheet('request_correction')" submit="confirmCorrection" title="Send back for correction" :tier="2" tone="waiting" icon="heroicon-o-arrow-uturn-left" confirm-label="Send to principal"
    description="The principal edits the submission and resubmits a new revision. The current revision stays on record."
    :error="$sheetError" :stale="$sheetStale" :success="$sheetSuccess">
    <div class="ap-reasons" aria-label="Common reasons">
        @foreach(['Supporting evidence is missing or incomplete.', 'Proposed dates do not match the contract calendar.', 'Proposed tier is not the next allowed step.', 'Recommendation and justification are inconsistent.'] as $r)
            <button type="button" class="ap-reason" wire:click="$set('reason', @js($r))">{{ \Illuminate\Support\Str::limit($r, 38) }}</button>
        @endforeach
    </div>
    <div class="ap-field">
        <label class="ap-label" for="rc-reason">What must be corrected <span class="req">required</span></label>
        <textarea id="rc-reason" class="ap-textarea" rows="4" wire:model="reason"></textarea>
        <div class="ap-help">At least {{ $min }} characters. The principal sees this text verbatim.</div>
    </div>
</x-approval.confirm-sheet>

{{-- Assign HR (Tier 2) --}}
<x-approval.confirm-sheet :open="$this->isSheet('assign_hr')" submit="confirmAssign" title="Assign HR to prepare the contract" :tier="2" tone="primary" icon="heroicon-o-user-plus" confirm-label="Assign and notify"
    :error="$sheetError" :stale="$sheetStale" :success="$sheetSuccess">
    <x-slot:summary>
        <div class="ap-summary__row"><div class="ap-summary__k">Teacher</div><div class="ap-summary__v">{{ $sheetReq?->teacher?->name }}</div></div>
        @if($sheetRev?->proposed_type)<div class="ap-summary__row"><div class="ap-summary__k">Approved terms</div><div class="ap-summary__v">{{ $tierLabel($sheetRev->current_type) }} to {{ $tierLabel($sheetRev->proposed_type) }} · {{ $sheetRev->proposed_starts_at?->format('d M Y') ?? '—' }} to {{ $sheetRev->proposed_ends_at?->format('d M Y') ?? 'open' }}</div></div>@endif
    </x-slot:summary>
    <div class="ap-field">
        <label class="ap-label" for="as-hr">HR officer <span class="req">required</span></label>
        <select id="as-hr" class="ap-select" wire:model="hrUserId">
            <option value="">Choose…</option>
            @foreach($hrUsers as $u)<option value="{{ $u->id }}">{{ $u->name }} — {{ $u->open_tasks }} open {{ \Illuminate\Support\Str::plural('task', $u->open_tasks) }}</option>@endforeach
        </select>
        <div class="ap-help">Open task counts help balance the load.</div>
    </div>
    <div class="ap-field">
        <label class="ap-label" for="as-instr">Instructions <span class="req">required</span></label>
        <textarea id="as-instr" class="ap-textarea" rows="3" wire:model="hrInstructions" placeholder="What to prepare, template to use, anything unusual about this case."></textarea>
    </div>
    <div class="ap-field">
        <label class="ap-label" for="as-due">Preparation due</label>
        <input id="as-due" type="date" class="ap-input" wire:model="hrDueAt">
        <div class="ap-help">Defaults to {{ config('approvals.hr_default_due_days') }} working days from today.</div>
    </div>
    <ul class="ap-consequences">
        <li><x-filament::icon icon="heroicon-m-lock-open" aria-hidden="true" /><span>Unlocks document preparation for the chosen officer only.</span></li>
        <li><x-filament::icon icon="heroicon-m-bell" aria-hidden="true" /><span>HR and the principal are notified; the principal reviews the upload directly.</span></li>
    </ul>
</x-approval.confirm-sheet>

{{-- Reassign HR (Tier 2) --}}
<x-approval.confirm-sheet :open="$this->isSheet('reassign_hr')" submit="confirmReassign" title="Reassign preparation" :tier="2" tone="waiting" icon="heroicon-o-arrows-right-left" confirm-label="Reassign"
    description="{{ $sheetReq?->assignedHr?->name ?? 'The current officer' }} loses access to this task immediately. Any uploaded versions stay on the record."
    :error="$sheetError" :stale="$sheetStale" :success="$sheetSuccess">
    <div class="ap-field">
        <label class="ap-label" for="ra-hr">New HR officer <span class="req">required</span></label>
        <select id="ra-hr" class="ap-select" wire:model="hrUserId">
            <option value="">Choose…</option>
            @foreach($hrUsers as $u)@if($u->id !== $sheetReq?->assigned_hr_id)<option value="{{ $u->id }}">{{ $u->name }} — {{ $u->open_tasks }} open {{ \Illuminate\Support\Str::plural('task', $u->open_tasks) }}</option>@endif@endforeach
        </select>
    </div>
    <div class="ap-field">
        <label class="ap-label" for="ra-reason">Reason <span class="req">required</span></label>
        <textarea id="ra-reason" class="ap-textarea" rows="3" wire:model="reason"></textarea>
        <div class="ap-help">At least {{ $min }} characters. Shared with both officers.</div>
    </div>
</x-approval.confirm-sheet>

{{-- ══ Teacher dossier slide-over ═══════════════════════════════════════ --}}
@php $d = $dossierTeacherId && ! $dossierLoading ? $this->dossier : []; $t = $d['teacher'] ?? null; @endphp
<x-approval.slide-over :open="(bool) $dossierTeacherId" :loading="$dossierLoading"
    :title="$t?->name ?? 'Teacher dossier'"
    :subtitle="$t ? trim(($t->employee_no ?? '') . ($t->subject ? ' · ' . $t->subject : '') . ($t->campus ? ' · ' . strtoupper($t->campus) : ''), ' ·') : null">
    @if($dossierLoading)
        <div wire:init="loadDossier"></div>
    @elseif($t)
        <div class="ap-facts">
            <div class="ap-fact"><div class="ap-fact__k">Status</div><div class="ap-fact__v" style="font-size:.95rem">{{ \App\Models\Teacher::STATUSES[$t->status] ?? ucfirst((string) $t->status) }}</div></div>
            <div class="ap-fact"><div class="ap-fact__k">Supervisions</div><div class="ap-fact__v">{{ $d['supervisions']->count() }}</div><div class="ap-fact__s">{{ $d['supervisions']->where('status','approved')->count() }} approved</div></div>
            <div class="ap-fact"><div class="ap-fact__k">Peer observations</div><div class="ap-fact__v">{{ $d['peers']->count() }}</div><div class="ap-fact__s">{{ $d['peers']->where('status','approved')->count() }} approved</div></div>
            <div class="ap-fact"><div class="ap-fact__k">Joined</div><div class="ap-fact__v" style="font-size:.95rem">{{ $t->joined_at ? \Illuminate\Support\Carbon::parse($t->joined_at)->format('M Y') : '—' }}</div></div>
        </div>
        <p class="ap-muted" style="font-size:.76rem;margin:0">Observation totals include drafts; “approved” counts only reviewed observations and are the figures to rely on.</p>

        <div>
            <h3 class="ap-section-h">Contract history</h3>
            @if($d['contracts']->isEmpty())
                <p class="ap-muted" style="font-size:.84rem">No contracts recorded.</p>
            @else
            <table class="ap-table">
                <thead><tr><th>Type</th><th>Period</th><th>Status</th><th>Yayasan</th></tr></thead>
                <tbody>
                @foreach($d['contracts'] as $c)
                    <tr>
                        <td>{{ \App\Models\TeacherContract::TYPES[$c->type] ?? $c->type }}</td>
                        <td class="ap-mono">{{ $c->starts_at?->format('d M Y') }} – {{ $c->ends_at?->format('d M Y') ?? 'open' }}</td>
                        <td>{{ \App\Models\TeacherContract::STATUSES[$c->status] ?? $c->status }}</td>
                        <td>{{ $c->yayasan_decision ? ucfirst($c->yayasan_decision) . ($c->yayasan_response_at ? ' · ' . $c->yayasan_response_at->format('d M Y') : '') : '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            @endif
        </div>

        <div>
            <h3 class="ap-section-h">Recent supervisions</h3>
            @if($d['supervisions']->isEmpty())
                <p class="ap-muted" style="font-size:.84rem">None recorded.</p>
            @else
            <table class="ap-table">
                <thead><tr><th>Date</th><th>Status</th><th>Score</th><th>Notes</th></tr></thead>
                <tbody>
                @foreach($d['supervisions']->take(6) as $o)
                    <tr>
                        <td class="ap-mono">{{ $o->observed_at?->format('d M Y') }}</td>
                        <td>{{ ucfirst((string) $o->status) }}</td>
                        <td>{{ $o->overall_score ?? $o->score ?? '—' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($o->summary ?? $o->notes ?? '', 80) ?: '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            @endif
        </div>

        <div>
            <h3 class="ap-section-h">Previous approval requests</h3>
            @if($d['history']->isEmpty())
                <p class="ap-muted" style="font-size:.84rem">This is the first request for this teacher.</p>
            @else
                <div class="ap-list">
                    @foreach($d['history'] as $h)
                        <div class="ap-row" style="cursor:default">
                            <div class="ap-row__main">
                                <div class="ap-row__title"><x-approval.type-chip :type="$h->type" /><span class="ap-row__name">{{ $h->submitted_at?->format('d M Y') }}</span></div>
                                <div class="ap-row__meta"><x-approval.stage-badge :presenter="$h" />@if($h->decider)<span>Decided by {{ $h->decider->name }}</span>@endif</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @else
        <p class="ap-muted">Teacher not found.</p>
    @endif
</x-approval.slide-over>
</x-filament-panels::page>
