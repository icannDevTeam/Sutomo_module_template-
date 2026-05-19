<x-filament-panels::page>
    {{-- Filter bar --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:.75rem;padding:.85rem 1rem;margin-bottom:1rem;">
        <div style="display:flex;flex-wrap:wrap;gap:.5rem;align-items:center;">
            <select wire:model.live="period" style="border:1px solid #d1d5db;border-radius:.5rem;padding:.4rem .6rem;font-size:.85rem;">
                <option value="">All Periods</option>
                @foreach ($periods as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
            <select wire:model.live="campus" style="border:1px solid #d1d5db;border-radius:.5rem;padding:.4rem .6rem;font-size:.85rem;">
                <option value="">All Campuses</option>
                <option value="sd">SD</option><option value="smp">SMP</option><option value="sma">SMA</option><option value="int">Int'l</option>
            </select>
            <input wire:model.live.debounce.400ms="grade" placeholder="Grade" style="border:1px solid #d1d5db;border-radius:.5rem;padding:.4rem .6rem;font-size:.85rem;width:90px;" />
            <input wire:model.live.debounce.400ms="q" placeholder="Search name or code…" style="border:1px solid #d1d5db;border-radius:.5rem;padding:.4rem .75rem;font-size:.85rem;flex:1;min-width:200px;" />
            <button wire:click="resetFilters" style="background:#f3f4f6;border:0;border-radius:.5rem;padding:.4rem .8rem;font-size:.8rem;cursor:pointer;">Reset</button>
        </div>
    </div>

    {{-- Kanban --}}
    <div style="display:flex;gap:.85rem;overflow-x:auto;padding-bottom:1rem;">
        @foreach ($columns as $key => $col)
            <div style="flex:0 0 295px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:.75rem;padding:.75rem;display:flex;flex-direction:column;max-height:78vh;">
                {{-- Column header --}}
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.6rem;padding-bottom:.5rem;border-bottom:2px solid {{ $col['color'] }};">
                    <div style="display:flex;align-items:center;gap:.4rem;">
                        <span style="width:.55rem;height:.55rem;border-radius:9999px;background:{{ $col['color'] }};"></span>
                        <span style="font-weight:700;color:#111827;font-size:.85rem;">{{ $col['label'] }}</span>
                    </div>
                    <span style="background:{{ $col['color'] }};color:#fff;padding:.1rem .55rem;border-radius:9999px;font-size:.72rem;font-weight:700;">{{ $col['count'] }}</span>
                </div>
                {{-- Cards --}}
                <div style="display:flex;flex-direction:column;gap:.5rem;overflow-y:auto;flex:1;">
                    @forelse ($col['records'] as $r)
                        @php
                            $score = $r->placement_score;
                            $scoreColor = $score === null ? '#9ca3af' : ($score >= 70 ? '#10b981' : ($score < 50 ? '#ef4444' : '#f59e0b'));
                            $initials = collect(explode(' ', $r->name))->map(fn($p)=>mb_substr($p,0,1))->take(2)->implode('');
                        @endphp
                        <div style="background:#fff;border:1px solid #e5e7eb;border-left:4px solid {{ $col['color'] }};border-radius:.55rem;padding:.55rem .65rem;font-size:.78rem;">
                            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.4rem;">
                                <div style="display:flex;gap:.5rem;min-width:0;flex:1;">
                                    <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,{{ $col['color'] }},#a78bfa);color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;flex-shrink:0;">{{ $initials }}</div>
                                    <div style="min-width:0;flex:1;">
                                        <a href="{{ \App\Filament\Principal\Resources\ApplicationResource::getUrl('view', ['record'=>$r->id]) }}"
                                           style="color:#111827;font-weight:600;text-decoration:none;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $r->name }}</a>
                                        <div style="color:#6b7280;font-size:.7rem;">{{ $r->code }} · {{ strtoupper($r->campus) }} {{ $r->grade }}</div>
                                    </div>
                                </div>
                                {{ ($this->cardAction)(['id' => $r->id]) }}
                            </div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:.45rem;">
                                <div style="display:flex;gap:.3rem;align-items:center;font-size:.7rem;">
                                    @if ($r->is_teacher_child)
                                        <span title="Teacher child" style="color:#f59e0b;">★</span>
                                    @endif
                                    @if ($r->is_existing_student)
                                        <span title="Existing student" style="background:#dbeafe;color:#1e40af;padding:0 .35rem;border-radius:9999px;font-size:.6rem;font-weight:700;">EXIST</span>
                                    @endif
                                    <span style="color:{{ $r->payment_status === 'paid' ? '#10b981' : '#f59e0b' }};font-weight:700;">●</span>
                                    <span style="color:#6b7280;">{{ ucfirst($r->payment_status) }}</span>
                                </div>
                                @if ($score !== null)
                                    <span style="background:{{ $scoreColor }};color:#fff;padding:.1rem .45rem;border-radius:.4rem;font-size:.7rem;font-weight:700;">{{ (int)$score }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center;color:#9ca3af;font-size:.78rem;padding:1.5rem .5rem;">No applicants</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
