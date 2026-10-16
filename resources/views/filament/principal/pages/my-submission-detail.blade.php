<x-filament-panels::page>
@php
    $user = auth()->user();
    $doc  = $record->latestDocument;
    $rev  = $record->latestRevision;
    $min  = (int) config('approvals.reason_min_length', 20);
    $tierLabel = fn (?string $t) => $presenter->tierLabel($t) ?? '—';
    $loi = $record->type === 'annual_continuation' ? $record->sourceLoi : null;
    $loiPreviewUrl = $loi ? route('letter-of-intent.print', ['record' => $loi, 'workflow' => 1]) : null;
    $embeddedLoiPreviewUrl = $loi ? route('letter-of-intent.print', ['record' => $loi, 'workflow' => 1, 'embedded' => 1]) : null;
    $contractPreviewUrl = $doc
        ? route('approvals.document.download', ['document' => $doc, 'inline' => 1])
        : ($loi?->yayasan_contract_path ? route('contracts.loi.download', ['letter' => $loi, 'inline' => 1]) : null);
@endphp

<a class="ap-back" href="{{ \App\Filament\Principal\Pages\MySubmissions::getUrl(panel: 'principal') }}">
    <x-filament::icon icon="heroicon-m-arrow-left" aria-hidden="true" /> Back to Contract Workflow
</a>

<x-approval.request-detail :request="$record" :user="$user" :can-download="true" :show-stage-track="$record->type !== 'annual_continuation'">
    <x-slot:actions>
        @foreach($actions as $a)
            @if($a['key'] === 'withdraw') <span class="ap-actionbar__spacer"></span> @endif
            <x-approval.btn :variant="$a['variant']" :icon="$a['icon']" :target="'openSheet(' . $record->id . ', \'' . $a['key'] . '\')'"
                wire:click="openSheet({{ $record->id }}, '{{ $a['key'] }}')">{{ $a['label'] }}</x-approval.btn>
        @endforeach
        @if($record->type === 'annual_continuation' && $record->stage === 'document_accepted')
            <button type="button" class="ap-btn ap-btn--primary ap-btn--sm"
               wire:click="simulateAgreementSigning"
               wire:confirm="Record this Agreement handover as a demo simulation? It is not a legal teacher signature. HR will be notified."
               wire:loading.attr="disabled" wire:target="simulateAgreementSigning">
                <x-filament::icon icon="heroicon-m-finger-print" aria-hidden="true" />
                <span wire:loading.remove wire:target="simulateAgreementSigning">Record Agreement handover</span>
                <span wire:loading wire:target="simulateAgreementSigning">Recording…</span>
            </button>
        @endif
        @if($record->type === 'annual_continuation' && $record->stage === 'hr_registration')
            <button type="button" class="ap-btn ap-btn--primary ap-btn--sm"
               wire:click="recordBukuInduk"
               wire:confirm="Confirm Buku Induk is updated for {{ $record->teacher?->name }}? This completes the continuation and notifies HR."
               wire:loading.attr="disabled" wire:target="recordBukuInduk">
                <x-filament::icon icon="heroicon-m-book-open" aria-hidden="true" />
                <span wire:loading.remove wire:target="recordBukuInduk">Record Buku Induk</span>
                <span wire:loading wire:target="recordBukuInduk">Saving…</span>
            </button>
        @endif
    </x-slot:actions>
</x-approval.request-detail>

@if($loi)
    <section class="ap-section" style="margin-top:1rem">
        <h2 class="ap-section-h">Continuation progress</h2>
        @if($continuationStages)
            <div class="sp-stepper" aria-label="Continuation progress">
                @foreach($continuationStages as $stage)
                    <div class="sp-step sp-step-{{ $stage['state'] }}">
                        <span class="sp-step-dot">
                            @if($stage['state'] === 'done')
                                <x-filament::icon icon="heroicon-m-check" style="width:12px;height:12px;color:#fff;" />
                            @elseif($stage['state'] === 'current')
                                <span class="sp-step-pulse"></span>
                            @elseif($stage['state'] === 'failed')
                                <x-filament::icon icon="heroicon-m-x-mark" style="width:12px;height:12px;color:#fff;" />
                            @else
                                <x-filament::icon icon="heroicon-m-lock-closed" style="width:11px;height:11px;color:#94a3b8;" />
                            @endif
                        </span>
                        <span class="sp-step-label">{{ $stage['label'] }}</span>
                    </div>
                @endforeach
            </div>
        @endif
        <p class="ap-muted" style="margin:.7rem 0 0">
            LOI {{ \App\Models\LetterOfIntent::STATUSES[$loi->status] ?? $loi->status }}
            @if($loi->signed_at) · signed {{ $loi->signed_at->format('d M Y') }} @endif
            @if($loi->academic_year) · AY {{ $loi->academic_year }} @endif
        </p>
    </section>

    <section class="ap-section" style="margin-top:1rem">
        <h2 class="ap-section-h">LOI and contract preview</h2>
        <div class="ap-terms">
            <div>
                <div class="ap-term__k">Letter of Intent</div>
                <div class="ap-term__v">{{ $loi->teacher?->name }} · {{ $loi->academic_year }}</div>
                <a class="ap-btn ap-btn--secondary ap-btn--sm" href="{{ $loiPreviewUrl }}" target="_blank" rel="noopener">
                    <x-filament::icon icon="heroicon-m-eye" aria-hidden="true" /> Open LOI preview
                </a>
            </div>
            <div>
                <div class="ap-term__k">Prepared contract</div>
                <div class="ap-term__v">{{ $doc?->original_filename ?? ($loi->yayasan_contract_path ? 'Legacy uploaded contract' : 'Not uploaded yet') }}</div>
                @if($contractPreviewUrl)
                    <a class="ap-btn ap-btn--secondary ap-btn--sm" href="{{ $contractPreviewUrl }}" target="_blank" rel="noopener">
                        <x-filament::icon icon="heroicon-m-eye" aria-hidden="true" /> Preview contract
                    </a>
                @endif
            </div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,24rem),1fr));gap:1rem;margin-top:1rem">
            <iframe src="{{ $embeddedLoiPreviewUrl }}" loading="lazy" title="Letter of Intent preview" style="width:100%;height:32rem;border:1px solid #dbe2ea;border-radius:.75rem;background:white"></iframe>
            @if($contractPreviewUrl)
                <iframe src="{{ $contractPreviewUrl }}" loading="lazy" title="Prepared contract preview" style="width:100%;height:32rem;border:1px solid #dbe2ea;border-radius:.75rem;background:white"></iframe>
            @endif
        </div>
    </section>
@endif

{{-- ── Accept document (Tier 3) ─────────────────────────────────────── --}}
<x-approval.confirm-sheet
    :open="$this->isSheet('accept_document')"
    submit="confirmAcceptDocument"
    title="Accept document for signing"
    :tier="3" tone="approved" icon="heroicon-o-document-check"
    confirm-label="Accept and release for signing"
    acknowledge="I have opened and read this exact document version and confirm it reflects the approved terms."
    :error="$sheetError" :stale="$sheetStale" :success="$sheetSuccess">
    <x-slot:summary>
        <div class="ap-summary__row"><div class="ap-summary__k">Teacher</div><div class="ap-summary__v">{{ $record->teacher?->name }}</div></div>
        <div class="ap-summary__row"><div class="ap-summary__k">Document</div><div class="ap-summary__v">v{{ $doc?->version_number }} · {{ $doc?->original_filename ?? '—' }}</div></div>
        <div class="ap-summary__row"><div class="ap-summary__k">Uploaded</div><div class="ap-summary__v">{{ $doc?->uploaded_at?->format('d M Y, H:i') }} by {{ $doc?->uploader?->name ?? $record->assignedHr?->name ?? 'HR' }}</div></div>
        @if($doc?->integrity_hash)<div class="ap-summary__row"><div class="ap-summary__k">Fingerprint</div><div class="ap-summary__v ap-mono" style="font-size:.76rem">{{ $doc->integrity_hash }}</div></div>@endif
        @if($rev?->proposed_type)<div class="ap-summary__row"><div class="ap-summary__k">Approved terms</div><div class="ap-summary__v">{{ $tierLabel($rev->current_type) }} to {{ $tierLabel($rev->proposed_type) }} · {{ $rev->proposed_starts_at?->format('d M Y') ?? '—' }} to {{ $rev->proposed_ends_at?->format('d M Y') ?? 'open' }}</div></div>@endif
    </x-slot:summary>
    <ul class="ap-consequences">
        <li class="is-irreversible"><x-filament::icon icon="heroicon-m-lock-closed" aria-hidden="true" /><span>Acceptance is recorded against version {{ $doc?->version_number }} and cannot be undone.</span></li>
        <li><x-filament::icon icon="heroicon-m-pencil-square" aria-hidden="true" /><span>{{ $record->teacher?->name ?? 'The teacher' }} will be notified and can sign this version only.</span></li>
        <li><x-filament::icon icon="heroicon-m-arrow-path" aria-hidden="true" /><span>If HR uploads a new version later, you will have to accept it again.</span></li>
        <li><x-filament::icon icon="heroicon-m-information-circle" aria-hidden="true" /><span>This does not activate employment; activation follows signing and the effective date.</span></li>
    </ul>
    @if($doc)
        <a class="ap-btn ap-btn--secondary ap-btn--sm" href="{{ route('approvals.document.download', $doc) }}" target="_blank" rel="noopener" style="align-self:flex-start">
            <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" aria-hidden="true" /><span>Open document in a new tab</span>
        </a>
    @endif
</x-approval.confirm-sheet>

{{-- ── Request document correction (Tier 2) ─────────────────────────── --}}
<x-approval.confirm-sheet
    :open="$this->isSheet('request_doc_fix')"
    submit="confirmDocumentCorrection"
    title="Request a document correction"
    :tier="2" tone="waiting" icon="heroicon-o-arrow-uturn-left"
    confirm-label="Send to HR"
    description="Describe precisely what is wrong. HR's task reopens, the current version is kept for the record, and you will review the replacement."
    :error="$sheetError" :stale="$sheetStale" :success="$sheetSuccess">
    <div class="ap-reasons" aria-label="Common reasons">
        @foreach(['Contract dates do not match the approved terms.', 'Teacher name or employee number is incorrect.', 'Salary or allowance figures are missing or wrong.', 'Signature block or page is missing.'] as $r)
            <button type="button" class="ap-reason" wire:click="$set('reason', @js($r))">{{ \Illuminate\Support\Str::limit($r, 36) }}</button>
        @endforeach
    </div>
    <div class="ap-field">
        <label class="ap-label" for="doc-fix-reason">What needs to change <span class="req">required</span></label>
        <textarea id="doc-fix-reason" class="ap-textarea" wire:model="reason" rows="4" placeholder="e.g. Page 2: end date shows 30 Jun 2026; approved terms end 31 Jul 2026."></textarea>
        <div class="ap-help">At least {{ $min }} characters. HR sees this text verbatim.</div>
    </div>
    <ul class="ap-consequences">
        <li><x-filament::icon icon="heroicon-m-user" aria-hidden="true" /><span>Goes back to {{ $record->assignedHr?->name ?? 'the assigned HR officer' }}. Yayasan is not involved unless the approved terms change.</span></li>
    </ul>
</x-approval.confirm-sheet>

{{-- ── Edit and resubmit (Tier 2 with form + diff) ─────────────────── --}}
<x-approval.confirm-sheet
    :open="$this->isSheet('resubmit')"
    submit="confirmResubmit"
    title="Edit and resubmit to Yayasan"
    :tier="2" tone="primary" icon="heroicon-o-paper-airplane"
    confirm-label="Resubmit as new revision" wide
    :error="$sheetError" :stale="$sheetStale" :success="$sheetSuccess">
    @php $note = $presenter->lastNoteOfType(['correction.requested']); @endphp
    @if($note)
        <div class="ap-notice ap-notice--waiting">
            <x-filament::icon icon="heroicon-m-exclamation-triangle" aria-hidden="true" />
            <div><div class="ap-notice__title">Yayasan asked you to correct</div>{{ $note }}</div>
        </div>
    @endif
    @if($record->type === 'pkwt_renewal')
        <div class="ap-terms">
            <div class="ap-field">
                <label class="ap-label" for="rs-rec">Recommendation</label>
                <select id="rs-rec" class="ap-select" wire:model="form.recommendation">
                    <option value="renew">Renew</option>
                    <option value="not_renew">Do not renew</option>
                </select>
            </div>
            <div class="ap-field">
                <label class="ap-label" for="rs-tier">Proposed tier</label>
                <select id="rs-tier" class="ap-select" wire:model="form.proposed_type">
                    @foreach($tiers as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
                </select>
            </div>
            <div class="ap-field">
                <label class="ap-label" for="rs-start">Proposed start</label>
                <input id="rs-start" type="date" class="ap-input" wire:model="form.proposed_starts_at">
            </div>
            <div class="ap-field">
                <label class="ap-label" for="rs-end">Proposed end</label>
                <input id="rs-end" type="date" class="ap-input" wire:model="form.proposed_ends_at">
                <div class="ap-help">Leave empty for Guru SK.</div>
            </div>
        </div>
    @endif
    <div class="ap-field">
        <label class="ap-label" for="rs-just">Updated justification <span class="req">required</span></label>
        <textarea id="rs-just" class="ap-textarea" wire:model="form.justification" rows="5"></textarea>
        <div class="ap-help">Explain what changed in response to the note above. Revision {{ ($rev?->revision_number ?? 0) + 1 }} will be created; revision {{ $rev?->revision_number ?? 1 }} stays visible to Yayasan.</div>
    </div>
</x-approval.confirm-sheet>

{{-- ── Withdraw (Tier 2) ────────────────────────────────────────────── --}}
<x-approval.confirm-sheet
    :open="$this->isSheet('withdraw')"
    submit="confirmWithdraw"
    title="Withdraw this request"
    :tier="2" tone="danger" icon="heroicon-o-no-symbol"
    confirm-label="Withdraw request"
    description="The request is closed and Yayasan is told why. The teacher's contract or letter of intent is not changed, and you can submit a new request later."
    :error="$sheetError" :stale="$sheetStale" :success="$sheetSuccess">
    <div class="ap-field">
        <label class="ap-label" for="wd-reason">Reason <span class="req">required</span></label>
        <textarea id="wd-reason" class="ap-textarea" wire:model="reason" rows="3" placeholder="e.g. Submitted against the wrong academic year; will resubmit."></textarea>
        <div class="ap-help">At least {{ $min }} characters.</div>
    </div>
</x-approval.confirm-sheet>
</x-filament-panels::page>
