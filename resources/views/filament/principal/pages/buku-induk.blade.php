<x-filament-panels::page>
<div style="margin-bottom:1rem;">
    <input type="search" wire:model.live.debounce.300ms="search"
           placeholder="Search by name or NIS…"
           style="width:100%;max-width:420px;padding:.55rem .75rem;border:1px solid #e2e8f0;border-radius:.5rem;font-size:.9rem;">
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:1rem;">
    @foreach ($students as $s)
        <div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;">
            <div style="display:flex;justify-content:space-between;align-items:baseline;">
                <div style="font-weight:700;">{{ $s->name }}</div>
                <span style="background:#eef2ff;color:#4338ca;padding:.1rem .5rem;border-radius:.4rem;font-size:.7rem;font-weight:600;">{{ $s->nis }}</span>
            </div>
            <div style="font-size:.8rem;color:#64748b;margin-top:.3rem;">
                {{ strtoupper($s->campus) }} · {{ $s->grade }} · {{ $s->gender === 'M' ? 'Male' : ($s->gender === 'F' ? 'Female' : '—') }}
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem;margin-top:.6rem;font-size:.78rem;">
                <div><span style="color:#64748b;">DOB:</span> {{ $s->dob?->format('d M Y') ?? '—' }}</div>
                <div><span style="color:#64748b;">City:</span> {{ $s->city ?? '—' }}</div>
                <div><span style="color:#64748b;">Religion:</span> {{ $s->religion ?? '—' }}</div>
                <div><span style="color:#64748b;">Enrolled:</span> {{ $s->enrolled_at?->format('M Y') ?? '—' }}</div>
            </div>
            <div style="border-top:1px solid #f1f5f9;margin-top:.6rem;padding-top:.5rem;font-size:.78rem;">
                <div><span style="color:#64748b;">Guardian:</span> {{ $s->parent_name ?? '—' }}</div>
                <div><span style="color:#64748b;">Contact:</span> {{ $s->parent_phone ?? '—' }}</div>
            </div>
            <div style="margin-top:.6rem;">
                <a href="{{ url("/principal/students/{$s->id}/edit") }}" style="font-size:.78rem;color:#4f46e5;text-decoration:none;font-weight:600;">Open record →</a>
            </div>
        </div>
    @endforeach
</div>
</x-filament-panels::page>
