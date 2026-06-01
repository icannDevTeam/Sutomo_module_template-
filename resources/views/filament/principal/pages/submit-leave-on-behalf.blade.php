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
        .slob-empty { color:#64748b; font-size:13px; text-align:center; padding:40px 20px; }
    </style>

    <div class="slob-grid">
        {{-- LEFT: form + quotas --}}
        <div style="display:flex; flex-direction:column; gap:16px;">
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
                    @foreach($types as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>

                <label class="slob-label" style="margin-top:12px;">Reason (optional)</label>
                <textarea class="slob-textarea" wire:model="reason" placeholder="e.g., Family emergency"></textarea>

                <label style="display:flex; align-items:center; gap:8px; margin-top:12px; font-size:13px;">
                    <input type="checkbox" wire:model="autoSearchEnabled">
                    Enable auto substitute search
                </label>
                <div style="font-size:11px; color:#64748b; margin-top:4px; padding-left:24px;">
                    Emails eligible internal teachers to express interest. You still pick the final substitute.
                </div>
            </div>

            @if($teacher && !empty($quotas))
                <div class="slob-card">
                    <h3>Quota · {{ $teacher->name }} ({{ $month->year }})</h3>
                    <div class="slob-quota">
                        @foreach($quotas as $q)
                            <div class="slob-quota-row">
                                <div class="slob-quota-row__head">
                                    <span class="slob-quota-row__name">{{ $q['label'] }}</span>
                                    <span class="slob-quota-row__nums">
                                        {{ $q['used'] }} / {{ $q['limit'] }} ·
                                        <strong style="color:{{ $q['color'] === 'danger' ? '#ef4444' : ($q['color'] === 'warning' ? '#f59e0b' : '#10b981') }}">{{ $q['remaining'] }} left</strong>
                                    </span>
                                </div>
                                <div class="slob-bar slob-bar--{{ $q['color'] }}">
                                    <div style="width:{{ $q['pct'] }}%;"></div>
                                </div>
                            </div>
                        @endforeach
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
                            {{ Carbon::parse($startsAtDate)->format('D, d M Y') }}
                            →
                            {{ Carbon::parse($endsAtDate)->format('D, d M Y') }}
                        </div>
                        <button type="button" wire:click="clearSelection" style="margin-top:8px; background:transparent; border:none; color:#6366f1; cursor:pointer; font-size:12px; padding:0;">Clear selection</button>
                    </div>
                    <button class="slob-submit" wire:click="submit" wire:loading.attr="disabled">
                        Submit leave
                    </button>
                </div>
            @endif
        </div>

        {{-- RIGHT: calendar --}}
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
                                             $cell->between(Carbon::parse($startsAtDate), Carbon::parse($endsAtDate));
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
    </div>
</x-filament-panels::page>
