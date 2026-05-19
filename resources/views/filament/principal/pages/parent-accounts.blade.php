<x-filament-panels::page>
<div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
        <thead style="background:#f8fafc;">
            <tr>
                <th style="text-align:left;padding:.65rem .85rem;">Parent</th>
                <th style="text-align:left;padding:.65rem .85rem;">Student</th>
                <th style="text-align:left;padding:.65rem .85rem;">Campus</th>
                <th style="text-align:left;padding:.65rem .85rem;">Email</th>
                <th style="text-align:left;padding:.65rem .85rem;">Phone</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($students as $s)
                <tr style="border-top:1px solid #f1f5f9;">
                    <td style="padding:.55rem .85rem;font-weight:600;">{{ $s->parent_name ?? '—' }}</td>
                    <td style="padding:.55rem .85rem;">{{ $s->name }} <span style="color:#64748b;font-size:.75rem;">({{ $s->nis }})</span></td>
                    <td style="padding:.55rem .85rem;">{{ strtoupper($s->campus) }}</td>
                    <td style="padding:.55rem .85rem;color:#4f46e5;">{{ $s->parent_email }}</td>
                    <td style="padding:.55rem .85rem;">{{ $s->parent_phone ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
</x-filament-panels::page>
