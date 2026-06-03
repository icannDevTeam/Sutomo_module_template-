@php
    /** @var array $eligibility */
    /** @var bool $showPick - whether to render Pick buttons (true on submit-leave + approve modal) */
    /** @var string|null $alpinePickTarget - Alpine variable name to set when Pick clicked */
    /** @var string|null $livewirePickMethod - Livewire method to call when Pick clicked (alt to Alpine) */
    $showPick           = $showPick           ?? true;
    $alpinePickTarget   = $alpinePickTarget   ?? null;     // e.g. 'picked'
    $livewirePickMethod = $livewirePickMethod ?? null;     // e.g. 'pickSubstitute'
    $demand             = $eligibility['demand']      ?? [];
    $rows               = $eligibility['rows']        ?? [];
    $totalSlots         = $eligibility['total_slots'] ?? 0;
    $daysCount          = $eligibility['days_count']  ?? 0;
@endphp

<style>
    .seg { font-size: 12px; color: #1f2937; }
    .seg__head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
    .seg__title { font-size: 13px; font-weight: 700; color: #0f172a; }
    .seg__subtitle { font-size: 11px; color: #64748b; }

    .seg__legend { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
    .seg__legend-item { display: inline-flex; align-items: center; gap: 4px; font-size: 10px; color: #475569; }
    .seg__legend-swatch { width: 10px; height: 10px; border-radius: 3px; border: 1px solid rgba(0,0,0,.06); }

    .seg__demand {
        background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;
        padding: 8px 10px; margin-bottom: 10px;
    }
    .seg__demand-table { width: 100%; border-collapse: collapse; font-size: 11px; }
    .seg__demand-table th { text-align: left; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 9px; letter-spacing: .04em; padding: 4px 6px; }
    .seg__demand-table td { padding: 3px 6px; border-top: 1px solid #e2e8f0; color: #0f172a; }
    .seg__demand-empty { color: #94a3b8; font-style: italic; padding: 8px; font-size: 11px; }

    .seg__matrix-wrap { overflow-x: auto; }
    .seg__matrix {
        border-collapse: collapse; width: 100%; min-width: 480px;
        font-size: 11px;
    }
    .seg__matrix th, .seg__matrix td {
        border: 1px solid #e2e8f0; padding: 4px 6px; text-align: center; vertical-align: middle;
    }
    .seg__matrix thead th {
        background: #f8fafc; color: #475569; font-weight: 600; font-size: 10px;
        white-space: nowrap;
    }
    .seg__matrix thead .seg__col-cand { text-align: left; min-width: 160px; }
    .seg__matrix thead .seg__col-free { min-width: 56px; }
    .seg__matrix thead .seg__col-pick { min-width: 80px; }
    .seg__matrix tbody .seg__cand-cell { text-align: left; padding: 6px 8px; }
    .seg__matrix tbody .seg__cand-name { font-weight: 600; color: #0f172a; font-size: 11px; }
    .seg__matrix tbody .seg__cand-meta { color: #64748b; font-size: 10px; margin-top: 1px; }
    .seg__matrix tbody .seg__cand-tier { display: inline-block; background:#eef2ff; color:#4338ca; font-size:9px; padding:1px 6px; border-radius:999px; margin-top:2px; font-weight:600; }

    .seg__cell { font-weight: 700; font-size: 11px; cursor: default; }
    .seg__cell--free       { background: #dcfce7; color: #166534; }
    .seg__cell--teaching   { background: #fee2e2; color: #991b1b; }
    .seg__cell--duty       { background: #fef3c7; color: #92400e; }
    .seg__cell--off        { background: #f1f5f9; color: #94a3b8; }
    .seg__cell--unavailable{ background: #fafafa; color: #cbd5e1; }

    .seg__row--blocked .seg__cand-name { color: #94a3b8; text-decoration: line-through; }
    .seg__row--blocked { opacity: .65; }
    .seg__row--recommended { background: #f0fdf4; }

    .seg__free-pill {
        display: inline-block; padding: 3px 8px; border-radius: 999px; font-weight: 700;
        font-size: 11px; min-width: 36px;
    }
    .seg__free-pill--good { background: #dcfce7; color: #166534; }
    .seg__free-pill--mid  { background: #fef3c7; color: #92400e; }
    .seg__free-pill--bad  { background: #fee2e2; color: #991b1b; }

    .seg__btn {
        padding: 4px 10px; border-radius: 6px; border: 1px solid #6366f1;
        background: #fff; color: #6366f1; font-weight: 600; font-size: 10px;
        cursor: pointer; white-space: nowrap;
    }
    .seg__btn:hover { background: #eef2ff; }
    .seg__btn--selected { background: #10b981; border-color: #10b981; color: #fff; }
    .seg__btn--disabled { background:#f1f5f9; border-color:#e2e8f0; color:#94a3b8; cursor:not-allowed; }
    .seg__btn--disabled:hover { background:#f1f5f9; }

    .seg__block-reason { color:#b91c1c; font-size:10px; margin-top:2px; }

    .seg__empty {
        background:#fffbeb; border:1px solid #fde68a; border-radius:8px;
        padding:10px 12px; color:#78350f; font-size:12px;
    }
</style>

<div class="seg">
    <div class="seg__head">
        <div>
            <div class="seg__title">Eligibility timetable</div>
            <div class="seg__subtitle">
                @if($totalSlots > 0)
                    {{ $totalSlots }} lesson{{ $totalSlots === 1 ? '' : 's' }} need cover across {{ $daysCount }} day{{ $daysCount === 1 ? '' : 's' }}
                @else
                    No lessons in this leave window
                @endif
            </div>
        </div>

        @if($totalSlots > 0)
            <div class="seg__legend">
                <span class="seg__legend-item"><span class="seg__legend-swatch" style="background:#dcfce7;"></span> Free</span>
                <span class="seg__legend-item"><span class="seg__legend-swatch" style="background:#fee2e2;"></span> Teaching</span>
                <span class="seg__legend-item"><span class="seg__legend-swatch" style="background:#fef3c7;"></span> Duty</span>
                <span class="seg__legend-item"><span class="seg__legend-swatch" style="background:#f1f5f9;"></span> Off-day</span>
                <span class="seg__legend-item"><span class="seg__legend-swatch" style="background:#fafafa; border-color:#e2e8f0;"></span> Blocked</span>
            </div>
        @endif
    </div>

    @if($totalSlots === 0)
        <div class="seg__empty">
            The on-leave teacher has no scheduled lessons during this window (e.g. weekend-only leave or unpublished timetable). Substitute selection can proceed without the per-period matrix.
        </div>
    @else
        {{-- Section A: demand summary --}}
        <div class="seg__demand">
            <table class="seg__demand-table">
                <thead>
                    <tr>
                        <th>Date</th><th>Day</th><th>Period</th><th>Class</th><th>Subject</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($demand as $d)
                        <tr>
                            <td>{{ $d['date_label'] }}</td>
                            <td>{{ $d['day_label'] }}</td>
                            <td><strong>{{ $d['period'] }}</strong></td>
                            <td>{{ $d['class_code'] }}</td>
                            <td>{{ $d['subject'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Section B: heatmap matrix --}}
        @if(empty($rows))
            <div class="seg__empty">No candidates available for this window.</div>
        @else
            <div class="seg__matrix-wrap">
                <table class="seg__matrix">
                    <thead>
                        <tr>
                            <th class="seg__col-cand">Substitute</th>
                            @foreach($demand as $d)
                                <th title="{{ $d['date_label'] }} · {{ $d['day_label'] }} · {{ $d['class_code'] }} · {{ $d['subject'] }}">
                                    {{ $d['day_label'] }}<br>
                                    <span style="font-weight:400; color:#94a3b8;">{{ $d['period'] }}</span>
                                </th>
                            @endforeach
                            <th class="seg__col-free">Free</th>
                            @if($showPick)
                                <th class="seg__col-pick">Pick</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $r)
                            @php
                                $freePill = $r['free_pct'] >= 80 ? 'good' : ($r['free_pct'] >= 50 ? 'mid' : 'bad');
                                $rowClasses = ['seg__matrix-row'];
                                if ($r['is_blocked']) $rowClasses[] = 'seg__row--blocked';
                                if (! empty($r['is_recommended']) && $r['free_count'] === $r['total']) $rowClasses[] = 'seg__row--recommended';
                            @endphp
                            <tr class="{{ implode(' ', $rowClasses) }}"
                                @if($alpinePickTarget && $showPick && ! $r['is_blocked'])
                                    :class="{{ $alpinePickTarget }} == {{ $r['teacher_id'] }} ? 'seg__row--picked' : ''"
                                @endif
                            >
                                <td class="seg__cand-cell">
                                    <div class="seg__cand-name">{{ $r['teacher_name'] }}</div>
                                    <div class="seg__cand-meta">
                                        {{ $r['teacher_subject'] ?: '—' }} · {{ $r['teacher_campus'] ?: '—' }}
                                    </div>
                                    @if($r['tier_label'])
                                        <span class="seg__cand-tier">{{ $r['tier_label'] }}</span>
                                    @endif
                                    @if($r['is_blocked'] && $r['block_reason'])
                                        <div class="seg__block-reason">{{ $r['block_reason'] }}</div>
                                    @endif
                                </td>

                                @foreach($r['cells'] as $cell)
                                    <td class="seg__cell seg__cell--{{ $cell['state'] }}"
                                        title="{{ $cell['detail'] ?? ucfirst($cell['state']) }}">
                                        @switch($cell['state'])
                                            @case('free')        ✓ @break
                                            @case('teaching')    ✕ @break
                                            @case('duty')        D @break
                                            @case('off')         · @break
                                            @case('unavailable') — @break
                                        @endswitch
                                    </td>
                                @endforeach

                                <td>
                                    <span class="seg__free-pill seg__free-pill--{{ $freePill }}">
                                        {{ $r['free_count'] }}/{{ $r['total'] }}
                                    </span>
                                </td>

                                @if($showPick)
                                    <td>
                                        @if($r['is_blocked'])
                                            <button type="button" class="seg__btn seg__btn--disabled" disabled
                                                title="{{ $r['block_reason'] }}">Blocked</button>
                                        @elseif($alpinePickTarget)
                                            <button type="button"
                                                    class="seg__btn"
                                                    :class="{{ $alpinePickTarget }} == {{ $r['teacher_id'] }} ? 'seg__btn--selected' : ''"
                                                    @click="{{ $alpinePickTarget }} = {{ $r['teacher_id'] }}"
                                                    x-text="{{ $alpinePickTarget }} == {{ $r['teacher_id'] }} ? '✓ Picked' : 'Pick'">
                                                Pick
                                            </button>
                                        @elseif($livewirePickMethod)
                                            <button type="button" class="seg__btn"
                                                    wire:click="{{ $livewirePickMethod }}({{ $r['teacher_id'] }})">
                                                Pick
                                            </button>
                                        @else
                                            <span style="color:#94a3b8; font-size:10px;">—</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif
</div>
