<x-filament-panels::page>
    <style>
        .obs-cell:hover { transform: scale(1.05); filter: brightness(0.96); }
        .obs-cell:focus-visible { outline: 2px solid #4338ca; outline-offset: 2px; }
        .obs-student-cell { min-width: 240px; max-width: 280px; }
        .obs-student-cell > div:first-child {
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
    </style>
    {{-- ============================================================
         PERIOD SWITCHER + ACTIONS
         ============================================================ --}}
    <div class="sp-card" style="padding:1rem;">
        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:1rem;">
            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:.5rem;">
                <span style="font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">Enrollment Period</span>
                @foreach ($periods as $p)
                    <button type="button" wire:click="setActivePeriod({{ $p->id }})"
                        class="sp-pill"
                        style="cursor:pointer;border:1px solid {{ $period && $period->id === $p->id ? '#4338ca' : '#e5e7eb' }};
                               background:{{ $period && $period->id === $p->id ? '#eef2ff' : '#fff' }};
                               color:{{ $period && $period->id === $p->id ? '#3730a3' : '#374151' }};
                               padding:.375rem .625rem;font-weight:600;font-size:.75rem;">
                        {{ $p->name }}
                    </button>
                @endforeach
            </div>
            <div style="display:flex;gap:.5rem;align-items:center;">
                {{ ($this->markAllPresentTodayAction) }}
            </div>
        </div>

        {{-- Period observation window summary --}}
        @if ($period)
            <div style="margin-top:.75rem;display:flex;flex-wrap:wrap;gap:.5rem;font-size:.8125rem;color:#374151;">
                @if (! empty($dates))
                    <span class="sp-pill" style="background:#eef2ff;color:#3730a3;padding:.25rem .625rem;">
                        Window: {{ \Illuminate\Support\Carbon::parse($dates[0])->translatedFormat('d M Y') }}
                        &rarr; {{ \Illuminate\Support\Carbon::parse(end($dates))->translatedFormat('d M Y') }}
                        ({{ count($dates) }} days)
                    </span>
                @else
                    <span class="sp-pill" style="background:#fef3c7;color:#92400e;padding:.25rem .625rem;">
                        No observation window set. Open Enrollment, "Set Observation Window".
                    </span>
                @endif
                <span class="sp-pill" style="background:#f3f4f6;color:#374151;padding:.25rem .625rem;">
                    Cohort: {{ $apps->count() }} student(s){{ $classFilter ? ' · class ' . $classFilter : '' }}
                </span>
            </div>
        @endif
    </div>

    {{-- ============================================================
         FILTERS
         ============================================================ --}}
    <div class="sp-card sp-filters" style="margin-top:1rem;">
        <div class="sp-filter">
            <label>Search</label>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Name or code…" />
        </div>
        <div class="sp-filter">
            <label>Class</label>
            <select wire:model.live="classFilter">
                <option value="">All</option>
                @foreach ($classes as $c)
                    <option value="{{ $c }}">{{ $c }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- ============================================================
         ATTENDANCE TABLE
         ============================================================ --}}
    @if (empty($dates))
        <div class="sp-card" style="margin-top:1rem;padding:2rem;text-align:center;color:#6b7280;">
            <div style="font-size:2.5rem;margin-bottom:.5rem;">📅</div>
            <p style="font-weight:600;color:#374151;">No observation window scheduled yet.</p>
            <p style="font-size:.875rem;">Go to <strong>Open Enrollment</strong> and click <strong>Set Observation Window</strong> to choose Day 1.</p>
        </div>
    @elseif ($apps->isEmpty())
        <div class="sp-card" style="margin-top:1rem;padding:2rem;text-align:center;color:#6b7280;">
            <p>No students in this cohort match the current filters.</p>
        </div>
    @else
    <div class="sp-card" style="margin-top:1rem;padding:0;overflow:hidden;">
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:separate;border-spacing:0;font-size:.8125rem;">
                <thead>
                    <tr style="background:#f9fafb;color:#374151;text-align:left;">
                        <th style="padding:.625rem .75rem;border-bottom:1px solid #e5e7eb;position:sticky;left:0;background:#f9fafb;z-index:2;min-width:220px;">Student</th>
                        <th style="padding:.625rem .75rem;border-bottom:1px solid #e5e7eb;">Class</th>
                        @foreach ($dates as $i => $d)
                            @php $c = \Illuminate\Support\Carbon::parse($d); @endphp
                            <th style="padding:.5rem .25rem;border-bottom:1px solid #e5e7eb;text-align:center;min-width:84px;">
                                <div style="font-size:.6875rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Day {{ $i + 1 }}</div>
                                <div style="font-weight:700;color:#111827;">{{ $c->translatedFormat('D') }}</div>
                                <div style="font-size:.6875rem;color:#6b7280;">{{ $c->translatedFormat('d M') }}</div>
                            </th>
                        @endforeach
                        <th style="padding:.625rem .75rem;border-bottom:1px solid #e5e7eb;text-align:center;">Present</th>
                        <th style="padding:.625rem .75rem;border-bottom:1px solid #e5e7eb;text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($apps as $a)
                        @php
                            $days = data_get($a->meta, 'observation.days', []);
                            $present = collect($days)->filter(fn ($d) => ($d['present'] ?? null) === true)->count();
                            $absent  = collect($days)->filter(fn ($d) => ($d['present'] ?? null) === false)->count();
                            $complete = $present >= 5;
                            $warn = $absent >= 3;
                        @endphp
                        <tr style="border-bottom:1px solid #f3f4f6;">
                            <td class="obs-student-cell" style="padding:.625rem .75rem;position:sticky;left:0;background:#fff;z-index:1;">
                                <div style="font-weight:600;color:#111827;" title="{{ $a->name }}">{{ $a->name }}</div>
                                <div style="font-size:.6875rem;color:#6b7280;font-family:ui-monospace,monospace;">{{ $a->code }}</div>
                            </td>
                            <td style="padding:.625rem .75rem;color:#374151;">
                                <div>{{ $a->class_label }}</div>
                                <div style="font-size:.6875rem;color:#9ca3af;">{{ \App\Filament\Principal\Pages\OnboardingAttendance::unitLabel($a->campus) }}</div>
                            </td>
                            @foreach ($dates as $d)
                                @php
                                    $row = $days[$d] ?? null;
                                    $p = $row['present'] ?? null;
                                    $by = $row['by'] ?? null;
                                    $bg = $p === true ? '#d1fae5' : ($p === false ? '#fee2e2' : '#f3f4f6');
                                    $fg = $p === true ? '#065f46' : ($p === false ? '#991b1b' : '#6b7280');
                                    $borderColor = $p === true ? '#10b981' : ($p === false ? '#ef4444' : '#d1d5db');
                                    $icon = $p === true ? '✓' : ($p === false ? '✗' : '—');
                                    $title = $p === null ? 'Pending — click to mark present' : ($by ?: 'Marked');
                                @endphp
                                <td style="padding:.25rem;text-align:center;">
                                    <button type="button"
                                        wire:click="cycleCell({{ $a->id }}, '{{ $d }}')"
                                        title="{{ $title }} (click to cycle: present, absent, pending)"
                                        class="obs-cell"
                                        style="cursor:pointer;border:1px solid {{ $borderColor }};
                                               background:{{ $bg }};color:{{ $fg }};border-radius:6px;
                                               width:42px;height:36px;font-size:1.125rem;font-weight:700;line-height:1;
                                               transition:transform .12s ease, filter .12s ease;">
                                        {{ $icon }}
                                    </button>
                                    @if ($by)
                                        <div style="font-size:.6875rem;color:#6b7280;margin-top:.125rem;max-width:84px;margin-left:auto;margin-right:auto;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;line-height:1.2;">
                                            {{ str_contains($by, 'Principal') ? '⚡ override' : $by }}
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                            <td style="padding:.625rem .75rem;text-align:center;font-weight:700;color:{{ $present >= 5 ? '#065f46' : '#374151' }};">
                                {{ $present }}/{{ count($dates) }}
                            </td>
                            <td style="padding:.625rem .75rem;text-align:center;">
                                @if ($complete)
                                    <span class="sp-pill" style="background:#d1fae5;color:#065f46;padding:.25rem .5rem;font-weight:600;">✓ Complete</span>
                                @elseif ($warn)
                                    <span class="sp-pill" style="background:#fef3c7;color:#92400e;padding:.25rem .5rem;font-weight:600;">Check-up</span>
                                @else
                                    <span class="sp-pill" style="background:#eef2ff;color:#3730a3;padding:.25rem .5rem;font-weight:600;">In progress</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#f9fafb;font-size:.75rem;color:#374151;">
                        <td colspan="2" style="padding:.625rem .75rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">
                            Daily Present @if($classFilter || $search)<span style="text-transform:none;letter-spacing:0;font-weight:500;color:#9ca3af;">(filtered view)</span>@endif
                        </td>
                        @foreach ($dates as $d)
                            @php
                                $t = $colTotals[$d];
                                $total = $apps->count();
                                $pct = $total ? round($t['present'] / $total * 100) : 0;
                            @endphp
                            <td style="padding:.5rem .25rem;text-align:center;">
                                <div style="font-weight:700;color:#065f46;">{{ $t['present'] }}/{{ $total }}</div>
                                <div style="font-size:.625rem;color:#9ca3af;">{{ $pct }}%</div>
                            </td>
                        @endforeach
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div style="padding:.625rem .875rem;border-top:1px solid #f3f4f6;background:#fafafa;color:#6b7280;font-size:.75rem;display:flex;flex-wrap:wrap;gap:1rem;">
            <span><strong style="color:#065f46;">✓</strong> Present</span>
            <span><strong style="color:#991b1b;">✗</strong> Absent</span>
            <span><strong style="color:#9ca3af;">—</strong> Pending</span>
            <span style="margin-left:auto;">Click any cell to cycle <em>present, absent, pending</em>. Principal overrides are logged.</span>
        </div>
    </div>
    @endif
</x-filament-panels::page>
