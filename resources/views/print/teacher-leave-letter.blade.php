<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leave Letter · {{ $record->teacher?->name ?? 'Teacher' }}</title>
    <style>
        @page { size: A4; margin: 22mm; }
        * { box-sizing: border-box; }
        body { font-family: Georgia, 'Times New Roman', serif; color: #0f172a; line-height: 1.55; font-size: 12pt; margin: 0; padding: 28px; max-width: 800px; margin: 0 auto; background: #fff; }
        .pl-head { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 28px; }
        .pl-school { font-size: 20pt; font-weight: 700; letter-spacing: 1px; }
        .pl-subhead { font-size: 11pt; color: #475569; margin-top: 4px; }
        .pl-title { text-align: center; font-size: 16pt; font-weight: 700; text-decoration: underline; margin: 28px 0 18px; letter-spacing: 1.5px; }
        .pl-ref { display: flex; justify-content: space-between; font-size: 11pt; color: #475569; margin-bottom: 24px; }
        .pl-body p { margin: 14px 0; text-align: justify; }
        .pl-body strong { color: #0f172a; }
        .pl-table { width: 100%; border-collapse: collapse; margin: 18px 0; font-size: 11pt; }
        .pl-table td { padding: 8px 12px; border: 1px solid #cbd5e1; }
        .pl-table td:first-child { width: 35%; background: #f1f5f9; font-weight: 600; }
        .pl-signoff { margin-top: 48px; }
        .pl-sig-row { display: flex; justify-content: space-between; gap: 40px; }
        .pl-sig { width: 45%; }
        .pl-sig-line { border-top: 1px solid #0f172a; margin-top: 60px; padding-top: 6px; font-size: 11pt; }
        .pl-actions { text-align: center; margin: 20px 0; }
        .pl-actions button { padding: 8px 24px; background: #4338ca; color: #fff; border: 0; border-radius: 4px; cursor: pointer; font-size: 11pt; }
        @media print { .pl-actions { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="pl-actions">
        <button onclick="window.print()">🖨 Print this letter</button>
    </div>

    <div class="pl-head">
        <div class="pl-school">SUTOMO SCHOOL</div>
        <div class="pl-subhead">Yayasan Pendidikan Sutomo · Medan, Indonesia</div>
    </div>

    <div class="pl-ref">
        <div>Ref: LEAVE/{{ str_pad($record->id, 5, '0', STR_PAD_LEFT) }}/{{ now()->format('Y') }}</div>
        <div>Issued: {{ now()->format('d F Y') }}</div>
    </div>

    <div class="pl-title">LEAVE APPROVAL LETTER</div>

    <div class="pl-body">
        <p>This letter certifies that the following member of teaching staff has been granted leave of absence on the terms recorded below:</p>

        <table class="pl-table">
            <tr><td>Teacher Name</td><td>{{ $record->teacher?->name ?? '—' }}</td></tr>
            <tr><td>Employee Code</td><td>{{ $record->teacher?->code ?? '—' }}</td></tr>
            <tr><td>Department</td><td>{{ $record->teacher?->dept ?? '—' }}</td></tr>
            <tr><td>Leave Type</td><td>{{ \App\Models\LeaveType::labelFor($record->type) }}</td></tr>
            <tr><td>From</td><td>{{ optional($record->starts_at)->format('l, d F Y') ?? '—' }}</td></tr>
            <tr><td>To</td><td>{{ optional($record->ends_at)->format('l, d F Y') ?? '—' }}</td></tr>
            <tr><td>Duration</td><td>{{ $record->starts_at && $record->ends_at ? ($record->starts_at->diffInDays($record->ends_at) + 1) . ' working day(s)' : '—' }}</td></tr>
            <tr><td>Substitute / Cover</td><td>{{ $record->substitute?->name ?? 'To be arranged by Department Head' }}</td></tr>
            @if($record->reason)
                <tr><td>Stated Reason</td><td>{{ $record->reason }}</td></tr>
            @endif
            <tr><td>Status</td><td><strong>{{ strtoupper(\App\Models\TeacherLeave::STATUSES[$record->status] ?? $record->status) }}</strong></td></tr>
        </table>

        <p>During the absence period, the teacher's regular duties and classes will be covered as arranged. The teacher is expected to ensure handover notes are provided to the substitute or department head before commencement of leave.</p>

        <p>Upon return, the teacher is required to report to the Principal's office for re-engagement briefing.</p>

        <p>This letter is issued for administrative records and may be presented as supporting documentation where required.</p>
    </div>

    <div class="pl-signoff">
        <div class="pl-sig-row">
            <div class="pl-sig">
                <div>Acknowledged by Teacher</div>
                <div class="pl-sig-line">{{ $record->teacher?->name ?? '—' }}</div>
            </div>
            <div class="pl-sig">
                <div>Approved by Principal</div>
                <div class="pl-sig-line">{{ $record->decided_by ?: 'Principal' }}</div>
            </div>
        </div>
    </div>
</body>
</html>
