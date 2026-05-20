<x-filament-panels::page>
    {{-- =====================================================
         FUNNEL CTAs (Accepted / Waitlist / Withdrawn / Failed)
         ===================================================== --}}
    <div class="sp-funnel">
        @foreach ([
            'accepted'   => ['Accepted',     'heroicon-o-check-badge',   '#10b981'],
            'waitlisted' => ['Waiting List', 'heroicon-o-clock',         '#f59e0b'],
            'withdrawn'  => ['Withdrawn',    'heroicon-o-arrow-uturn-left','#94a3b8'],
            'failed'     => ['Failed',       'heroicon-o-x-circle',      '#ef4444'],
        ] as $key => $cfg)
            <button type="button" wire:click="setActiveTab('{{ $key }}')"
                class="sp-funnel-cta {{ $tab === $key ? 'is-active' : '' }}"
                style="--cta:{{ $cfg[2] }};">
                <span class="sp-funnel-icon">
                    <x-filament::icon :icon="$cfg[1]" style="width:22px;height:22px;" />
                </span>
                <span class="sp-funnel-body">
                    <span class="sp-funnel-label">{{ $cfg[0] }}</span>
                    <span class="sp-funnel-count">{{ $counts[$key] }}</span>
                </span>
            </button>
        @endforeach
    </div>

    {{-- =====================================================
         CONTEXT BANNER per tab
         ===================================================== --}}
    @php
        $banner = match ($tab) {
            'accepted'   => ['#4338ca','heroicon-o-information-circle','Each row shows the onboarding flow. Steps unlock in order — Dev Fee → Books → Class → Observation (5 days) → Student ID → CCA + e-Books → Tuition. Status changes move students between tabs automatically.'],
            'waitlisted' => ['#f59e0b','heroicon-o-clock','Students on the waiting list can be reinstated or withdrawn. Reinstating moves them back to Accepted at the start of the onboarding flow.'],
            'withdrawn'  => ['#64748b','heroicon-o-archive-box','Voluntarily withdrawn or declined. Records are kept for the academic year audit. Reinstate to restart onboarding.'],
            'failed'     => ['#ef4444','heroicon-o-exclamation-triangle','Did not pass the placement exam threshold. Reinstating sends them back to Accepted (use only with principal override).'],
        };
    @endphp
    <div class="sp-card sp-banner" style="--accent:{{ $banner[0] }};">
        <div class="sp-banner-icon"><x-filament::icon :icon="$banner[1]" style="width:18px;height:18px;color:#fff;" /></div>
        <div class="sp-banner-body">{{ $banner[2] }}</div>
    </div>

    {{-- =====================================================
         FILTERS
         ===================================================== --}}
    <div class="sp-card sp-filters">
        <div class="sp-filter">
            <label>Search</label>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Name, code, or wali…" />
        </div>
        <div class="sp-filter">
            <label>Campus</label>
            <select wire:model.live="campusFilter">
                <option value="">All</option>
                @foreach ($campuses as $c)
                    <option value="{{ $c }}">{{ strtoupper($c) }}</option>
                @endforeach
            </select>
        </div>
        <div class="sp-filter">
            <label>Unit</label>
            <select wire:model.live="unitFilter">
                <option value="">All</option>
                @foreach ($units as $u)
                    <option value="{{ $u }}">{{ $u }}</option>
                @endforeach
            </select>
        </div>
        <div class="sp-filter">
            <label>Grade</label>
            <select wire:model.live="gradeFilter">
                <option value="">All</option>
                @foreach ($grades as $g)
                    <option value="{{ $g }}">{{ $g }}</option>
                @endforeach
            </select>
        </div>
        <div class="sp-filter">
            <label>Stream</label>
            <select wire:model.live="streamFilter">
                <option value="">All</option>
                @foreach ($streams as $s)
                    <option value="{{ $s }}">{{ strtoupper($s) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- =====================================================
         ROWS
         ===================================================== --}}
    <div class="sp-onb-list">
        @forelse ($rows as $row)
            @php
                /** @var \App\Models\Application $a */
                $a = $row['app'];
                $stages = $row['stages'];
                $score = $a->placement_score;
                $obsDays = data_get($a->meta, 'observation.days', []);
                if (is_array($obsDays) && $obsDays) {
                    $absences = collect($obsDays)->filter(fn ($d) => ($d['present'] ?? null) === false)->count();
                } else {
                    $absences = collect(data_get($a->meta, 'attendance', []))->filter(fn ($v) => $v === false)->count();
                }
                $needsCheckup = $absences >= 3
                    && isset($stages['attendance'])
                    && $stages['attendance']['state'] !== 'done';
            @endphp

            <div class="sp-card sp-onb-card">
                {{-- Identity row --}}
                <div class="sp-onb-head">
                    <div class="sp-onb-id">
                        <div class="sp-onb-name">
                            {{ $a->name }}
                            @if ($a->is_teacher_child) <span class="sp-pill sp-pill-amber">★ Teacher child</span> @endif
                        </div>
                        <div class="sp-onb-meta">
                            {{ $a->code }} · {{ \App\Models\Application::applicantTypeLabel($a->applicant_type) }}
                            · {{ strtoupper($a->campus ?? '—') }}
                            @if ($a->unit) · {{ $a->unit }} @endif
                            · Grade {{ $a->grade }}
                            @if ($a->stream) · {{ strtoupper($a->stream) }} @endif
                            @if ($a->nisn) · <span class="sp-pill sp-pill-slate">NISN {{ $a->nisn }}</span> @endif
                            @if ($score !== null) · <b>Score {{ $score }}</b> @endif
                            @if ($a->assigned_student_no) · <span class="sp-pill sp-pill-blue">ID {{ $a->assigned_student_no }}</span> @endif
                            @if ($needsCheckup) · <span class="sp-pill sp-pill-red">⚠ Check-up: {{ $absences }} absences</span> @endif
                        </div>
                    </div>
                    <div class="sp-onb-actions">
                        @if ($tab === 'accepted')
                            {{ ($this->moveToWaitlistAction)(['id' => $a->id]) }}
                            {{ ($this->withdrawAction)(['id' => $a->id]) }}
                        @elseif ($tab === 'waitlisted' || $tab === 'withdrawn' || $tab === 'failed')
                            {{ ($this->reinstateAction)(['id' => $a->id]) }}
                            @if ($tab !== 'withdrawn')
                                {{ ($this->withdrawAction)(['id' => $a->id]) }}
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Stepper (only meaningful on Accepted tab) --}}
                @if ($tab === 'accepted')
                    <div class="sp-stepper" role="list">
                        @foreach ($stages as $s)
                            <div class="sp-step sp-step-{{ $s['state'] }}" role="listitem"
                                 @if ($s['hint']) title="{{ $s['hint'] }}" @endif>
                                <span class="sp-step-dot">
                                    @if ($s['state'] === 'done')
                                        <x-filament::icon icon="heroicon-m-check" style="width:12px;height:12px;color:#fff;" />
                                    @elseif ($s['state'] === 'current')
                                        <span class="sp-step-pulse"></span>
                                    @else
                                        <x-filament::icon icon="heroicon-m-lock-closed" style="width:11px;height:11px;color:#94a3b8;" />
                                    @endif
                                </span>
                                <span class="sp-step-label">{{ $s['label'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Active step hint --}}
                    @php
                        $current = collect($stages)->firstWhere('state', 'current');
                    @endphp
                    @if ($current)
                        <div class="sp-step-hint">
                            <x-filament::icon icon="heroicon-m-information-circle" style="width:14px;height:14px;" />
                            <span>Next: <b>{{ $current['label'] }}</b> — {{ $current['hint'] }}</span>
                        </div>
                    @else
                        <div class="sp-step-hint sp-step-hint-done">
                            <x-filament::icon icon="heroicon-m-sparkles" style="width:14px;height:14px;" />
                            <span><b>Fully activated.</b> Student onboarding complete.</span>
                        </div>
                    @endif

                    {{-- Stage actions row — only render buttons whose stage exists for this track --}}
                    @php
                        $stageActionMap = [
                            'interview'  => 'recordInterviewAction',
                            'donation'   => 'confirmDonationAction',
                            'devfee'     => 'confirmDevFeeAction',
                            'books'      => 'recordBooksAction',
                            'class'      => 'assignClassAction',
                            'attendance' => 'markAttendanceAction',
                            'student_id' => 'issueStudentIdAction',
                            'eca'        => 'ecaEbooksAction',
                            'tuition'    => 'confirmTuitionAction',
                        ];
                    @endphp
                    <div class="sp-step-actions">
                        @foreach ($stages as $key => $s)
                            @php $method = $stageActionMap[$key] ?? null; @endphp
                            @if ($method)
                                <span @class(['sp-step-btn-wrap', 'is-disabled' => $s['state'] !== 'current'])>
                                    {{ ($this->{$method})(['id' => $a->id]) }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="sp-card" style="text-align:center;padding:3rem 1.5rem;">
                <x-filament::icon icon="heroicon-o-inbox" style="width:48px;height:48px;color:#94a3b8;margin:0 auto;" />
                <div style="font-weight:700;color:#0f172a;margin-top:.85rem;">No students in this list.</div>
                <div style="font-size:.85rem;color:#64748b;margin-top:.25rem;">Try clearing filters or pick another funnel tab.</div>
            </div>
        @endforelse
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
