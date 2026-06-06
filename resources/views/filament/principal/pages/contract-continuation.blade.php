<x-filament-panels::page>
    {{-- Intro --}}
    <div class="sp-cm-intro">
        Per-teacher continuation pipeline for permanent (Guru SK) staff.
        Stages unlock in order:
        <b>Submit to Yayasan → Contract Uploaded → Principal Review → Agreement Letter Signed → Buku Induk → Complete</b>.
        Bulk submission lives on the
        <a href="{{ \App\Filament\Principal\Resources\LetterOfIntentResource::getUrl(panel: 'principal') }}?activeTab=signed">Signed tab</a>
        of the LOI list.
    </div>

    <x-principal.academic-year-rail
        :years="$academicYearSummary"
        :selected-ay="$academicYear"
        :current-ay="$currentAy"
        select-action="selectAcademicYear"
        :show-all="false"
    />

    {{-- KPIs --}}
    <div class="sp-kpis">
        @php
            $kpis = [
                ['Total in ' . $academicYear, $stats['total'],            'slate',   'heroicon-o-users'],
                ['Pending Agreement Letter', $stats['pending_agreement'], 'amber', 'heroicon-o-pencil-square'],
                ['Needs Principal Review',   $stats['review_required'],   'rose',    'heroicon-o-exclamation-triangle'],
                ['In progress with Yayasan',   $stats['in_progress'],     'sky',     'heroicon-o-paper-airplane'],
                ['Handed over',                $stats['handed_over'],     'emerald', 'heroicon-o-check-badge'],
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

    {{-- Pipeline rows --}}
    @php
        $stageDef = [
            ['key' => 'sent',              'label' => 'Sent'],
            ['key' => 'signed',            'label' => 'Signed'],
            ['key' => 'submitted',         'label' => 'Yayasan'],
            ['key' => 'contract_uploaded', 'label' => 'Contract'],
            ['key' => 'review',            'label' => 'Review'],
            ['key' => 'agreement_signed',  'label' => 'Agreement Letter'],
            ['key' => 'buku_induk',        'label' => 'Buku Induk'],
            ['key' => 'complete',          'label' => 'Complete'],
        ];

        $stageStateFor = function (\App\Models\LetterOfIntent $loi, string $stageKey): string {
            $done = match ($stageKey) {
                'sent'              => ! is_null($loi->sent_at) || in_array($loi->status, ['sent', 'signed']),
                'signed'            => ! is_null($loi->signed_at),
                'submitted'         => ! is_null($loi->submitted_to_yayasan_at),
                'contract_uploaded' => ! is_null($loi->yayasan_contract_uploaded_at),
                'review'            => $loi->yayasan_review_status === 'accepted',
                'agreement_signed'  => ! is_null($loi->agreement_signed_at),
                'buku_induk'        => ! is_null($loi->buku_induk_recorded_at),
                'complete'          => ! is_null($loi->continuation_completed_at),
                default             => false,
            };
            if ($done) return 'done';

            $next = match (true) {
                ! is_null($loi->continuation_completed_at) => null,
                ! is_null($loi->buku_induk_recorded_at)    => 'complete',
                ! is_null($loi->agreement_signed_at)       => 'buku_induk',
                ! is_null($loi->yayasan_contract_uploaded_at) && $loi->yayasan_review_status === 'accepted' => 'agreement_signed',
                ! is_null($loi->yayasan_contract_uploaded_at) => 'review',
                ! is_null($loi->submitted_to_yayasan_at)   => 'contract_uploaded',
                ! is_null($loi->signed_at)                 => 'submitted',
                $loi->status === 'sent'                    => 'signed',
                default                                    => 'sent',
            };
            return $stageKey === $next ? 'current' : 'locked';
        };
    @endphp

    @forelse ($rows as $loi)
        @php
            $isComplete = ! is_null($loi->continuation_completed_at);
            $rowClass   = $isComplete ? 'is-success' : '';
            $currentStage = collect($stageDef)
                ->map(fn ($s) => ['label' => $s['label'], 'state' => $stageStateFor($loi, $s['key'])])
                ->firstWhere('state', 'current');
        @endphp

        <div class="sp-card sp-cm-card {{ $rowClass }}">
            <div class="sp-onb-head">
                <div class="sp-onb-id" style="display:flex; align-items:center; gap:.75rem;">
                    <div class="sp-avatar">{{ \Illuminate\Support\Str::of($loi->teacher?->name ?? '?')->substr(0, 1) }}</div>
                    <div style="min-width:0;">
                        <div class="sp-onb-name">
                            {{ $loi->teacher?->name ?? 'Unknown teacher' }}
                            <span class="sp-tier sp-tier--guru_sk">Guru SK</span>
                        </div>
                        <div class="sp-onb-meta">
                            {{ $loi->teacher?->employee_no ?? '—' }}
                            · LOI {{ \App\Models\LetterOfIntent::STATUSES[$loi->status] ?? $loi->status }}
                            @if ($loi->academic_year) · AY {{ $loi->academic_year }} @endif
                        </div>
                    </div>
                </div>
                <div class="sp-onb-actions">
                    @if ($isComplete)
                        <span class="sp-pill sp-pill-green">
                            <x-filament::icon icon="heroicon-o-check-badge" style="width:13px;height:13px;" />
                            Complete
                        </span>
                    @endif
                    @if ($loi->yayasan_review_status === 'needs_revision')
                        <span class="sp-pill" style="background:#fff1f2;color:#be123c;">
                            <x-filament::icon icon="heroicon-o-arrow-path" style="width:13px;height:13px;" />
                            Sent Back to Yayasan
                        </span>
                    @elseif ($loi->yayasan_review_status === 'accepted')
                        <span class="sp-pill" style="background:#ecfdf5;color:#047857;">
                            <x-filament::icon icon="heroicon-o-check-circle" style="width:13px;height:13px;" />
                            Contract Accepted
                        </span>
                    @endif
                </div>
            </div>

            {{-- Stepper --}}
            <div class="sp-stepper" style="margin-top:.9rem;">
                @foreach ($stageDef as $s)
                    @php $state = $stageStateFor($loi, $s['key']); @endphp
                    <div class="sp-step sp-step-{{ $state }}">
                        <span class="sp-step-dot">
                            @if ($state === 'done')
                                <x-filament::icon icon="heroicon-m-check" style="width:12px;height:12px;color:#fff;" />
                            @elseif ($state === 'current')
                                <span class="sp-step-pulse"></span>
                            @else
                                <x-filament::icon icon="heroicon-m-lock-closed" style="width:11px;height:11px;color:#94a3b8;" />
                            @endif
                        </span>
                        <span class="sp-step-label">{{ $s['label'] }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Active stage hint --}}
            @if ($currentStage)
                <div class="sp-step-hint">
                    <x-filament::icon icon="heroicon-m-information-circle" style="width:14px;height:14px;" />
                    <span>Next: <b>{{ $currentStage['label'] }}</b></span>
                </div>
            @else
                <div class="sp-step-hint sp-step-hint-done">
                    <x-filament::icon icon="heroicon-m-sparkles" style="width:14px;height:14px;" />
                    <span><b>Continuation complete.</b> Teacher fully transferred for {{ $loi->academic_year }}.</span>
                </div>
            @endif

            {{-- Timestamps strip --}}
            <div class="sp-counters" style="grid-template-columns:repeat(4,1fr); margin-top:.75rem;">
                <div class="sp-counter">
                    <div class="sp-counter__label">Signed</div>
                    <div class="sp-counter__value"><strong style="font-size:.95rem;">{{ $loi->signed_at?->format('d M Y') ?? '—' }}</strong></div>
                </div>
                <div class="sp-counter">
                    <div class="sp-counter__label">Submitted</div>
                    <div class="sp-counter__value"><strong style="font-size:.95rem;">{{ $loi->submitted_to_yayasan_at?->format('d M Y') ?? '—' }}</strong></div>
                </div>
                <div class="sp-counter">
                    <div class="sp-counter__label">Contract uploaded</div>
                    <div class="sp-counter__value"><strong style="font-size:.95rem;">{{ $loi->yayasan_contract_uploaded_at?->format('d M Y') ?? '—' }}</strong></div>
                </div>
                <div class="sp-counter">
                    <div class="sp-counter__label">Agreement Letter</div>
                    <div class="sp-counter__value"><strong style="font-size:.95rem;">{{ $loi->agreement_signed_at?->format('d M Y') ?? '—' }}</strong></div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="sp-act-row">
                <a href="{{ \App\Filament\Principal\Resources\LetterOfIntentResource::getUrl('view', ['record' => $loi->id], panel: 'principal') }}"
                    class="sp-act sp-act--ghost">
                    <x-filament::icon icon="heroicon-o-eye" style="width:14px;height:14px;" /> View LOI
                </a>

                @if ($loi->yayasan_contract_path)
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($loi->yayasan_contract_path) }}"
                        target="_blank"
                        class="sp-act sp-act--ghost">
                        <x-filament::icon icon="heroicon-o-document-text" style="width:14px;height:14px;" /> View Contract
                    </a>
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($loi->yayasan_contract_path) }}"
                        target="_blank"
                        class="sp-act sp-act--ghost">
                        <x-filament::icon icon="heroicon-o-printer" style="width:14px;height:14px;" /> Print Contract
                    </a>
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($loi->yayasan_contract_path) }}"
                        download
                        class="sp-act sp-act--ghost">
                        <x-filament::icon icon="heroicon-o-arrow-down-tray" style="width:14px;height:14px;" /> Download
                    </a>
                @endif

                <div class="sp-act-spacer"></div>

                @if ($loi->canOpenAgreementLetter())
                    <a href="{{ \App\Filament\Principal\Pages\SignLetterOfIntent::getUrl(['record' => $loi->id, 'mode' => 'agreement'], panel: 'principal') }}"
                        class="sp-act sp-act--amber">
                        <x-filament::icon icon="heroicon-o-finger-print" style="width:14px;height:14px;" /> Open Agreement Letter
                    </a>
                @endif

                @if (! is_null($loi->agreement_signed_at) && is_null($loi->buku_induk_recorded_at))
                    <button type="button"
                        wire:click="recordBukuInduk({{ $loi->id }})"
                        wire:confirm="Confirm: Buku Induk has been updated for {{ $loi->teacher?->name }}?"
                        class="sp-act sp-act--slate">
                        <x-filament::icon icon="heroicon-o-book-open" style="width:14px;height:14px;" /> Record Buku Induk
                    </button>
                @endif

                @if ($loi->canMarkContinuationComplete())
                    <button type="button"
                        wire:click="markComplete({{ $loi->id }})"
                        wire:confirm="Mark continuation as complete?"
                        class="sp-act sp-act--success">
                        <x-filament::icon icon="heroicon-o-check-badge" style="width:14px;height:14px;" /> Mark complete
                    </button>
                @endif
            </div>

            @if (! is_null($loi->yayasan_contract_uploaded_at) && is_null($loi->agreement_signed_at))
                <div style="margin-top:.8rem; border:1px solid #e2e8f0; border-radius:12px; padding:.8rem; background:#f8fafc;">
                    <label style="display:block; font-size:.78rem; font-weight:700; color:#334155; margin-bottom:.35rem;">
                        Principal Review Note (required to resubmit)
                    </label>
                    <textarea
                        wire:model="resubmitNotes.{{ $loi->id }}"
                        rows="3"
                        placeholder="Write note for Yayasan if contract needs correction."
                        style="width:100%; border:1px solid #cbd5e1; border-radius:10px; font-size:.82rem; padding:.55rem .65rem; background:#fff;"></textarea>
                    <div style="display:flex; justify-content:flex-end; gap:.55rem; margin-top:.55rem;">
                        <button type="button"
                            wire:click="resubmitContract({{ $loi->id }})"
                            class="sp-act"
                            style="background:#ffe4e6; color:#be123c; border:1px solid #fecdd3;">
                            <x-filament::icon icon="heroicon-o-arrow-uturn-left" style="width:14px;height:14px;" /> Resubmit to Yayasan
                        </button>
                        <button type="button"
                            wire:click="acceptContract({{ $loi->id }})"
                            class="sp-act sp-act--success">
                            <x-filament::icon icon="heroicon-o-check-circle" style="width:14px;height:14px;" /> Accept Contract
                        </button>
                    </div>
                    @if ($loi->yayasan_resubmit_notes)
                        <div style="margin-top:.55rem; font-size:.76rem; color:#be123c;">
                            Last resubmit note: {{ $loi->yayasan_resubmit_notes }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @empty
        <div class="sp-cm-empty">
            <x-filament::icon icon="heroicon-o-inbox" style="width:42px;height:42px;color:#cbd5e1;margin:0 auto;" />
            <h3>No Guru SK continuations for {{ $academicYear }}</h3>
            <p>This page only lists permanent teachers (status = Guru SK) with an LOI in the selected academic year.
            Send LOIs from the LOI list to populate this pipeline.</p>
        </div>
    @endforelse
</x-filament-panels::page>
