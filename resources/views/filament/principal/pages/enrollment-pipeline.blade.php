<x-filament-panels::page>
<div style="display:flex;gap:.75rem;overflow-x:auto;padding:.25rem .25rem 1rem;">
    @foreach ($columns as $key => $col)
        <div style="flex:0 0 280px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:.75rem;padding:.75rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem;">
                <div style="font-weight:600;font-size:.85rem;color:#0f172a;">{{ $col['label'] }}</div>
                <span style="background:#e2e8f0;color:#334155;padding:.1rem .5rem;border-radius:.5rem;font-size:.75rem;font-weight:600;">{{ $col['count'] }}</span>
            </div>
            <div style="display:flex;flex-direction:column;gap:.5rem;max-height:60vh;overflow-y:auto;">
                @foreach ($col['records'] as $r)
                    <a href="{{ url("/principal/applications/{$r->id}/edit") }}"
                       style="background:white;border:1px solid #e2e8f0;border-left:3px solid var(--c-{{ $col['color'] }}, #4f46e5);border-radius:.5rem;padding:.6rem .7rem;text-decoration:none;color:inherit;display:block;">
                        <div style="font-weight:600;font-size:.85rem;">{{ $r->name }}</div>
                        <div style="font-size:.72rem;color:#64748b;margin-top:.2rem;">
                            {{ strtoupper($r->campus) }} · Grade {{ $r->grade }} · {{ $r->code }}
                        </div>
                        @if ($r->placement_score)
                            <div style="margin-top:.3rem;font-size:.72rem;color:#0f766e;">Score: {{ $r->placement_score }}</div>
                        @endif
                    </a>
                @endforeach
                @if (! $col['records']->count())
                    <div style="font-size:.75rem;color:#94a3b8;font-style:italic;padding:.5rem;">empty</div>
                @endif
            </div>
        </div>
    @endforeach
</div>
<style>
:root{--c-gray:#94a3b8;--c-sky:#0ea5e9;--c-emerald:#10b981;--c-amber:#f59e0b;--c-indigo:#4f46e5;--c-rose:#f43f5e;}
</style>
</x-filament-panels::page>
