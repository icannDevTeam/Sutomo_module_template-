<x-filament-panels::page>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1rem;">
    @forelse ($events as $e)
        <div style="background:linear-gradient(135deg,#ffffff,#f8fafc);border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;border-left:4px solid #f59e0b;">
            <div style="display:flex;justify-content:space-between;align-items:start;">
                <div style="font-weight:700;font-size:1rem;">{{ $e->title }}</div>
                <span style="background:#fef3c7;color:#92400e;padding:.1rem .5rem;border-radius:.4rem;font-size:.7rem;text-transform:uppercase;font-weight:600;">{{ $e->category }}</span>
            </div>
            <div style="font-size:.78rem;color:#64748b;margin-top:.4rem;">{{ $e->starts_at->format('d M Y') }} · {{ strtoupper($e->campus) }}</div>
            <div style="font-size:.85rem;color:#334155;margin-top:.5rem;">{{ \Illuminate\Support\Str::limit($e->description, 90) }}</div>
            <div style="display:flex;justify-content:space-between;margin-top:.65rem;font-size:.75rem;">
                <span style="color:#64748b;">PIC: {{ $e->pic ?? '—' }}</span>
                <span style="color:#0f766e;font-weight:600;">{{ $e->participants }} participants</span>
            </div>
        </div>
    @empty
        <div style="color:#94a3b8;">No activities yet.</div>
    @endforelse
</div>
</x-filament-panels::page>
