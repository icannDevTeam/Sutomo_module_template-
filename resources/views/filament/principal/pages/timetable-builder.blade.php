<x-filament-panels::page>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem;">
    @foreach ($classes as $c)
        @php
            $util = $c->capacity > 0 ? min(100, round(($c->students_count / $c->capacity) * 100)) : 0;
            $color = $util >= 95 ? '#f43f5e' : ($util >= 80 ? '#f59e0b' : '#10b981');
        @endphp
        <div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;">
            <div style="display:flex;justify-content:space-between;align-items:baseline;">
                <div style="font-weight:700;font-size:1rem;">{{ $c->name }}</div>
                <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;">{{ strtoupper($c->campus) }}</div>
            </div>
            <div style="font-size:.75rem;color:#64748b;margin:.2rem 0 .65rem;">{{ $c->code }} · Room {{ $c->room ?? '—' }}</div>
            <div style="font-size:.8rem;color:#475569;margin-bottom:.4rem;">Homeroom: <strong>{{ $c->homeroomTeacher?->name ?? 'Unassigned' }}</strong></div>
            <div style="display:flex;justify-content:space-between;font-size:.75rem;color:#64748b;margin-bottom:.25rem;">
                <span>{{ $c->students_count }} / {{ $c->capacity }} students</span>
                <span style="color:{{ $color }};font-weight:600;">{{ $util }}%</span>
            </div>
            <div style="background:#f1f5f9;border-radius:.5rem;height:.5rem;overflow:hidden;">
                <div style="height:100%;background:{{ $color }};width:{{ $util }}%;"></div>
            </div>
        </div>
    @endforeach
</div>
</x-filament-panels::page>
