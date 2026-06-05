<x-filament-panels::page>
<x-principal.module-hero
    title="Items Requiring Yayasan Board Attention"
    description="Behavior escalations, procurement above principal limit, and contract renewal escalations needing board-level review."
    icon="heroicon-o-exclamation-triangle"
    tone="indigo"
/>
<div style="height:.9rem;"></div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
    <div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
        <div style="padding:.75rem 1rem;border-bottom:1px solid #e2e8f0;font-weight:600;">Behavior — Principal Action</div>
        @forelse ($behavior as $b)
            <div style="padding:.7rem 1rem;border-top:1px solid #f1f5f9;">
                <div style="font-weight:600;font-size:.9rem;">{{ $b->title }}</div>
                <div style="font-size:.78rem;color:#64748b;margin-top:.2rem;">{{ $b->student->name }} · {{ $b->occurred_at->format('d M Y') }} · {{ strtoupper($b->severity) }}</div>
            </div>
        @empty
            <div style="padding:1rem;color:#94a3b8;text-align:center;">Clear.</div>
        @endforelse
    </div>
    <div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
        <div style="padding:.75rem 1rem;border-bottom:1px solid #e2e8f0;font-weight:600;">Procurement — Yayasan Review</div>
        @forelse ($procurement as $p)
            <div style="padding:.7rem 1rem;border-top:1px solid #f1f5f9;">
                <div style="font-weight:600;font-size:.9rem;">{{ $p->title }}</div>
                <div style="font-size:.78rem;color:#64748b;margin-top:.2rem;">{{ $p->category }} · Rp {{ number_format($p->amount) }} · {{ strtoupper($p->campus) }}</div>
            </div>
        @empty
            <div style="padding:1rem;color:#94a3b8;text-align:center;">Clear.</div>
        @endforelse
    </div>
</div>
</x-filament-panels::page>
