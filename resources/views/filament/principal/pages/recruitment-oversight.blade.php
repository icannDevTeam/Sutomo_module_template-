<x-filament-panels::page>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
    <div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
        <div style="padding:.75rem 1rem;font-weight:600;border-bottom:1px solid #e2e8f0;">Open Vacancies</div>
        <table style="width:100%;font-size:.85rem;border-collapse:collapse;">
            @foreach ($vacancies as $v)
                <tr style="border-top:1px solid #f1f5f9;">
                    <td style="padding:.55rem .85rem;font-weight:600;">{{ $v->title }}</td>
                    <td style="padding:.55rem .85rem;">{{ strtoupper($v->campus) }}</td>
                    <td style="padding:.55rem .85rem;text-align:right;color:#4f46e5;font-weight:600;">{{ $v->candidates_count }} candidates</td>
                </tr>
            @endforeach
        </table>
    </div>
    <div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
        <div style="padding:.75rem 1rem;font-weight:600;border-bottom:1px solid #e2e8f0;">Shortlisted from Talent Pool</div>
        <table style="width:100%;font-size:.85rem;border-collapse:collapse;">
            @foreach ($shortlist as $c)
                <tr style="border-top:1px solid #f1f5f9;">
                    <td style="padding:.55rem .85rem;">{{ $c->name }}</td>
                    <td style="padding:.55rem .85rem;color:#64748b;">{{ $c->subject }}</td>
                    <td style="padding:.55rem .85rem;text-align:right;font-size:.75rem;color:#92400e;font-weight:600;">{{ $c->years_exp }} yrs</td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
</x-filament-panels::page>
