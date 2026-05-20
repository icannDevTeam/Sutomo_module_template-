<x-filament-panels::page>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
    <div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
        <div style="padding:.75rem 1rem;border-bottom:1px solid #e2e8f0;font-weight:600;background:#f0fdf4;color:#166534;">🏆 Top 10 — Highest GPA</div>
        <table style="width:100%;font-size:.85rem;border-collapse:collapse;">
            @foreach ($top as $s)
                <tr style="border-top:1px solid #f1f5f9;">
                    <td style="padding:.55rem .75rem;">{{ $s->name }}</td>
                    <td style="padding:.55rem .75rem;color:#64748b;">{{ strtoupper($s->campus) }} · {{ $s->grade }}</td>
                    <td style="padding:.55rem .75rem;text-align:right;font-weight:700;color:#166534;">{{ $s->gpa }}</td>
                </tr>
            @endforeach
        </table>
    </div>
    <div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
        <div style="padding:.75rem 1rem;border-bottom:1px solid #e2e8f0;font-weight:600;background:#fef2f2;color:#991b1b;">At-Risk Students</div>
        <table style="width:100%;font-size:.85rem;border-collapse:collapse;">
            @foreach ($atRisk as $s)
                <tr style="border-top:1px solid #f1f5f9;">
                    <td style="padding:.55rem .75rem;">{{ $s->name }}</td>
                    <td style="padding:.55rem .75rem;color:#64748b;">{{ strtoupper($s->campus) }}</td>
                    <td style="padding:.55rem .75rem;text-align:right;color:#991b1b;">Att {{ $s->attendance_rate }}% · GPA {{ $s->gpa }}</td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
</x-filament-panels::page>
