<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Duty Briefing · {{ $record->title }}</title>
    <style>
        @page { size: A4; margin: 18mm; }
        body { font-family: 'Helvetica', Arial, sans-serif; color: #0f172a; line-height: 1.55; font-size: 11pt; margin: 0; padding: 22px; max-width: 800px; margin: 0 auto; background: #fff; }
        .db-head { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 3px solid #4338ca; padding-bottom: 12px; margin-bottom: 22px; }
        .db-school { font-size: 16pt; font-weight: 800; letter-spacing: 1.5px; color: #4338ca; }
        .db-subhead { font-size: 10pt; color: #64748b; }
        .db-meta { text-align: right; font-size: 10pt; color: #64748b; }
        .db-title { font-size: 22pt; font-weight: 700; margin: 12px 0 4px; color: #0f172a; }
        .db-sub { font-size: 12pt; color: #475569; margin-bottom: 22px; }
        .db-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 22px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; margin-bottom: 22px; }
        .db-grid-item-full { grid-column: 1 / -1; }
        .db-label { font-size: 9pt; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; }
        .db-value { font-size: 12pt; color: #0f172a; margin-top: 2px; }
        .db-week { display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; margin: 6px 0; }
        .db-day { padding: 8px 4px; text-align: center; background: #e2e8f0; border-radius: 4px; font-size: 10pt; }
        .db-day.on { background: #4338ca; color: #fff; font-weight: 700; }
        .db-section { margin: 22px 0; }
        .db-section-title { font-size: 12pt; font-weight: 700; color: #0f172a; border-bottom: 1px solid #cbd5e1; padding-bottom: 6px; margin-bottom: 10px; }
        .db-instructions { background: #fef9c3; border-left: 3px solid #f59e0b; padding: 12px 16px; border-radius: 4px; font-size: 11pt; line-height: 1.6; }
        .db-instructions ul { margin: 6px 0 0 18px; }
        .db-signoff { margin-top: 42px; display: grid; grid-template-columns: 1fr 1fr; gap: 32px; }
        .db-sig-line { border-top: 1px solid #0f172a; margin-top: 56px; padding-top: 4px; font-size: 10pt; text-align: center; }
        .db-actions { text-align: center; margin: 16px 0; }
        .db-actions button { padding: 8px 22px; background: #4338ca; color: #fff; border: 0; border-radius: 4px; cursor: pointer; font-size: 11pt; }
        @media print { .db-actions { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="db-actions">
        <button onclick="window.print()">🖨 Print Briefing</button>
    </div>

    @php
        use App\Models\DutyAssignment;
        $selectedDays = collect(is_array($record->days_of_week) ? $record->days_of_week : [])->map(fn ($d) => (int) $d)->all();
    @endphp

    <div class="db-head">
        <div>
            <div class="db-school">SUTOMO SCHOOL</div>
            <div class="db-subhead">Operations · Duty Briefing</div>
        </div>
        <div class="db-meta">
            Ref: DUTY/{{ str_pad($record->id, 5, '0', STR_PAD_LEFT) }}/{{ now()->format('Y') }}<br>
            Issued: {{ now()->format('d M Y') }}
        </div>
    </div>

    <div class="db-title">{{ $record->title }}</div>
    <div class="db-sub">
        {{ DutyAssignment::RECURRENCES[$record->recurrence] ?? $record->recurrence }}
        @if($record->academic_year) · AY {{ $record->academic_year }}@endif
    </div>

    <div class="db-grid">
        <div>
            <div class="db-label">Assigned To</div>
            <div class="db-value"><strong>{{ $record->teacher?->name ?? '—' }}</strong></div>
        </div>
        <div>
            <div class="db-label">Assigned By</div>
            <div class="db-value">{{ $record->assigned_by ?: '—' }}</div>
        </div>
        <div>
            <div class="db-label">Location</div>
            <div class="db-value">{{ $record->location ?: '—' }}</div>
        </div>
        <div>
            <div class="db-label">Status</div>
            <div class="db-value">{{ DutyAssignment::STATUSES[$record->status] ?? $record->status }}</div>
        </div>
        <div>
            <div class="db-label">Starts</div>
            <div class="db-value">{{ optional($record->starts_at)->format('d M Y, H:i') ?: '—' }}</div>
        </div>
        <div>
            <div class="db-label">Ends</div>
            <div class="db-value">{{ optional($record->ends_at)->format('d M Y, H:i') ?: '—' }}</div>
        </div>

        @if($record->recurrence === 'weekly')
            <div class="db-grid-item-full">
                <div class="db-label">Recurs on</div>
                <div class="db-week">
                    @foreach(DutyAssignment::DAYS as $n => $abbr)
                        <div class="db-day {{ in_array($n, $selectedDays, true) ? 'on' : '' }}">{{ $abbr }}</div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div class="db-section">
        <div class="db-section-title">Duty Instructions</div>
        <div class="db-instructions">
            <strong>Standard responsibilities:</strong>
            <ul>
                <li>Arrive at the designated location 5 minutes before the duty period begins.</li>
                <li>Supervise students and ensure their safety and orderly conduct.</li>
                <li>Address any incidents promptly and report serious matters to the Vice Principal on duty.</li>
                <li>Maintain a visible presence throughout the duty period.</li>
                <li>Submit a duty log entry upon completion if requested.</li>
            </ul>
            @if($record->decline_reason)
                <div style="margin-top:10px;"><strong>Special note:</strong> {{ $record->decline_reason }}</div>
            @endif
        </div>
    </div>

    <div class="db-signoff">
        <div>
            Acknowledged by Teacher
            <div class="db-sig-line">{{ $record->teacher?->name ?? '—' }}</div>
        </div>
        <div>
            Issued by Principal's Office
            <div class="db-sig-line">{{ $record->assigned_by ?: 'Principal' }}</div>
        </div>
    </div>
</body>
</html>
