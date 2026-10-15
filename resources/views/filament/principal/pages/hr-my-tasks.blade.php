<x-filament-panels::page>
@php
    $user   = auth()->user();
    $counts = $this->counts;
    $rows   = $this->rows;
    $tabs   = \App\Filament\Principal\Pages\HrMyTasks::TABS;
    $sel    = $selectedRequest;
    $byKey  = $actions->keyBy('key');
    $sheetReq = $this->sheetFor ? ($sel && $sel->id === $this->sheetFor ? $sel : \App\Models\ContractApprovalRequest::with(['teacher','latestRevision','latestDocument'])->find($this->sheetFor)) : null;
    $sheetRev = $sheetReq?->latestRevision;
    $tierLabel = fn (?string $t) => \App\Support\ContractRequestPresenter::for($sheetReq ?? $sel ?? new \App\Models\ContractApprovalRequest())->tierLabel($t) ?? '—';
@endphp

<div class="ap-intro">
    @if($isSimulation)
        Simulation workspace: HR actions are performed and audited under your Principal account. Preparation stays locked until Yayasan approves and assigns the request.
    @else
        You can see every submission, but you can only prepare a document once Yayasan has approved the request <em>and</em> assigned it to you. Uploads go straight to the principal for review.
    @endif
</div>

<div class="ap-toolbar">
    <div class="ap-tabs" role="tablist" aria-label="HR queues">
        @foreach($tabs as $key => $def)
            <x-approval.tab :active="$tab === $key" :count="$counts[$key] ?? 0" :alert="$key === 'tasks'" wire:click="setTab('{{ $key }}')" target="setTab">{{ $def['label'] }}</x-approval.tab>
        @endforeach
    </div>
</div>

<div class="ap-shell {{ $sel ? 'is-detail-open' : '' }}">
    <div class="ap-shell__list" role="region" aria-label="{{ $tabs[$tab]['label'] }}">
        <div wire:loading.delay.shorter wire:target="setTab"><x-approval.skeleton-rows :count="4" /></div>
        <div wire:loading.remove wire:target="setTab">
            @if($rows->isEmpty())
                @php
                    [$t, $b] = match ($tab) {
                        'tasks'        => ['No preparation tasks', 'When Yayasan assigns a request to you it appears here with instructions and a due date.'],
                        'submitted'    => ['No submitted documents', 'Documents you upload wait here while the principal reviews them.'],
                        'registration' => ['Nothing to register', 'After a teacher signs, confirm the Buku Induk entry here.'],
                        default        => ['Nothing awaiting Yayasan', 'Submissions from principals that have not yet been approved and assigned show here, read-only.'],
                    };
                @endphp
                <x-approval.empty-state :title="$t" :body="$b" icon="heroicon-o-clipboard-document-list" />
            @else
                @if($tab === 'awaiting')
                    <div class="ap-notice ap-notice--neutral" style="margin-bottom:.5rem">
                        <x-filament::icon icon="heroicon-m-lock-closed" aria-hidden="true" />
                        <div>Read-only. Preparation is locked until Yayasan approves and assigns.</div>
                    </div>
                @endif
                <div class="ap-list">
                    @foreach($rows as $req)
                        <x-approval.request-row :request="$req" :user="$user" :open="'select(' . $req->id . ')'" :selected="$selectedId === $req->id" :show-submitter="$tab === 'awaiting'" wire:key="hr-row-{{ $req->id }}" />
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="ap-shell__detail">
        <div wire:loading.delay.shorter wire:target="select"><x-approval.skeleton-detail /></div>
        <div wire:loading.remove wire:target="select">
            @if(! $sel)
                <x-approval.empty-state title="Select a task" body="Open a task to see Yayasan's instructions, the approved terms, and to upload the prepared document." icon="heroicon-o-cursor-arrow-rays" />
            @else
                <button type="button" class="ap-back ap-back--narrow-only" wire:click="clearSelection" style="margin-bottom:.5rem"><x-filament::icon icon="heroicon-m-arrow-left" aria-hidden="true" /> Back to list</button>

                <x-approval.request-detail :request="$sel" :user="$user" :can-download="true" wire:key="hr-detail-{{ $sel->id }}-{{ $sel->updated_at?->timestamp }}">
                    <x-slot:decision>
                        @if($actions->isNotEmpty())
                            <div class="ap-decision" role="group" aria-label="Your actions">
                                @if($sel->hr_instructions)
                                    <div class="ap-notice ap-notice--review">
                                        <x-filament::icon icon="heroicon-m-clipboard-document-list" aria-hidden="true" />
                                        <div><div class="ap-notice__title">Instructions from {{ $sel->hrAssigner?->name ?? 'Yayasan' }}</div>{{ $sel->hr_instructions }}</div>
                                    </div>
                                @endif
                                <div class="ap-actionbar" wire:loading.class="is-busy" wire:target="markInProgress, openSheet">
                                    @if($byKey->has('mark_in_progress'))
                                        <x-approval.btn variant="secondary" icon="heroicon-m-play" :target="'markInProgress(' . $sel->id . ')'" wire:click="markInProgress({{ $sel->id }})">Start preparation</x-approval.btn>
                                    @endif
                                    @if($byKey->has('upload_document'))
                                        <x-approval.btn variant="primary" icon="heroicon-m-document-arrow-up" :target="'openSheet(' . $sel->id . ', \'upload_document\')'" wire:click="openSheet({{ $sel->id }}, 'upload_document')">{{ $sel->stage === 'document_correction' ? 'Upload corrected version' : 'Upload document' }}</x-approval.btn>
                                    @endif
                                    @if($byKey->has('acknowledge_registration'))
                                        <x-approval.btn variant="primary" icon="heroicon-m-clipboard-document-check" :target="'openSheet(' . $sel->id . ', \'acknowledge_registration\')'" wire:click="openSheet({{ $sel->id }}, 'acknowledge_registration')">Confirm registration</x-approval.btn>
                                    @endif
                                </div>
                            </div>
                        @elseif(in_array($sel->stage, ['submitted','under_review','correction_requested','approved']))
                            <div class="ap-decision">
                                <div class="ap-decision__note"><x-filament::icon icon="heroicon-m-lock-closed" aria-hidden="true" />Locked — waiting for Yayasan {{ $sel->stage === 'approved' ? 'to assign an officer' : 'to decide' }}.</div>
                            </div>
                        @endif
                    </x-slot:decision>
                </x-approval.request-detail>
            @endif
        </div>
    </div>
</div>

{{-- Upload (Tier 2) --}}
<x-approval.confirm-sheet :open="$this->isSheet('upload_document')" submit="confirmUpload" title="Upload prepared document" :tier="2" tone="primary" icon="heroicon-o-document-arrow-up" confirm-label="Submit for principal review" wide
    :error="$sheetError" :stale="$sheetStale" :success="$sheetSuccess">
    <x-slot:summary>
        <div class="ap-summary__row"><div class="ap-summary__k">Teacher</div><div class="ap-summary__v">{{ $sheetReq?->teacher?->name }}</div></div>
        @if($sheetRev?->proposed_type)<div class="ap-summary__row"><div class="ap-summary__k">Approved terms</div><div class="ap-summary__v">{{ $tierLabel($sheetRev->current_type) }} to {{ $tierLabel($sheetRev->proposed_type) }} · {{ $sheetRev->proposed_starts_at?->format('d M Y') ?? '—' }} to {{ $sheetRev->proposed_ends_at?->format('d M Y') ?? 'open' }}</div></div>@endif
        <div class="ap-summary__row"><div class="ap-summary__k">Version</div><div class="ap-summary__v">v{{ ($sheetReq?->latestDocument?->version_number ?? 0) + 1 }}@if($sheetReq?->latestDocument) — replaces v{{ $sheetReq->latestDocument->version_number }}@endif</div></div>
    </x-slot:summary>
    @if($sheetReq?->stage === 'document_correction' && $sheetReq?->latestDocument?->correction_note)
        <div class="ap-notice ap-notice--waiting"><x-filament::icon icon="heroicon-m-arrow-uturn-left" aria-hidden="true" /><div><div class="ap-notice__title">Principal asked for</div>{{ $sheetReq->latestDocument->correction_note }}</div></div>
    @endif
    <div class="ap-field" x-data="{ name: null, size: null }">
        <span class="ap-label">PDF file <span class="req">required</span></span>
        <label class="ap-drop" :class="{ 'is-filled': name }">
            <input type="file" accept="application/pdf" wire:model="upload"
                x-on:change="name = $event.target.files[0]?.name; size = $event.target.files[0] ? Math.round($event.target.files[0].size/1024) + ' KB' : null">
            <div x-show="!name"><div style="font-weight:600;font-size:.86rem">Choose a PDF</div><div class="ap-drop__hint">Up to 15 MB. The file is fingerprinted (SHA-256) so acceptance and signing bind to this exact version.</div></div>
            <div x-show="name" x-cloak><div style="font-weight:600;font-size:.86rem" x-text="name"></div><div class="ap-drop__hint" x-text="size"></div></div>
        </label>
        <div class="ap-help" wire:loading wire:target="upload">Uploading file…</div>
        @error('upload')<div class="ap-error">{{ $message }}</div>@enderror
    </div>
    <div class="ap-field">
        <label class="ap-label" for="up-note">Handover note</label>
        <textarea id="up-note" class="ap-textarea" rows="3" wire:model="uploadNote" placeholder="Template used, anything the principal should check first."></textarea>
    </div>
    <ul class="ap-consequences">
        <li><x-filament::icon icon="heroicon-m-eye" aria-hidden="true" /><span>Goes directly to the principal for review; Yayasan is informed but does not re-approve.</span></li>
        <li><x-filament::icon icon="heroicon-m-document-duplicate" aria-hidden="true" /><span>Previous versions remain on the record and are never overwritten.</span></li>
    </ul>
</x-approval.confirm-sheet>

{{-- Registration (Tier 3) --}}
<x-approval.confirm-sheet :open="$this->isSheet('acknowledge_registration')" submit="confirmRegistration" title="Confirm Buku Induk registration" :tier="3" tone="approved" icon="heroicon-o-clipboard-document-check" confirm-label="Confirm registration"
    acknowledge="I have recorded this contract in Buku Induk and the HR system."
    :error="$sheetError" :stale="$sheetStale" :success="$sheetSuccess">
    <x-slot:summary>
        <div class="ap-summary__row"><div class="ap-summary__k">Teacher</div><div class="ap-summary__v">{{ $sheetReq?->teacher?->name }}</div></div>
        <div class="ap-summary__row"><div class="ap-summary__k">Signed document</div><div class="ap-summary__v">v{{ $sheetReq?->latestDocument?->version_number }} · signed {{ $sheetReq?->latestDocument?->signed_at?->format('d M Y') ?? '—' }}</div></div>
        @if($sheetRev?->proposed_type)<div class="ap-summary__row"><div class="ap-summary__k">Terms</div><div class="ap-summary__v">{{ $tierLabel($sheetRev->current_type) }} to {{ $tierLabel($sheetRev->proposed_type) }} · {{ $sheetRev->proposed_starts_at?->format('d M Y') ?? '—' }} to {{ $sheetRev->proposed_ends_at?->format('d M Y') ?? 'open' }}</div></div>@endif
    </x-slot:summary>
    <ul class="ap-consequences">
        <li class="is-irreversible"><x-filament::icon icon="heroicon-m-lock-closed" aria-hidden="true" /><span>Marks the request complete under your name. This is the record that HR registration actually happened.</span></li>
        <li><x-filament::icon icon="heroicon-m-bell" aria-hidden="true" /><span>The principal is notified that the process is finished.</span></li>
    </ul>
</x-approval.confirm-sheet>
</x-filament-panels::page>
