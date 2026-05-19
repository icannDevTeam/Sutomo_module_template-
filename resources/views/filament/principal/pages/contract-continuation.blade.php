<x-filament-panels::page>
<div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;overflow-x:auto;">
    <div style="padding:.85rem 1rem;border-bottom:1px solid #e2e8f0;background:#fffbeb;color:#92400e;font-weight:600;">
        ⏰ Contracts expiring within 3 months — recommend continuation or send to Yayasan
    </div>
    <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
        <thead style="background:#f8fafc;"><tr>
            <th style="text-align:left;padding:.6rem .85rem;">Teacher</th>
            <th style="text-align:left;padding:.6rem .85rem;">Campus</th>
            <th style="text-align:left;padding:.6rem .85rem;">Contract</th>
            <th style="text-align:left;padding:.6rem .85rem;">End Date</th>
            <th style="text-align:left;padding:.6rem .85rem;">Rating</th>
            <th style="text-align:left;padding:.6rem .85rem;">Action</th>
        </tr></thead>
        <tbody>
            @forelse ($expiring as $t)
                <tr style="border-top:1px solid #f1f5f9;">
                    <td style="padding:.55rem .85rem;font-weight:600;">{{ $t->name }}</td>
                    <td style="padding:.55rem .85rem;">{{ strtoupper($t->campus) }}</td>
                    <td style="padding:.55rem .85rem;">{{ $t->contract ?? '—' }}</td>
                    <td style="padding:.55rem .85rem;color:#991b1b;">{{ $t->contract_end?->format('d M Y') }}</td>
                    <td style="padding:.55rem .85rem;">{{ $t->rating ? $t->rating.'/5' : '—' }}</td>
                    <td style="padding:.55rem .85rem;">
                        <span style="background:#dcfce7;color:#166534;padding:.15rem .55rem;border-radius:.4rem;font-size:.72rem;font-weight:600;cursor:pointer;">Recommend renewal</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="padding:1.5rem;text-align:center;color:#94a3b8;">No contracts expiring soon.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
</x-filament-panels::page>
