@php
    /** @var \App\Models\TeacherLeave $leave */
    /** @var \Illuminate\Support\Collection $offers */
    /** @var \Illuminate\Support\Collection $candidates */
    /** @var array $eligibility */
    $days = $leave->starts_at && $leave->ends_at
        ? $leave->starts_at->diffInDays($leave->ends_at) + 1
        : null;
@endphp

<style>
    .rs-head {
        background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
        padding: 12px 14px; margin-bottom: 14px;
        display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 12px;
        font-size: 13px;
    }
    .rs-head .lbl { font-size: 10px; text-transform: uppercase; letter-spacing: .05em; color: #64748b; font-weight: 700; }
    .rs-head .val { font-weight: 600; color: #0f172a; font-size: 13px; margin-top: 2px; }
    .rs-head .meta { font-size: 11px; color: #64748b; margin-top: 1px; }
    .rs-respondents-note {
        margin-bottom: 10px; font-size: 11px; color: #475569;
        padding: 6px 10px; background:#eef2ff; border:1px solid #c7d2fe; border-radius:6px;
    }
</style>

<div>
    <div class="rs-head">
        <div>
            <div class="lbl">Teacher on leave</div>
            <div class="val">{{ $leave->teacher?->name ?? '—' }}</div>
            <div class="meta">{{ $leave->teacher?->subject ?? '' }} · {{ $leave->teacher?->campus ?? '' }}</div>
        </div>
        <div>
            <div class="lbl">Type</div>
            <div class="val">{{ \App\Models\LeaveType::labelFor($leave->type) }}</div>
        </div>
        <div>
            <div class="lbl">Dates</div>
            <div class="val">{{ optional($leave->starts_at)->format('d M') }} – {{ optional($leave->ends_at)->format('d M Y') }}</div>
            <div class="meta">{{ $days ? $days . ' day(s)' : '' }}</div>
        </div>
        <div>
            <div class="lbl">Responded</div>
            <div class="val">{{ $offers->count() }} teacher{{ $offers->count() === 1 ? '' : 's' }}</div>
            <div class="meta">interested</div>
        </div>
    </div>

    <div class="rs-respondents-note">
        ℹ Heatmap shows each respondent's per-period availability against this leave's actual lessons.
        Use it to spot conflicts before assigning, then pick the substitute below.
    </div>

    @if(! empty($eligibility) && ($eligibility['total_slots'] ?? 0) > 0)
        @include('filament.principal.teacher.eligibility-grid', [
            'eligibility' => $eligibility,
            'showPick'    => false,
        ])
    @else
        <div style="padding:10px 12px; background:#fffbeb; border:1px solid #fde68a; border-radius:8px; color:#78350f; font-size:12px; margin-bottom:10px;">
            No published timetable lessons fall in this leave window — proceed with the radio selection below.
        </div>
    @endif
</div>
