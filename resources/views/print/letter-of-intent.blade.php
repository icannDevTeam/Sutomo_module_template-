<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Letter of Intent · {{ $record->teacher?->name ?? 'Teacher' }}</title>
    <style>
        @page { size: A4; margin: 22mm; }
        * { box-sizing: border-box; }
        body {
            font-family: Georgia, 'Times New Roman', serif;
            color: #0f172a;
            line-height: 1.55;
            font-size: 12pt;
            margin: 0;
            padding: 28px;
            max-width: 820px;
            margin: 0 auto;
            background: #fff;
        }
        .pl-actions {
            text-align: center;
            margin: 20px 0 24px;
        }
        .pl-actions button,
        .pl-actions a {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: 8px 20px;
            border-radius: 6px;
            border: 0;
            font-size: 11pt;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            margin: 0 4px;
        }
        .pl-actions button { background: #4f46e5; color: #fff; }
        .pl-actions a { background: #f3f4f6; color: #374151; }
        .pl-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 12px;
            margin-bottom: 22px;
        }
        .pl-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .pl-mark {
            width: 48px;
            height: 48px;
            border-radius: 9999px;
            background: linear-gradient(135deg, #4f46e5, #22c55e);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 18px;
        }
        .pl-school { font-size: 20pt; font-weight: 700; letter-spacing: 1px; }
        .pl-subhead { font-size: 11pt; color: #475569; margin-top: 4px; }
        .pl-title {
            text-align: center;
            font-size: 16pt;
            font-weight: 700;
            text-decoration: underline;
            margin: 18px 0 16px;
            letter-spacing: 1.5px;
        }
        .pl-ref {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            font-size: 11pt;
            color: #475569;
            margin-bottom: 22px;
        }
        .pl-pill {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .2rem .6rem;
            border-radius: 9999px;
            font-size: .75rem;
            font-weight: 700;
            background: #ecfeff;
            color: #155e75;
        }
        .pl-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
            margin-bottom: 18px;
        }
        .pl-card {
            border: 1px solid #cbd5e1;
            background: #fff;
            border-radius: 12px;
            padding: 14px;
        }
        .pl-label {
            font-size: .7rem;
            letter-spacing: .08em;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: .35rem;
        }
        .pl-value { font-weight: 700; font-size: 1rem; }
        .pl-subvalue { font-size: .85rem; color: #374151; }
        .pl-table { width: 100%; border-collapse: collapse; margin: 18px 0 0; font-size: 11pt; }
        .pl-table th,
        .pl-table td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .pl-table th { background: #f3f4f6; text-align: left; font-size: .82rem; color: #374151; }
        .pl-table td:last-child,
        .pl-table th:last-child { text-align: right; }
        .pl-table tfoot td { font-weight: 700; }
        .pl-justified { margin-top: 18px; text-align: justify; }
        .pl-notes { margin-top: 16px; }
        .pl-note-box {
            margin-top: 8px;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #d1fae5;
            background: #ecfdf5;
            color: #065f46;
            font-size: 11pt;
        }
        .pl-signoff { margin-top: 42px; }
        .pl-sig-row { display: flex; justify-content: space-between; gap: 40px; }
        .pl-sig { width: 45%; }
        .pl-sig-line {
            border-top: 1px solid #0f172a;
            margin-top: 56px;
            padding-top: 6px;
            font-size: 11pt;
            min-height: 28px;
        }
        .pl-foot {
            margin-top: 26px;
            padding-top: 12px;
            border-top: 1px dashed #d1d5db;
            font-size: .75rem;
            color: #6b7280;
            text-align: center;
        }
        @media print {
            .pl-actions { display: none; }
            body { padding: 0; }
            .pl-card { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="pl-actions no-print">
        <button onclick="window.print()">Print LOI</button>
        <a href="{{ \App\Filament\Principal\Resources\LetterOfIntentResource::getUrl('view', ['record' => $record->id], panel: 'principal') }}">Back to LOI</a>
    </div>

    <div class="pl-head">
        <div>
            <div class="pl-brand">
                <div class="pl-mark">S</div>
                <div>
                    <div class="pl-school">YAYASAN PERGURUAN SUTOMO</div>
                    <div class="pl-subhead">Letter of Intent · {{ $record->academic_year }}</div>
                </div>
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:1.55rem;font-weight:800;letter-spacing:.05em;color:#4f46e5;">LETTER OF INTENT</div>
            <div style="font-size:.85rem;color:#374151;margin-top:.15rem;"><b>No:</b> LOI-{{ str_pad((string) $record->id, 5, '0', STR_PAD_LEFT) }}</div>
            <div style="font-size:.8rem;color:#6b7280;"><b>Date:</b> {{ optional($record->sent_at ?? $record->created_at)->format('d M Y') ?? now()->format('d M Y') }}</div>
            <div style="margin-top:.4rem;">
                <span class="pl-pill">{{ \App\Models\LetterOfIntent::STATUSES[$record->status] ?? $record->status }}</span>
            </div>
        </div>
    </div>

    <div class="pl-ref">
        <div><b>To:</b> {{ $record->teacher?->name ?? '—' }}</div>
        <div><b>Print Date:</b> {{ now()->format('d M Y') }}</div>
    </div>

    <div class="pl-justified">
        {{ $record->body }}
    </div>

    <div class="pl-grid">
        <div class="pl-card">
            <div class="pl-label">Teacher</div>
            <div class="pl-value">{{ $record->teacher?->name ?? '—' }}</div>
            <div class="pl-subvalue">{{ $record->teacher?->employee_no ?? '—' }}</div>
            <div class="pl-subvalue">{{ $record->position ?? '—' }}</div>
        </div>
        <div class="pl-card">
            <div class="pl-label">Principal</div>
            <div class="pl-value">{{ $record->principal?->name ?? '—' }}</div>
            <div class="pl-subvalue">Sent at {{ $record->sent_at?->format('d M Y H:i') ?? '—' }}</div>
            <div class="pl-subvalue">Deadline {{ $record->deadline_at?->format('d M Y H:i') ?? '—' }}</div>
        </div>
        <div class="pl-card">
            <div class="pl-label">Status</div>
            <div class="pl-value">{{ \App\Models\LetterOfIntent::STATUSES[$record->status] ?? $record->status }}</div>
            <div class="pl-subvalue">Signed {{ $record->signed_at?->format('d M Y H:i') ?? '—' }}</div>
            <div class="pl-subvalue">Declined {{ $record->decline_reason ? 'Yes' : 'No' }}</div>
        </div>
    </div>

    <table class="pl-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Period</th>
                <th>Stage</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Letter of Intent</strong><div style="font-size:.78rem;color:#6b7280;">Initial invitation for academic year continuation</div></td>
                <td>{{ $record->academic_year }}</td>
                <td>{{ ucfirst($record->status) }}</td>
                <td>{{ $record->sent_at?->format('d M Y') ?? '—' }}</td>
            </tr>
            <tr>
                <td><strong>Signed LOI</strong><div style="font-size:.78rem;color:#6b7280;">Teacher acknowledgement and response</div></td>
                <td>{{ $record->academic_year }}</td>
                <td>{{ $record->signed_at ? 'Signed' : 'Pending' }}</td>
                <td>{{ $record->signed_at?->format('d M Y') ?? '—' }}</td>
            </tr>
            <tr>
                <td><strong>Submitted to Yayasan</strong><div style="font-size:.78rem;color:#6b7280;">Principal-side handoff</div></td>
                <td>{{ $record->academic_year }}</td>
                <td>{{ $record->submitted_to_yayasan_at ? 'Submitted' : 'Pending' }}</td>
                <td>{{ $record->submitted_to_yayasan_at?->format('d M Y') ?? '—' }}</td>
            </tr>
            <tr>
                <td><strong>Yayasan Contract</strong><div style="font-size:.78rem;color:#6b7280;">Uploaded contract from Yayasan</div></td>
                <td>{{ $record->academic_year }}</td>
                <td>{{ $record->yayasan_contract_uploaded_at ? 'Uploaded' : 'Waiting' }}</td>
                <td>{{ $record->yayasan_contract_uploaded_at?->format('d M Y') ?? '—' }}</td>
            </tr>
            <tr>
                <td><strong>Agreement Letter</strong><div style="font-size:.78rem;color:#6b7280;">Temporary handover agreement</div></td>
                <td>{{ $record->academic_year }}</td>
                <td>{{ $record->agreement_signed_at ? 'Signed' : 'Pending' }}</td>
                <td>{{ $record->agreement_signed_at?->format('d M Y') ?? '—' }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align:right;">Completed milestones</td>
                <td>{{ collect([$record->signed_at, $record->submitted_to_yayasan_at, $record->yayasan_contract_uploaded_at, $record->agreement_signed_at, $record->buku_induk_recorded_at])->filter()->count() }}</td>
            </tr>
            <tr>
                <td colspan="3" style="text-align:right;">Pending milestones</td>
                <td>{{ 5 - collect([$record->signed_at, $record->submitted_to_yayasan_at, $record->yayasan_contract_uploaded_at, $record->agreement_signed_at, $record->buku_induk_recorded_at])->filter()->count() }}</td>
            </tr>
        </tfoot>
    </table>

    @if ($record->notes)
        <div class="pl-notes">
            <div class="pl-label">Notes</div>
            <div class="pl-note-box">{{ $record->notes }}</div>
        </div>
    @endif

    <div class="pl-signoff">
        <div class="pl-sig-row">
            <div class="pl-sig">
                <div>Acknowledged by Teacher</div>
                <div class="pl-sig-line">{{ $record->teacher?->name ?? '—' }}</div>
            </div>
            <div class="pl-sig">
                <div>Approved by Principal</div>
                <div class="pl-sig-line">{{ $record->principal?->name ?? 'Principal' }}</div>
            </div>
        </div>
    </div>

    <div class="pl-foot">
        System-generated LOI document for administrative use.
    </div>
</body>
</html>
