<x-filament-panels::page>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1rem;">
    <div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;">
        <div style="font-size:.75rem;color:#64748b;text-transform:uppercase;">Fee Arrears</div>
        <div style="font-size:1.8rem;font-weight:700;color:#f43f5e;">{{ $arrears->count() }}</div>
        <div style="font-size:.8rem;color:#64748b;">students with outstanding fees</div>
    </div>
    <div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;">
        <div style="font-size:.75rem;color:#64748b;text-transform:uppercase;">Pending Procurement</div>
        <div style="font-size:1.8rem;font-weight:700;color:#0ea5e9;">{{ $procurement->count() }}</div>
        <div style="font-size:.8rem;color:#64748b;">requests in queue</div>
    </div>
    <div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;">
        <div style="font-size:.75rem;color:#64748b;text-transform:uppercase;">Pending Total</div>
        <div style="font-size:1.4rem;font-weight:700;color:#4f46e5;">Rp {{ number_format($totalAmount) }}</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
    <div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
        <div style="padding:.75rem 1rem;border-bottom:1px solid #e2e8f0;font-weight:600;background:#fef2f2;color:#991b1b;">Arrears — Parent Contacts</div>
        <table style="width:100%;font-size:.83rem;border-collapse:collapse;">
            @foreach ($arrears as $s)
                <tr style="border-top:1px solid #f1f5f9;">
                    <td style="padding:.5rem .8rem;">{{ $s->name }}</td>
                    <td style="padding:.5rem .8rem;color:#64748b;">{{ strtoupper($s->campus) }} · {{ $s->grade }}</td>
                    <td style="padding:.5rem .8rem;text-align:right;color:#64748b;">{{ $s->parent_phone ?? '—' }}</td>
                </tr>
            @endforeach
        </table>
    </div>
    <div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
        <div style="padding:.75rem 1rem;border-bottom:1px solid #e2e8f0;font-weight:600;background:#eff6ff;color:#1e40af;">Procurement Queue</div>
        <table style="width:100%;font-size:.83rem;border-collapse:collapse;">
            @foreach ($procurement as $p)
                <tr style="border-top:1px solid #f1f5f9;">
                    <td style="padding:.5rem .8rem;">{{ $p->title }}</td>
                    <td style="padding:.5rem .8rem;color:#64748b;">{{ $p->category }}</td>
                    <td style="padding:.5rem .8rem;text-align:right;font-weight:600;">Rp {{ number_format($p->amount) }}</td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
</x-filament-panels::page>
