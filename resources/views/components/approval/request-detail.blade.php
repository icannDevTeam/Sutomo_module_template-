{{-- Full request detail workspace: header, stage track, terms, documents, timeline. Action bar passed via slot. --}}
@props(['request', 'user', 'canDownload' => false, 'compare' => true, 'showStageTrack' => true])
@php
    $p = \App\Support\ContractRequestPresenter::for($request);
    $rev = $request->latestRevision;
    $correctionNote = $request->stage === 'correction_requested' ? $p->lastNoteOfType(['correction.requested']) : null;
    $docCorrectionNote = $request->stage === 'document_correction' ? ($request->latestDocument?->correction_note ?? $p->lastNoteOfType(['document.correction'])) : null;
@endphp
<section {{ $attributes->merge(['class' => 'ap-detail']) }} aria-label="Request detail">
    <header class="ap-detail__head">
        <div class="ap-detail__title">
            <x-approval.type-chip :type="$request->type" />
            <h2 class="ap-detail__name">{{ $request->teacher?->name ?? 'Unknown teacher' }}</h2>
            <x-approval.tier-transition :from="$rev?->current_type" :to="$rev?->proposed_type" />
            @if($rev?->academic_year)<span class="ap-muted" style="font-size:.8rem">AY {{ $rev->academic_year }}</span>@endif
        </div>
        <div class="ap-detail__sub">
            <span>Request #{{ $request->id }}</span>
            @if($request->submitter)<span>Submitted by {{ $request->submitter->name }}@if($request->campus) · {{ strtoupper($request->campus) }}@endif</span>@endif
            @if($request->submitted_at)<span>{{ $request->submitted_at->format('d M Y, H:i') }}</span>@endif
            @if($request->teacher?->employee_no)<span>{{ $request->teacher->employee_no }}</span>@endif
        </div>
        <div class="ap-detail__status">
            <x-approval.stage-badge :presenter="$p" size="lg" />
            <x-approval.owner-chip :presenter="$p" :user="$user" />
            <x-approval.age :presenter="$p" />
            @if($p->lastActivityLabel())<span class="ap-muted" style="font-size:.76rem">{{ $p->lastActivityLabel() }}</span>@endif
        </div>
        @isset($actions)
            <div class="ap-actionbar" style="margin-top:.25rem">{{ $actions }}</div>
        @endisset
    </header>

    @if($showStageTrack)
        <x-approval.stage-track :presenter="$p" />
    @endif

    @isset($decision){{ $decision }}@endisset

    @if($correctionNote || $docCorrectionNote || $request->stage === 'rejected' || $request->stage === 'document_uploaded' || $request->stage === 'document_accepted' || $request->stage === 'signed_scheduled')
        <div style="padding:1rem 1.25rem 0">
            @if($correctionNote)
                <div class="ap-notice ap-notice--waiting">
                    <x-filament::icon icon="heroicon-m-exclamation-triangle" aria-hidden="true" />
                    <div><div class="ap-notice__title">Yayasan asked for a correction to the submission</div>{{ $correctionNote }}</div>
                </div>
            @elseif($docCorrectionNote)
                <div class="ap-notice ap-notice--waiting">
                    <x-filament::icon icon="heroicon-m-arrow-uturn-left" aria-hidden="true" />
                    <div><div class="ap-notice__title">Document correction in progress</div>{{ $docCorrectionNote }}</div>
                </div>
            @elseif($request->stage === 'rejected')
                <div class="ap-notice ap-notice--rejected">
                    <x-filament::icon icon="heroicon-m-x-circle" aria-hidden="true" />
                    <div><div class="ap-notice__title">Request rejected by {{ $request->decider?->name ?? 'Yayasan' }} on {{ $request->decided_at?->format('d M Y') }}</div>{{ $request->decision_reason ?: 'No reason recorded.' }}<br><span class="ap-muted">The teacher's current contract is unchanged.</span></div>
                </div>
            @elseif($request->stage === 'document_uploaded')
                <div class="ap-notice ap-notice--review">
                    <x-filament::icon icon="heroicon-m-document-arrow-up" aria-hidden="true" />
                    <div><div class="ap-notice__title">Document v{{ $request->latestDocument?->version_number ?? '—' }} is ready for principal review</div>Uploaded by {{ $request->latestDocument?->uploader?->name ?? $request->assignedHr?->name ?? 'HR' }} {{ $request->latestDocument?->uploaded_at?->diffForHumans() }}. Accept it to release it for teacher signing, or request a correction.</div>
                </div>
            @elseif($request->stage === 'document_accepted')
                <div class="ap-notice ap-notice--approved">
                    <x-filament::icon icon="heroicon-m-document-check" aria-hidden="true" />
                    <div><div class="ap-notice__title">Accepted — Agreement handover is ready</div>
                        @if($request->type === 'annual_continuation')
                            Record the demo Agreement handover from this workflow. It is not a legal teacher signature.
                        @else
                            Waiting for {{ $request->teacher?->name ?? 'the named teacher' }} to sign version {{ $request->latestDocument?->version_number ?? '—' }}.
                        @endif
                    </div>
                </div>
            @elseif($request->stage === 'signed_scheduled')
                <div class="ap-notice ap-notice--approved">
                    <x-filament::icon icon="heroicon-m-calendar-days" aria-hidden="true" />
                    <div><div class="ap-notice__title">Signed — scheduled</div>The next contract activates on {{ $request->approvedRevision?->proposed_starts_at?->format('d M Y') ?? 'its effective date' }} once HR confirms registration.</div>
                </div>
            @endif
        </div>
    @endif

    <div class="ap-detail__body">
        <div class="ap-detail__col">
            <div class="ap-section">
                <h3 class="ap-section-h">What was submitted</h3>
                <x-approval.terms-panel :request="$request" :compare="$compare" />
            </div>
            @if($request->assigned_hr_id)
                <div class="ap-section">
                    <h3 class="ap-section-h">HR preparation</h3>
                    <div class="ap-terms">
                        <div><div class="ap-term__k">Assigned to</div><div class="ap-term__v">{{ $request->assignedHr?->name ?? '—' }}</div></div>
                        <div><div class="ap-term__k">Assigned</div><div class="ap-term__v">{{ $request->hr_assigned_at?->format('d M Y') ?? '—' }}</div></div>
                        <div><div class="ap-term__k">Due</div><div class="ap-term__v">{{ $request->hr_preparation_due_at?->format('d M Y') ?? '—' }}</div></div>
                    </div>
                    @if($request->hr_instructions)<p class="ap-prose" style="margin-top:.6rem">{{ $request->hr_instructions }}</p>@endif
                </div>
            @endif
            {{ $slot }}
        </div>
        <div class="ap-detail__col">
            <div class="ap-section">
                <h3 class="ap-section-h">Documents</h3>
                <x-approval.document-list :request="$request" :can-download="$canDownload" />
            </div>
            <div class="ap-section">
                <h3 class="ap-section-h">Activity <span class="ap-muted" style="text-transform:none;letter-spacing:0;font-weight:500">{{ $request->events->count() }} events</span></h3>
                <x-approval.event-timeline :events="$request->events" />
            </div>
        </div>
    </div>
</section>
