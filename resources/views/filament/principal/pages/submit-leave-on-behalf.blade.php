<x-filament-panels::page>
    @php
        $startsAtDate = $this->startsAt ? \Illuminate\Support\Carbon::parse($this->startsAt)->toDateString() : null;
        $endsAtDate   = $this->endsAt ? \Illuminate\Support\Carbon::parse($this->endsAt)->toDateString() : null;
    @endphp

    <style>
        .slob-grid { display:grid; grid-template-columns: 320px 1fr; gap:20px; }
        @media (max-width: 960px) { .slob-grid { grid-template-columns:1fr; } }
        .slob-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px; }
        .slob-card h3 { font-size:13px; font-weight:600; color:#0f172a; margin:0 0 12px; text-transform:uppercase; letter-spacing:.05em; }
        .slob-select, .slob-input, .slob-textarea {
            width:100%; padding:8px 10px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; background:#fff;
            color:#0f172a; line-height:1.4; box-sizing:border-box;
        }
        .slob-select {
            -webkit-appearance:none; -moz-appearance:none; appearance:none;
            padding-right:32px;
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 20 20' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 8 10 12 14 8'/></svg>");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 12px 12px;
        }
        .slob-select:focus, .slob-input:focus, .slob-textarea:focus {
            outline:none; border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.15);
        }
        .slob-textarea { min-height:60px; resize:vertical; }
        .slob-label { font-size:12px; font-weight:500; color:#475569; margin-bottom:4px; display:block; }
        .slob-quota { display:flex; flex-direction:column; gap:10px; margin-top:14px; }
        .slob-quota-row { font-size:12px; }
        .slob-quota-row__head { display:flex; justify-content:space-between; margin-bottom:4px; }
        .slob-quota-row__name { font-weight:600; color:#0f172a; }
        .slob-quota-row__nums { color:#64748b; }
        .slob-bar { height:6px; background:#f1f5f9; border-radius:999px; overflow:hidden; }
        .slob-bar > div { height:100%; border-radius:999px; transition:width .2s; }
        .slob-bar--success > div { background:#10b981; }
        .slob-bar--warning > div { background:#f59e0b; }
        .slob-bar--danger  > div { background:#ef4444; }

        .slob-cal__head { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
        .slob-cal__title { font-size:16px; font-weight:700; color:#0f172a; }
        .slob-cal__nav { display:flex; gap:6px; }
        .slob-cal__navbtn { padding:4px 10px; border:1px solid #d1d5db; border-radius:6px; background:#fff; cursor:pointer; font-size:13px; }
        .slob-cal__navbtn:hover { background:#f8fafc; }
        .slob-cal__weekdays, .slob-cal__grid { display:grid; grid-template-columns:repeat(7,1fr); gap:4px; }
        .slob-cal__weekdays { font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; margin-bottom:6px; }
        .slob-cal__weekdays > div { text-align:center; padding:6px 0; }
        .slob-cell {
            aspect-ratio:1.1/1; border:1px solid #e5e7eb; border-radius:8px; padding:6px;
            display:flex; flex-direction:column; align-items:flex-start; justify-content:space-between;
            background:#fff; cursor:pointer; font-size:13px; transition:all .12s;
            position:relative;
        }
        .slob-cell:hover { border-color:#6366f1; background:#eef2ff; }
        .slob-cell--empty { background:transparent; border:none; cursor:default; }
        .slob-cell--weekend { background:#f8fafc; color:#94a3b8; }
        .slob-cell--today { border-color:#6366f1; font-weight:700; }
        .slob-cell--selected { background:#dbeafe; border-color:#3b82f6; color:#1e3a8a; font-weight:600; }
        .slob-cell--in-range { background:#eff6ff; border-color:#93c5fd; }
        .slob-cell--leave { background:#fef3c7; border-color:#fbbf24; }
        .slob-cell--disabled { cursor:not-allowed; opacity:.5; }
        .slob-cell__day { font-weight:600; }
        .slob-cell__tag { font-size:10px; color:#92400e; }

        .slob-summary { background:#f8fafc; border-radius:10px; padding:14px; font-size:13px; }
        .slob-summary strong { font-size:18px; color:#0f172a; }
        .slob-submit {
            margin-top:12px; padding:10px 16px; background:#6366f1; color:#fff; border:none; border-radius:8px;
            font-weight:600; cursor:pointer; width:100%;
        }
        .slob-submit:hover { background:#4f46e5; }
        .slob-submit:disabled { opacity:.5; cursor:not-allowed; }
        @keyframes slob-spin { to { transform: rotate(360deg); } }
        .slob-empty { color:#64748b; font-size:13px; text-align:center; padding:40px 20px; }
        .slob-badge {
            display:inline-block; padding:2px 7px; border-radius:999px;
            font-size:10px; font-weight:600;
        }
        .slob-badge--success { background:#dcfce7; color:#166534; }
        .slob-badge--warning { background:#fef3c7; color:#92400e; }
        .slob-badge--danger  { background:#fee2e2; color:#991b1b; }
        .slob-badge--info    { background:#dbeafe; color:#1e40af; }
        .slob-badge--gray    { background:#f1f5f9; color:#475569; }
    </style>

    <x-principal.module-hero
        title="Submit Leave On Behalf"
        description="File leave requests for teachers, check quota impact, and assign or broadcast substitute coverage from one workspace."
        icon="heroicon-o-calendar-days"
        tone="emerald"
    />

    <div style="height:.9rem;"></div>

    <div class="slob-grid">
        {{-- LEFT: form + quotas --}}
        <div style="display:flex; flex-direction:column; gap:16px;">
            @if($lastSubmitted)
                <div class="slob-card" style="border-color:#10b981; background:#ecfdf5;">
                    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px;">
                        <div>
                            <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#065f46;">
                                {{ $lastSubmitted['status'] === 'approved' ? '✓ Leave approved' : '✓ Leave submitted' }}
                            </div>
                            <div style="margin-top:4px; font-weight:600; color:#064e3b; font-size:14px;">
                                {{ $lastSubmitted['teacher'] }} · {{ $lastSubmitted['type'] }}
                            </div>
                            <div style="margin-top:2px; color:#065f46; font-size:12px;">
                                {{ $lastSubmitted['dates'] }} · {{ $lastSubmitted['message'] }}
                            </div>
                            @if($lastSubmitted['substitute'])
                                <div style="margin-top:2px; color:#065f46; font-size:11px;">
                                    Substitute: <strong>{{ $lastSubmitted['substitute'] }}</strong>{{ $lastSubmitted['duty'] ? ' · duty hand-off created' : '' }}
                                </div>
                            @endif
                            <div style="margin-top:8px; display:flex; gap:8px;">
                                <a href="{{ url('/principal/teacher-leaves') }}"
                                   style="padding:5px 10px; border-radius:6px; background:#10b981; color:#fff; font-size:11px; font-weight:600; text-decoration:none;">
                                    View Teacher Leaves
                                </a>
                                <button type="button" wire:click="dismissLastSubmitted"
                                    style="padding:5px 10px; border-radius:6px; border:1px solid #6ee7b7; background:#fff; color:#065f46; font-size:11px; font-weight:600; cursor:pointer;">
                                    Submit another
                                </button>
                            </div>
                        </div>
                        <button type="button" wire:click="dismissLastSubmitted"
                            style="background:transparent; border:none; color:#065f46; cursor:pointer; font-size:18px; line-height:1; padding:0;">×</button>
                    </div>
                </div>
            @endif

            <div class="slob-card">
                <h3>Teacher & leave details</h3>
                <label class="slob-label">Teacher</label>
                <select class="slob-select" wire:model.live="teacherId">
                    <option value="">— select a teacher —</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}">{{ $t->name }} · {{ $t->subject ?? 'N/A' }}</option>
                    @endforeach
                </select>

                <label class="slob-label" style="margin-top:12px;">Leave type</label>
                <select class="slob-select" wire:model.live="type">
                    @foreach($leaveTypes as $lt)
                        <option value="{{ $lt->key }}">
                            {{ $lt->label }}{{ $lt->affects_quota ? '' : ' (no quota)' }}{{ $lt->max_days ? ' · max '.$lt->max_days.' days' : '' }}
                        </option>
                    @endforeach
                </select>
                @php
                    $selectedType = $leaveTypes->firstWhere('key', $type);
                @endphp
                @if($selectedType && ! $selectedType->affects_quota)
                    <div style="margin-top:6px; padding:6px 10px; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:6px; font-size:11px; color:#047857;">
                        ✓ This type does <strong>not</strong> count against the teacher's quota.
                    </div>
                @endif
                @if($selectedType && ! $selectedType->requires_substitute)
                    <div style="margin-top:6px; padding:6px 10px; background:#f1f5f9; border:1px solid #cbd5e1; border-radius:6px; font-size:11px; color:#334155;">
                        ⓘ No substitute required for this type — leave will be approved on submit.
                    </div>
                @endif
                @if($selectedType && $selectedType->max_days)
                    <div style="margin-top:6px; padding:6px 10px; background:#fffbeb; border:1px solid #fde68a; border-radius:6px; font-size:11px; color:#92400e;">
                        ⓘ Max allowed for this type: <strong>{{ $selectedType->max_days }} day{{ $selectedType->max_days > 1 ? 's' : '' }}</strong>.
                    </div>
                @endif

                <label class="slob-label" style="margin-top:12px;">Date range</label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <div>
                        <input type="date" class="slob-input" wire:model.live="startsAt" placeholder="From">
                        <div style="font-size:10px; color:#94a3b8; margin-top:2px;">From</div>
                    </div>
                    <div>
                        <input type="date" class="slob-input" wire:model.live="endsAt" placeholder="To">
                        <div style="font-size:10px; color:#94a3b8; margin-top:2px;">To</div>
                    </div>
                </div>
                <div style="font-size:11px; color:#64748b; margin-top:4px;">Tip: you can also click two dates on the calendar.</div>

                <label class="slob-label" style="margin-top:12px;">Reason (optional)</label>
                <textarea class="slob-textarea" wire:model="reason" placeholder="e.g., Family emergency"></textarea>

                @if($requiresSubstitute)
                    <label style="display:flex; align-items:center; gap:8px; margin-top:12px; font-size:13px;">
                        <input type="checkbox" wire:model="autoSearchEnabled">
                        Enable auto substitute search
                    </label>
                    <div style="font-size:11px; color:#64748b; margin-top:4px; padding-left:24px;">
                        Emails eligible internal teachers to express interest. You still pick the final substitute.
                    </div>
                @endif
            </div>

            @if($teacher && !empty($quotas))
                @php $q = $quotas[0]; @endphp
                <div class="slob-card">
                    <h3>Leave quota · {{ $month->year }}</h3>
                    <div style="display:flex; align-items:baseline; gap:8px; margin-bottom:6px;">
                        <div style="font-size:28px; font-weight:700; color:#0f172a;">{{ $q['remaining'] }}</div>
                        <div style="font-size:13px; color:#64748b;">days remaining of {{ $q['limit'] }}</div>
                    </div>
                    <div class="slob-bar slob-bar--{{ $q['color'] }}">
                        <div style="width:{{ $q['pct'] }}%;"></div>
                    </div>
                    <div style="font-size:11px; color:#64748b; margin-top:6px;">
                        {{ $q['used'] }} day(s) used · only quota-affecting leave types are counted.
                    </div>
                </div>
            @endif

            @if($teacher && $startsAtDate && $endsAtDate)
                <div class="slob-card">
                    <h3>Selection</h3>
                    <div class="slob-summary">
                        <div style="margin-bottom:6px;">
                            <strong>{{ $workingDays }}</strong> working day{{ $workingDays === 1 ? '' : 's' }}
                        </div>
                        <div style="color:#475569;">
                            {{ \Illuminate\Support\Carbon::parse($startsAtDate)->format('D, d M Y') }}
                            →
                            {{ \Illuminate\Support\Carbon::parse($endsAtDate)->format('D, d M Y') }}
                        </div>
                        <button type="button" wire:click="clearSelection" style="margin-top:8px; background:transparent; border:none; color:#6366f1; cursor:pointer; font-size:12px; padding:0;">Clear selection</button>
                    </div>

                    {{-- Coverage decision summary --}}
                    @if(! $requiresSubstitute)
                        <div style="margin-top:10px; padding:10px 12px; background:#f1f5f9; border:1px solid #cbd5e1; border-radius:8px; font-size:12px; color:#334155;">
                            <div style="font-weight:600; color:#0f172a;">✓ Will be approved on submit</div>
                            <div style="margin-top:2px;">No substitute required for this leave type.</div>
                        </div>
                    @elseif($pickedSubstitute)
                        <div style="margin-top:10px; padding:10px 12px; background:#ecfdf5; border:1px solid #6ee7b7; border-radius:8px; font-size:12px; color:#065f46;">
                            <div style="font-weight:600; color:#064e3b;">✓ Will be approved on submit</div>
                            <div style="margin-top:2px;">Substitute: <strong>{{ $pickedSubstitute->name }}</strong></div>
                            <label style="display:flex; align-items:center; gap:6px; margin-top:8px; color:#064e3b;">
                                <input type="checkbox" wire:model.live="createDutyAssignment">
                                Create duty hand-off (status pending)
                            </label>
                        </div>
                    @elseif($autoSearchEnabled)
                        <div style="margin-top:10px; padding:10px 12px; background:#eff6ff; border:1px solid #93c5fd; border-radius:8px; font-size:12px; color:#1e40af;">
                            <div style="font-weight:600;">⚡ Will be filed pending</div>
                            <div style="margin-top:2px;">Auto-broadcast will email up to 15 eligible teachers; you finalise from the leave list.</div>
                        </div>
                    @else
                        <div style="margin-top:10px; padding:10px 12px; background:#fef2f2; border:1px solid #fca5a5; border-radius:8px; font-size:12px; color:#991b1b;">
                            <div style="font-weight:600;">⚠ Coverage path required</div>
                            <div style="margin-top:2px;">Pick a substitute from the suggestions panel, or enable auto substitute search.</div>
                        </div>
                    @endif

                    @if($lastSubmitted)
                        <div style="margin-top:12px; padding:10px 12px; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:8px; color:#065f46; font-size:12px; text-align:center;">
                            ✓ Submission saved. Click <strong>Submit another</strong> in the green banner above to file a new leave.
                        </div>
                    @else
                        <button type="button" class="slob-submit"
                                wire:click="submit" wire:loading.attr="disabled" wire:target="submit"
                                @disabled(! $canSubmit)
                                title="{{ $canSubmit ? 'Submit leave' : 'Pick a substitute or enable auto-broadcast first' }}">
                            <span wire:loading.remove wire:target="submit">
                                {{ ! $requiresSubstitute ? 'Submit & approve' : ($pickedSubstitute ? 'Submit & approve' : 'Submit leave') }}
                            </span>
                            <span wire:loading wire:target="submit" style="display:inline-flex; align-items:center; gap:6px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="animation:slob-spin 1s linear infinite;">
                                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" stroke-opacity=".25"/>
                                    <path d="M21 12a9 9 0 0 1-9 9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                </svg>
                                Submitting…
                            </span>
                        </button>
                    @endif
                </div>
            @endif
        </div>

        {{-- RIGHT: suggestions + calendar --}}
        <div style="display:flex; flex-direction:column; gap:16px;">
            @if($requiresSubstitute && ! empty($eligibility) && ($eligibility['total_slots'] ?? 0) > 0)
            {{-- Per-period eligibility matrix --}}
            <div class="slob-card">
                @include('filament.principal.teacher.eligibility-grid', [
                    'eligibility'        => $eligibility,
                    'showPick'           => true,
                    'livewirePickMethod' => 'pickSubstitute',
                ])
            </div>
            @endif

            @if($requiresSubstitute)
            {{-- Suggested substitutes panel --}}
            <div class="slob-card">
                <h3 style="display:flex; align-items:center; justify-content:space-between;">
                    <span>Suggested substitutes</span>
                    @if($pickedSubstitute)
                        <button type="button" wire:click="clearPick"
                            style="background:transparent; border:none; color:#6366f1; cursor:pointer; font-size:11px; text-transform:none; letter-spacing:0; font-weight:500;">
                            Clear pick
                        </button>
                    @endif
                </h3>

                @if(! $teacher || ! $startsAtDate || ! $endsAtDate)
                    <div class="slob-empty" style="padding:24px 12px;">Pick a teacher and date range to see ranked candidates.</div>
                @else
                    @php
                        $assignableCount = $suggestions->where('is_assignable', true)->count();
                        $grouped = $suggestions->groupBy('tier');
                    @endphp

                    @if($assignableCount === 0)
                        <div style="padding:10px 12px; background:#fffbeb; border:1px solid #fde68a; border-radius:6px; font-size:11px; color:#78350f; margin-bottom:10px;">
                            ⚠ 0 candidates currently free for this window. If you submit without a pick, auto-broadcast will still email a wider net.
                        </div>
                    @endif

                    @foreach([0, 1, 2, 3, 4, 5] as $tier)
                        @php $group = $grouped->get($tier, collect()); @endphp
                        @if($group->isNotEmpty())
                            <div style="margin-bottom:12px;">
                                <div style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#64748b; margin-bottom:6px;">
                                    {{ $tierLabels[$tier] ?? 'Tier '.$tier }} ({{ $group->count() }})
                                </div>
                                @foreach($group as $c)
                                    @php
                                        $t = $c['teacher'];
                                        $isPicked = $preferredSubstituteId === (int) $t->id;
                                        $rowStyle = 'display:grid; grid-template-columns:1fr auto; gap:8px; align-items:center; padding:8px 10px; border:1px solid #e5e7eb; border-radius:8px; margin-bottom:4px; transition:all .12s; '
                                            . ($isPicked ? 'background:#ecfdf5; border-color:#10b981; box-shadow:0 0 0 2px rgba(16,185,129,.18);' : 'background:#fff;')
                                            . (! $c['is_assignable'] ? 'opacity:.55;' : '');
                                    @endphp
                                    <div style="{{ $rowStyle }}">
                                        <div style="min-width:0;">
                                            <div style="font-weight:600; font-size:13px; color:#0f172a;">{{ $t->name }}</div>
                                            <div style="font-size:11px; color:#64748b;">{{ $t->subject ?: '—' }} · {{ $t->dept ?: '—' }} · {{ $t->campus ?: '—' }}</div>
                                            <div style="display:flex; gap:4px; flex-wrap:wrap; margin-top:4px;">
                                                <span class="slob-badge slob-badge--{{ $c['availability_color'] }}">{{ $c['availability_label'] ?? $c['availability'] }}</span>
                                                @if(($c['category'] ?? 'standard') !== 'standard')
                                                    <span class="slob-badge slob-badge--{{ $c['category_color'] }}">{{ $c['category_label'] }}</span>
                                                @endif
                                            </div>
                                            @if(! empty($c['conflict_reasons']))
                                                <div style="font-size:10px; color:#b91c1c; margin-top:3px;">{{ implode(' · ', $c['conflict_reasons']) }}</div>
                                            @endif
                                        </div>
                                        <div>
                                            @if($c['is_assignable'])
                                                <button type="button"
                                                        wire:click="pickSubstitute({{ $t->id }})"
                                                        style="padding:5px 10px; border-radius:6px; border:1px solid {{ $isPicked ? '#10b981' : '#6366f1' }}; background:{{ $isPicked ? '#10b981' : '#fff' }}; color:{{ $isPicked ? '#fff' : '#6366f1' }}; font-weight:600; font-size:11px; cursor:pointer; white-space:nowrap;">
                                                    {{ $isPicked ? '✓ Picked' : 'Pick' }}
                                                </button>
                                            @else
                                                <span style="font-size:11px; color:#94a3b8;">Unavailable</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
            @endif

            {{-- Calendar --}}
            <div class="slob-card">
            <div class="slob-cal__head">
                <div class="slob-cal__title">{{ $monthLabel }}</div>
                <div class="slob-cal__nav">
                    <button type="button" class="slob-cal__navbtn" wire:click="$set('monthKey', '{{ $prevMonth }}')">←</button>
                    <button type="button" class="slob-cal__navbtn" wire:click="$set('monthKey', '{{ now()->format('Y-m') }}')">Today</button>
                    <button type="button" class="slob-cal__navbtn" wire:click="$set('monthKey', '{{ $nextMonth }}')">→</button>
                </div>
            </div>

            @if(! $teacher)
                <div class="slob-empty">Select a teacher to view their calendar and quota.</div>
            @else
                <div class="slob-cal__weekdays">
                    <div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div><div>Sun</div>
                </div>
                <div class="slob-cal__grid">
                    @foreach($cells as $cell)
                        @if(! $cell)
                            <div class="slob-cell slob-cell--empty"></div>
                        @else
                            @php
                                $dateStr = $cell->toDateString();
                                $isWeekend = in_array($cell->dayOfWeek, [0, 6]); // 0=Sun, 6=Sat
                                $isToday = $cell->isToday();
                                $hasLeave = isset($leavesByDate[$dateStr]);
                                $isSelectedStart = $startsAtDate === $dateStr;
                                $isSelectedEnd = $endsAtDate === $dateStr;
                                $isInRange = $startsAtDate && $endsAtDate &&
                                             $cell->between(\Illuminate\Support\Carbon::parse($startsAtDate), \Illuminate\Support\Carbon::parse($endsAtDate));
                                $classes = ['slob-cell'];
                                if ($isWeekend) $classes[] = 'slob-cell--weekend';
                                if ($isToday) $classes[] = 'slob-cell--today';
                                if ($hasLeave) $classes[] = 'slob-cell--leave';
                                if ($isSelectedStart || $isSelectedEnd) $classes[] = 'slob-cell--selected';
                                elseif ($isInRange) $classes[] = 'slob-cell--in-range';
                            @endphp
                            <div class="{{ implode(' ', $classes) }}"
                                 wire:click="pickDate('{{ $dateStr }}')"
                                 title="{{ $cell->format('D, d M Y') }}{{ $hasLeave ? ' · existing leave' : '' }}">
                                <span class="slob-cell__day">{{ $cell->day }}</span>
                                @if($hasLeave)
                                    <span class="slob-cell__tag">on leave</span>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>

                <div style="margin-top:14px; display:flex; gap:14px; font-size:11px; color:#64748b; flex-wrap:wrap;">
                    <span style="display:flex; align-items:center; gap:4px;"><span style="width:12px; height:12px; background:#dbeafe; border:1px solid #3b82f6; border-radius:3px;"></span> Selected</span>
                    <span style="display:flex; align-items:center; gap:4px;"><span style="width:12px; height:12px; background:#fef3c7; border:1px solid #fbbf24; border-radius:3px;"></span> Existing leave</span>
                    <span style="display:flex; align-items:center; gap:4px;"><span style="width:12px; height:12px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:3px;"></span> Weekend</span>
                </div>
            @endif
        </div>
        </div>{{-- end right column wrapper --}}
    </div>
</x-filament-panels::page>
