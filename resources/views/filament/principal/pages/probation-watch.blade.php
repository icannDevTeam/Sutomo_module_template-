<x-filament-panels::page>
    {{-- Intro line --}}
    <div class="sp-cm-intro">
        Teachers inside their 3-month probation window. Minimum requirements:
        <b>{{ $minSup }}</b> principal supervisions ·
        <b>{{ $minPeer }}</b> peer observations ·
        decision flagged due <b>{{ $dueDays }}</b> days before probation ends.
        Adjust thresholds in
        <a href="{{ \App\Filament\Principal\Pages\ObservationConfig::getUrl(panel: 'principal') }}">Observation Configuration</a>.
    </div>

    <x-principal.academic-year-rail
        :years="$academicYearSummary"
        :selected-ay="$academicYear"
        :current-ay="$currentAy"
        select-action="selectAcademicYear"
        all-label="All AY"
    />

    @if (! $enabled)
        <div class="sp-card sp-banner" style="--accent:#dc2626;">
            <div class="sp-banner-icon"><x-filament::icon icon="heroicon-o-exclamation-triangle" style="width:18px;height:18px;color:#fff;" /></div>
            <div class="sp-banner-body">
                <b>Probation Watch is disabled</b> in Observation Configuration. Re-enable to surface probation candidates.
            </div>
        </div>
    @endif

    {{-- KPIs --}}
    <div class="sp-kpis">
        @php
            $kpis = [
                ['Total in probation',           $stats['total'],   'slate',   'heroicon-o-shield-check'],
                ['Decision due ≤ ' . $dueDays . 'd', $stats['due'], 'rose',    'heroicon-o-clock'],
                ['Missing observations',         $stats['missing'], 'amber',   'heroicon-o-exclamation-triangle'],
                ['Recommendation pending',       $stats['pending'], 'sky',     'heroicon-o-document-arrow-up'],
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
    @forelse ($rows as $r)
        @php
            $c = $r->contract;
            $supOk = $r->supervisions >= $minSup;
            $peerOk = $r->peer_obs >= $minPeer;
            $hasDecision = ! is_null($c->probation_decision);
            $rowClass = match (true) {
                $hasDecision                                          => 'is-success',
                $r->days_left <= $dueDays && (! $supOk || ! $peerOk)  => 'is-danger',
                $r->days_left <= $dueDays                             => 'is-warning',
                default                                               => '',
            };
            $daysCls = match (true) {
                $r->days_left < 0          => 'sp-days--past',
                $r->days_left <= $dueDays  => 'sp-days--due',
                default                    => 'sp-days--ok',
            };
        @endphp

        <div class="sp-card sp-cm-card {{ $rowClass }}">
            <div class="sp-onb-head">
                <div class="sp-onb-id" style="display:flex; align-items:center; gap:.75rem;">
                    <div class="sp-avatar">{{ \Illuminate\Support\Str::of($r->teacher?->name ?? '?')->substr(0, 1) }}</div>
                    <div style="min-width:0;">
                        <div class="sp-onb-name">
                            {{ $r->teacher?->name ?? 'Unknown teacher' }}
                            <span class="sp-tier sp-tier--pkwt_1">PKWT-I</span>
                        </div>
                        <div class="sp-onb-meta">
                            {{ $r->teacher?->employee_no ?? '—' }}
                            @if ($r->teacher?->campus) · {{ strtoupper($r->teacher->campus) }} @endif
                            · Probation
                            <b style="color:#0f172a;">{{ $c->probation_starts_at?->format('d M') }} → {{ $c->probation_ends_at?->format('d M Y') }}</b>
                        </div>
                    </div>
                </div>
                <div class="sp-onb-actions">
                    <span class="sp-days {{ $daysCls }}">
                        <x-filament::icon icon="heroicon-o-clock" style="width:13px;height:13px;" />
                        @if ($r->days_left < 0)
                            Probation ended {{ abs($r->days_left) }}d ago
                        @else
                            {{ $r->days_left }} days left
                        @endif
                    </span>
                </div>
            </div>

            {{-- Counters --}}
            <div class="sp-counters">
                <div class="sp-counter">
                    <div class="sp-counter__label">Supervisions</div>
                    <div class="sp-counter__value">
                        <strong class="{{ $supOk ? 'sp-ok' : 'sp-warn' }}">{{ $r->supervisions }}</strong>
                        <small>/ {{ $minSup }} required</small>
                        @if ($supOk)
                            <x-filament::icon icon="heroicon-o-check-circle" style="width:16px;height:16px;color:#059669;margin-left:auto;" />
                        @else
                            <x-filament::icon icon="heroicon-o-exclamation-circle" style="width:16px;height:16px;color:#d97706;margin-left:auto;" />
                        @endif
                    </div>
                </div>
                <div class="sp-counter">
                    <div class="sp-counter__label">Peer observations</div>
                    <div class="sp-counter__value">
                        <strong class="{{ $peerOk ? 'sp-ok' : 'sp-warn' }}">{{ $r->peer_obs }}</strong>
                        <small>/ {{ $minPeer }} required</small>
                        @if ($peerOk)
                            <x-filament::icon icon="heroicon-o-check-circle" style="width:16px;height:16px;color:#059669;margin-left:auto;" />
                        @else
                            <x-filament::icon icon="heroicon-o-exclamation-circle" style="width:16px;height:16px;color:#d97706;margin-left:auto;" />
                        @endif
                    </div>
                </div>
                <div class="sp-counter">
                    <div class="sp-counter__label">Decision</div>
                    <div class="sp-counter__value" style="display:block;">
                        @if ($hasDecision)
                            @if ($c->probation_decision === 'continue')
                                <span class="sp-pill sp-pill-green">Continue → PKWT-II</span>
                            @else
                                <span class="sp-pill sp-pill-red">Terminated</span>
                            @endif
                            <div style="font-size:.7rem;color:#94a3b8;margin-top:.25rem;">
                                {{ $c->probation_decision_at?->format('d M Y H:i') }}
                            </div>
                        @else
                            <span class="sp-pill sp-pill-gray">Pending</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Action row --}}
            <div class="sp-act-row">
                @if ($r->teacher)
                    <a href="{{ \App\Filament\Principal\Resources\TeacherObservationResource::getUrl('create', panel: 'principal') }}?teacher_id={{ $r->teacher->id }}"
                        class="sp-act sp-act--ghost">
                        <x-filament::icon icon="heroicon-o-plus" style="width:14px;height:14px;" /> Log observation
                    </a>
                @endif

                <div class="sp-act-spacer"></div>

                @if (! $hasDecision)
                    <button type="button"
                        wire:click="recordContinue({{ $c->id }})"
                        wire:confirm="Record CONTINUE decision for {{ $r->teacher?->name }}? This advances the teacher toward PKWT-II (the renewal flow handles the actual contract issuance)."
                        class="sp-act sp-act--success">
                        <x-filament::icon icon="heroicon-o-check-badge" style="width:14px;height:14px;" /> Continue → PKWT-II
                    </button>
                    <button type="button"
                        wire:click="recordTerminate({{ $c->id }})"
                        wire:confirm="TERMINATE probation for {{ $r->teacher?->name }}? This sets contract status to terminated, marks the teacher as not_renewed, and triggers the commitment fee refund flag for Finance."
                        class="sp-act sp-act--danger">
                        <x-filament::icon icon="heroicon-o-x-circle" style="width:14px;height:14px;" /> Terminate · Refund
                    </button>
                @endif
            </div>
        </div>
    @empty
        <div class="sp-cm-empty">
            <x-filament::icon icon="heroicon-o-shield-check" style="width:42px;height:42px;color:#cbd5e1;margin:0 auto;" />
            <h3>No active probations</h3>
            <p>No PKWT-I contracts are currently inside their 3-month probation window.</p>
        </div>
    @endforelse
</x-filament-panels::page>
