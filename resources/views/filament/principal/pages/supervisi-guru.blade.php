<x-filament-panels::page>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1rem;">
    <div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;"><div style="font-size:.75rem;color:#64748b;text-transform:uppercase;">Teachers</div><div style="font-size:1.6rem;font-weight:700;">{{ $teachers }}</div></div>
    <div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;"><div style="font-size:.75rem;color:#64748b;text-transform:uppercase;">Scheduled</div><div style="font-size:1.6rem;font-weight:700;color:#0ea5e9;">{{ $scheduled }}</div></div>
    <div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;"><div style="font-size:.75rem;color:#64748b;text-transform:uppercase;">Completed</div><div style="font-size:1.6rem;font-weight:700;color:#10b981;">{{ $completed }}</div></div>
    <div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;"><div style="font-size:.75rem;color:#64748b;text-transform:uppercase;">Training Needed</div><div style="font-size:1.6rem;font-weight:700;color:#f59e0b;">{{ $training }}</div></div>
</div>

<div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
        <thead style="background:#f8fafc;"><tr>
            <th style="text-align:left;padding:.6rem .85rem;">Date</th>
            <th style="text-align:left;padding:.6rem .85rem;">Teacher</th>
            <th style="text-align:left;padding:.6rem .85rem;">Round</th>
            <th style="text-align:left;padding:.6rem .85rem;">Evaluator</th>
            <th style="text-align:left;padding:.6rem .85rem;">Score</th>
            <th style="text-align:left;padding:.6rem .85rem;">Status</th>
        </tr></thead>
        <tbody>
            @foreach ($records as $r)
                <tr style="border-top:1px solid #f1f5f9;">
                    <td style="padding:.5rem .85rem;">{{ $r->scheduled_at->format('d M Y') }}</td>
                    <td style="padding:.5rem .85rem;">{{ $r->teacher->name }}</td>
                    <td style="padding:.5rem .85rem;">{{ $r->round }}</td>
                    <td style="padding:.5rem .85rem;">{{ $r->evaluator ?? '—' }}</td>
                    <td style="padding:.5rem .85rem;font-weight:600;">{{ $r->score ?? '—' }}</td>
                    <td style="padding:.5rem .85rem;">
                        @php $c = \App\Models\SupervisiEvaluation::STATUS_COLORS[$r->status] ?? 'gray';
                             $bg = ['gray'=>'#f1f5f9','success'=>'#dcfce7','danger'=>'#fee2e2','warning'=>'#fef3c7'][$c] ?? '#f1f5f9';
                             $fg = ['gray'=>'#334155','success'=>'#166534','danger'=>'#991b1b','warning'=>'#92400e'][$c] ?? '#334155'; @endphp
                        <span style="background:{{ $bg }};color:{{ $fg }};padding:.1rem .5rem;border-radius:.4rem;font-size:.72rem;font-weight:600;">{{ \App\Models\SupervisiEvaluation::STATUSES[$r->status] }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
</x-filament-panels::page>
