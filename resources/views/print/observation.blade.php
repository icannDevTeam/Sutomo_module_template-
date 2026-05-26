<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Observation · {{ optional($o->teacher)->name }}</title>
    <style>
        @page { size: A4; margin: 18mm; }
        body { font-family: 'Helvetica', Arial, sans-serif; color: #0f172a; line-height: 1.55; font-size: 11pt; margin: 0; padding: 22px; max-width: 800px; margin: 0 auto; background: #fff; }
        .ob-head { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 3px solid #4338ca; padding-bottom: 12px; margin-bottom: 22px; }
        .ob-school { font-size: 16pt; font-weight: 800; letter-spacing: 1.5px; color: #4338ca; }
        .ob-subhead { font-size: 10pt; color: #64748b; }
        .ob-meta { text-align: right; font-size: 10pt; color: #64748b; }
        .ob-title { font-size: 22pt; font-weight: 700; margin: 12px 0 4px; color: #0f172a; }
        .ob-sub { font-size: 12pt; color: #475569; margin-bottom: 22px; }
        .ob-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 22px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; margin-bottom: 22px; }
        .ob-label { font-size: 9pt; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; }
        .ob-value { font-size: 12pt; color: #0f172a; margin-top: 2px; }
        .ob-section { margin: 22px 0; }
        .ob-section-title { font-size: 12pt; font-weight: 700; color: #0f172a; border-bottom: 1px solid #cbd5e1; padding-bottom: 6px; margin-bottom: 10px; }
        table.ob-criteria { width: 100%; border-collapse: collapse; font-size: 10.5pt; }
        table.ob-criteria th, table.ob-criteria td { border: 1px solid #e2e8f0; padding: 8px 10px; text-align: left; vertical-align: top; }
        table.ob-criteria th { background: #f1f5f9; font-weight: 700; color: #334155; }
        table.ob-criteria .score { text-align: center; font-weight: 700; }
        .ob-narr { background: #fef9c3; border-left: 3px solid #f59e0b; padding: 12px 16px; border-radius: 4px; font-size: 11pt; line-height: 1.6; margin-bottom: 12px; white-space: pre-wrap; }
        .ob-narr.empty { background: #f1f5f9; border-left-color: #cbd5e1; color: #64748b; font-style: italic; }
        .ob-signoff { margin-top: 42px; display: grid; grid-template-columns: 1fr 1fr; gap: 32px; }
        .ob-sig-line { border-top: 1px solid #0f172a; margin-top: 56px; padding-top: 4px; font-size: 10pt; text-align: center; }
        .ob-actions { text-align: center; margin: 16px 0; }
        .ob-actions button { padding: 8px 22px; background: #4338ca; color: #fff; border: 0; border-radius: 4px; cursor: pointer; font-size: 11pt; }
        .ob-status { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 10pt; font-weight: 700; }
        .ob-status.pending { background: #fef3c7; color: #92400e; }
        .ob-status.approved { background: #d1fae5; color: #065f46; }
        .ob-status.rejected { background: #fee2e2; color: #991b1b; }
        @media print { .no-print { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="ob-actions no-print">
        <button onclick="window.print()">Print Observation</button>
    </div>

    @php
        $criteriaLabels = \App\Models\TeacherObservation::criteriaLabels();
        $dims = $o->dimensions ?? [];
        $notes = $o->notes_by_criterion ?? [];
        $statusKey = $o->status ?? 'pending';
    @endphp

    <div class="ob-head">
        <div>
            <div class="ob-school">SUTOMO SCHOOL</div>
            <div class="ob-subhead">Principal · Teacher Observation Report</div>
        </div>
        <div class="ob-meta">
            <div>Observation ID: #{{ $o->id }}</div>
            <div>Printed: {{ now()->format('d M Y H:i') }}</div>
        </div>
    </div>

    <div class="ob-title">Teacher Observation</div>
    <div class="ob-sub">
        <span class="ob-status {{ $statusKey }}">{{ strtoupper($statusKey) }}</span>
        @if($o->average_score !== null) · Average score: <strong>{{ $o->average_score }}/5</strong>@endif
    </div>

    <div class="ob-grid">
        <div>
            <div class="ob-label">Observee</div>
            <div class="ob-value">{{ optional($o->teacher)->name ?? '—' }}</div>
        </div>
        <div>
            <div class="ob-label">Observer</div>
            <div class="ob-value">{{ optional($o->observer)->name ?? '—' }}</div>
        </div>
        <div>
            <div class="ob-label">Observed at</div>
            <div class="ob-value">{{ optional($o->observed_at)->format('d M Y · H:i') ?? '—' }}</div>
        </div>
        <div>
            <div class="ob-label">Follow-up</div>
            <div class="ob-value">{{ optional($o->follow_up_date)->format('d M Y') ?? '—' }}</div>
        </div>
        <div>
            <div class="ob-label">Subject</div>
            <div class="ob-value">{{ $o->lesson_subject ?: '—' }}</div>
        </div>
        <div>
            <div class="ob-label">Class</div>
            <div class="ob-value">{{ $o->lesson_class_code ?: '—' }}</div>
        </div>
    </div>

    <div class="ob-section">
        <div class="ob-section-title">Criteria Scores (1–5)</div>
        <table class="ob-criteria">
            <thead>
                <tr>
                    <th style="width: 30%;">Criterion</th>
                    <th style="width: 10%;" class="score">Score</th>
                    <th style="width: 60%;">Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($criteriaLabels as $key => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="score">{{ isset($dims[$key]) && $dims[$key] !== null ? $dims[$key] . '/5' : '—' }}</td>
                        <td>{{ $notes[$key] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="ob-section">
        <div class="ob-section-title">Strengths</div>
        <div class="ob-narr {{ $o->strengths ? '' : 'empty' }}">{{ $o->strengths ?: 'No strengths recorded.' }}</div>
    </div>

    <div class="ob-section">
        <div class="ob-section-title">Action Items</div>
        <div class="ob-narr {{ $o->action_items ? '' : 'empty' }}">{{ $o->action_items ?: 'No action items recorded.' }}</div>
    </div>

    @if($o->review_notes || $o->reviewed_at)
        <div class="ob-section">
            <div class="ob-section-title">Review</div>
            <div class="ob-grid" style="margin-bottom: 8px;">
                <div>
                    <div class="ob-label">Reviewed by</div>
                    <div class="ob-value">{{ optional($o->reviewer)->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="ob-label">Reviewed at</div>
                    <div class="ob-value">{{ optional($o->reviewed_at)->format('d M Y · H:i') ?? '—' }}</div>
                </div>
            </div>
            @if($o->review_notes)
                <div class="ob-narr">{{ $o->review_notes }}</div>
            @endif
        </div>
    @endif

    <div class="ob-signoff">
        <div>
            <div class="ob-sig-line">Observer</div>
        </div>
        <div>
            <div class="ob-sig-line">Observee</div>
        </div>
    </div>
</body>
</html>
