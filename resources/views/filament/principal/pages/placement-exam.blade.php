<x-filament-panels::page>
    {{-- Period selector + Setup --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:.75rem;padding:1rem 1.25rem;margin-bottom:1rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;margin-bottom:.85rem;">
            <div>
                <div style="font-size:.7rem;letter-spacing:.08em;color:#6b7280;text-transform:uppercase;font-weight:600;">Enrollment Period</div>
                <select wire:model.live="selected_period" style="margin-top:.25rem;border:1px solid #d1d5db;border-radius:.5rem;padding:.45rem .7rem;font-size:.9rem;font-weight:600;min-width:280px;">
                    <option value="">Select period…</option>
                    @foreach ($periods as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            @if ($period)
                <div style="text-align:right;font-size:.8rem;color:#374151;">
                    <div><b>Status:</b>
                        <span style="padding:.1rem .55rem;border-radius:9999px;font-size:.7rem;font-weight:700;background:{{ $period->status==='open'?'#d1fae5':'#f3f4f6' }};color:{{ $period->status==='open'?'#065f46':'#374151' }};">{{ strtoupper($period->status) }}</span>
                    </div>
                    <div style="margin-top:.2rem;color:#6b7280;">Quota: {{ $period->quota }} · Pass ≥ {{ $period->pass_threshold }} · Fail &lt; {{ $period->fail_threshold }}</div>
                </div>
            @endif
        </div>

        @if ($period)
            <form wire:submit="saveExamSetup">
                {{ $this->examSetupForm }}
                <div style="margin-top:.85rem;">
                    <button type="submit" style="background:#4338ca;color:#fff;border:0;padding:.55rem 1.1rem;border-radius:.55rem;font-weight:600;cursor:pointer;font-size:.85rem;">
                        Save Exam Setup
                    </button>
                </div>
            </form>
        @endif
    </div>

    {{-- KPI strip --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.75rem;margin-bottom:1rem;">
        @foreach ([
            ['Eligible (paid)', $kpi['eligible'], '#6b7280'],
            ['Scheduled', $kpi['scheduled'], '#0ea5e9'],
            ['Passed', $kpi['passed'], '#10b981'],
            ['Failed', $kpi['failed'], '#ef4444'],
            ['Avg Score', $kpi['avg'] ?: '—', '#4338ca'],
        ] as [$label, $value, $color])
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:.7rem;padding:.9rem 1rem;">
                <div style="font-size:.7rem;letter-spacing:.08em;color:#6b7280;text-transform:uppercase;font-weight:600;">{{ $label }}</div>
                <div style="font-size:1.55rem;font-weight:800;color:{{ $color }};margin-top:.15rem;">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    {{-- 3-column workspace --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:.85rem;">
        {{-- Eligible --}}
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:.75rem;padding:1rem;">
            <div style="font-weight:700;margin-bottom:.5rem;color:#111827;">Eligible · Awaiting Schedule</div>
            <div style="font-size:.78rem;color:#6b7280;margin-bottom:.6rem;">Payment-confirmed applicants. Use header action <b>Schedule Eligible</b> to set exam_date in bulk.</div>
            <div style="display:flex;flex-direction:column;gap:.35rem;max-height:340px;overflow-y:auto;">
                @forelse ($eligible as $r)
                    <div style="display:flex;justify-content:space-between;border:1px solid #f3f4f6;border-radius:.45rem;padding:.45rem .6rem;font-size:.8rem;">
                        <span>{{ $r->name }} <span style="color:#9ca3af;">· {{ strtoupper($r->campus) }} {{ $r->grade }}</span></span>
                        <span style="color:#9ca3af;">{{ $r->code }}</span>
                    </div>
                @empty
                    <div style="color:#9ca3af;text-align:center;padding:1rem;">No eligible applicants</div>
                @endforelse
            </div>
        </div>

        {{-- Awaiting score --}}
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:.75rem;padding:1rem;">
            <div style="font-weight:700;margin-bottom:.5rem;color:#111827;">Awaiting Score Entry</div>
            <div style="font-size:.78rem;color:#6b7280;margin-bottom:.6rem;">Enter score → status auto-flips per period thresholds.</div>
            <div style="display:flex;flex-direction:column;gap:.4rem;max-height:340px;overflow-y:auto;">
                @forelse ($awaitScore as $r)
                    <div style="display:flex;justify-content:space-between;align-items:center;border:1px solid #f3f4f6;border-radius:.45rem;padding:.45rem .6rem;font-size:.8rem;">
                        <div>
                            <div style="font-weight:600;">{{ $r->name }}</div>
                            <div style="color:#9ca3af;font-size:.72rem;">{{ $r->code }} · {{ $r->exam_date?->format('d M') ?? '—' }}</div>
                        </div>
                        {{ ($this->enterScoreAction)(['id' => $r->id, 'score' => $r->placement_score]) }}
                    </div>
                @empty
                    <div style="color:#9ca3af;text-align:center;padding:1rem;">All scores entered</div>
                @endforelse
            </div>
        </div>

        {{-- Recent results --}}
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:.75rem;padding:1rem;">
            <div style="font-weight:700;margin-bottom:.5rem;color:#111827;">Recent Results</div>
            <div style="font-size:.78rem;color:#6b7280;margin-bottom:.6rem;">Latest scored applicants.</div>
            <div style="display:flex;flex-direction:column;gap:.4rem;max-height:340px;overflow-y:auto;">
                @forelse ($scored as $r)
                    @php $sc = (float)$r->placement_score; $col = $sc >= 70 ? '#10b981' : ($sc < 50 ? '#ef4444' : '#f59e0b'); @endphp
                    <div style="display:flex;justify-content:space-between;align-items:center;border:1px solid #f3f4f6;border-radius:.45rem;padding:.45rem .6rem;font-size:.8rem;">
                        <div>
                            <div style="font-weight:600;">{{ $r->name }}</div>
                            <div style="color:#9ca3af;font-size:.72rem;">{{ ucfirst($r->status) }}</div>
                        </div>
                        <span style="background:{{ $col }};color:#fff;padding:.2rem .55rem;border-radius:.45rem;font-weight:700;font-size:.78rem;">{{ (int)$sc }}</span>
                    </div>
                @empty
                    <div style="color:#9ca3af;text-align:center;padding:1rem;">No results yet</div>
                @endforelse
            </div>
        </div>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
