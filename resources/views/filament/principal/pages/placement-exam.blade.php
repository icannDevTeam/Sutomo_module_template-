<x-filament-panels::page>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1rem;">
    <div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;">
        <div style="font-size:.75rem;text-transform:uppercase;color:#64748b;letter-spacing:.1em;">Average Score</div>
        <div style="font-size:2rem;font-weight:700;color:#4f46e5;">{{ $avg }}</div>
        <div style="font-size:.8rem;color:#64748b;">across {{ $scheduled->count() + $passed->count() + $failed->count() }} candidates</div>
    </div>
    <div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;">
        <div style="font-size:.75rem;text-transform:uppercase;color:#64748b;letter-spacing:.1em;">Scheduled</div>
        <div style="font-size:2rem;font-weight:700;color:#0ea5e9;">{{ $scheduled->count() }}</div>
    </div>
    <div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;">
        <div style="font-size:.75rem;text-transform:uppercase;color:#64748b;letter-spacing:.1em;">Passed</div>
        <div style="font-size:2rem;font-weight:700;color:#10b981;">{{ $passed->count() }}</div>
    </div>
    <div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;">
        <div style="font-size:.75rem;text-transform:uppercase;color:#64748b;letter-spacing:.1em;">Failed</div>
        <div style="font-size:2rem;font-weight:700;color:#f43f5e;">{{ $failed->count() }}</div>
    </div>
</div>

<div style="margin-top:1.25rem;background:white;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
    <div style="padding:.75rem 1rem;border-bottom:1px solid #e2e8f0;font-weight:600;">Upcoming Exam Schedule</div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
            <thead style="background:#f8fafc;">
                <tr><th style="text-align:left;padding:.55rem .75rem;">Code</th><th style="text-align:left;padding:.55rem .75rem;">Name</th><th style="text-align:left;padding:.55rem .75rem;">Campus</th><th style="text-align:left;padding:.55rem .75rem;">Grade</th><th style="text-align:left;padding:.55rem .75rem;">Exam Date</th></tr>
            </thead>
            <tbody>
                @forelse ($scheduled as $a)
                    <tr style="border-top:1px solid #f1f5f9;">
                        <td style="padding:.5rem .75rem;font-family:monospace;color:#475569;">{{ $a->code }}</td>
                        <td style="padding:.5rem .75rem;">{{ $a->name }}</td>
                        <td style="padding:.5rem .75rem;">{{ strtoupper($a->campus) }}</td>
                        <td style="padding:.5rem .75rem;">{{ $a->grade }}</td>
                        <td style="padding:.5rem .75rem;">{{ $a->exam_date?->format('d M Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="padding:1rem;color:#94a3b8;text-align:center;">No upcoming exams</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-filament-panels::page>
