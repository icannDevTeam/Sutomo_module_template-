<x-filament-panels::page>
    {{-- Progress strip --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:.75rem;padding:1rem;margin-bottom:1rem;overflow-x:auto;">
        <div style="display:flex;gap:.5rem;min-width:max-content;">
            @php
                $steps = [
                    ['accepted','Accepted','#10b981'],
                    ['temp_id','Temp ID','#f59e0b'],
                    ['dev_fee','Dev Fee','#f59e0b'],
                    ['books','Books','#a855f7'],
                    ['class_assigned','Class','#6366f1'],
                    ['observing','5-Day Attend','#6366f1'],
                    ['id_issued','Permanent ID','#0ea5e9'],
                    ['activated','Activated','#10b981'],
                ];
            @endphp
            @foreach ($steps as $i => [$key, $label, $color])
                <div style="flex:1;min-width:120px;background:#f9fafb;border:1px solid #e5e7eb;border-top:3px solid {{ $color }};border-radius:.55rem;padding:.6rem .75rem;text-align:center;">
                    <div style="font-size:.7rem;letter-spacing:.05em;color:#6b7280;text-transform:uppercase;font-weight:600;">Step {{ $i+1 }}</div>
                    <div style="font-size:.85rem;font-weight:700;color:#111827;margin:.2rem 0;">{{ $label }}</div>
                    <div style="font-size:1.6rem;font-weight:800;color:{{ $color }};">{{ $counts[$key] ?? 0 }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Per-step queues --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:.85rem;">
        @foreach ($queues as $qkey => [$title, $ctaLabel, $stepKey, $rows])
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:.75rem;padding:1rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.55rem;">
                    <div style="font-weight:700;color:#111827;">{{ $title }}</div>
                    <span style="background:#eef2ff;color:#4338ca;padding:.1rem .55rem;border-radius:9999px;font-size:.72rem;font-weight:700;">{{ count($rows) }}</span>
                </div>
                <div style="font-size:.75rem;color:#6b7280;margin-bottom:.6rem;">Next: <b>{{ $ctaLabel }}</b></div>
                <div style="display:flex;flex-direction:column;gap:.35rem;max-height:280px;overflow-y:auto;">
                    @forelse ($rows as $r)
                        <div style="display:flex;justify-content:space-between;align-items:center;border:1px solid #f3f4f6;border-radius:.45rem;padding:.45rem .6rem;font-size:.8rem;">
                            <div style="min-width:0;">
                                <div style="font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $r->name }}</div>
                                <div style="color:#9ca3af;font-size:.72rem;">{{ $r->code }} · {{ strtoupper($r->campus) }} {{ $r->grade }}{{ $r->assigned_temp_id ? ' · '.$r->assigned_temp_id : '' }}</div>
                            </div>
                            {{ ($this->advanceAction)(['id' => $r->id, 'step' => $stepKey]) }}
                        </div>
                    @empty
                        <div style="color:#9ca3af;text-align:center;padding:1rem;">Nothing pending</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    {{-- Auto-assign proposals preview --}}
    @if (!empty($this->proposals))
        <div style="margin-top:1rem;background:#fffbeb;border:1px solid #fde68a;border-radius:.75rem;padding:1rem;">
            <div style="font-weight:700;color:#92400e;margin-bottom:.5rem;">Proposed Class Placements ({{ count($this->proposals) }})</div>
            <div style="font-size:.78rem;color:#78350f;margin-bottom:.6rem;">Click <b>Apply Proposed Placements</b> in the header to persist. Balances gender, religion, ethnicity and avoids surname clusters within class capacity.</div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.4rem;max-height:300px;overflow-y:auto;">
                @foreach ($this->proposals as $p)
                    <div style="background:#fff;border:1px solid #f3f4f6;border-radius:.4rem;padding:.4rem .55rem;font-size:.78rem;display:flex;justify-content:space-between;">
                        <span>{{ $p['student_name'] }}</span>
                        <span style="color:#4338ca;font-weight:700;">→ {{ $p['class_name'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <x-filament-actions::modals />
</x-filament-panels::page>
