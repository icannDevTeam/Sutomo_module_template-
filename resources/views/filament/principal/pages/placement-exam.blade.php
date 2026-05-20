<x-filament-panels::page>
    {{-- Period selector + Setup --}}
    <div class="sp-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;">
            <div style="min-width:280px;">
                <div class="sp-kpi-label">Enrollment Period</div>
                <select wire:model.live="selected_period" style="margin-top:.35rem;border:1px solid #d1d5db;border-radius:.5rem;padding:.55rem .8rem;font-size:.9rem;font-weight:600;width:100%;max-width:380px;">
                    <option value="">Select period…</option>
                    @foreach ($periods as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            @if ($period)
                <div style="font-size:.82rem;color:#374151;text-align:right;">
                    <div style="display:flex;gap:.5rem;align-items:center;justify-content:flex-end;">
                        <b>Status:</b>
                        <span class="sp-pill {{ $period->status==='open' ? 'sp-pill-green' : 'sp-pill-gray' }}">{{ strtoupper($period->status) }}</span>
                    </div>
                    <div style="margin-top:.35rem;color:#6b7280;">Quota: {{ $period->quota }} · Pass ≥ {{ $period->pass_threshold }} · Fail &lt; {{ $period->fail_threshold }}</div>
                </div>
            @endif
        </div>

        @if ($period)
            <form wire:submit="saveExamSetup">
                {{ $this->examSetupForm }}
                <div style="margin-top:1rem;display:flex;justify-content:flex-end;">
                    <button type="submit" class="sp-btn">Save Exam Setup</button>
                </div>
            </form>
        @endif
    </div>

    {{-- KPI strip --}}
    <div class="sp-kpis" style="margin-top:var(--sp-gap);">
        @foreach ([
            ['Eligible (paid)', $kpi['eligible'], '#475569'],
            ['Scheduled', $kpi['scheduled'], '#0ea5e9'],
            ['Passed', $kpi['passed'], '#10b981'],
            ['Failed', $kpi['failed'], '#ef4444'],
            ['Avg Score', $kpi['avg'] ?: '—', '#4338ca'],
        ] as [$label, $value, $color])
            <div class="sp-kpi">
                <div class="sp-kpi-label">{{ $label }}</div>
                <div class="sp-kpi-value" style="color:{{ $color }};">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    {{-- 3-column workspace --}}
    <div class="sp-grid-3" style="margin-top:var(--sp-gap);">
        {{-- Eligible --}}
        <div class="sp-card">
            <div class="sp-card-h">Eligible · Awaiting Schedule</div>
            <div class="sp-card-sub">Payment-confirmed applicants. Use header action <b>Schedule Eligible</b> to set exam_date in bulk.</div>
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
        <div class="sp-card">
            <div class="sp-card-h">Awaiting Score Entry</div>
            <div class="sp-card-sub">Enter score → status auto-flips per period thresholds.</div>
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
        <div class="sp-card">
            <div class="sp-card-h">Recent Results</div>
            <div class="sp-card-sub">Latest scored applicants.</div>
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

    {{-- Exam Sessions & Supervisors --}}
    <div style="margin-top:var(--sp-gap);">
        {{ $this->table }}
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
