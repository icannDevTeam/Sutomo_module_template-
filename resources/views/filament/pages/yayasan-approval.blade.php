<x-filament-panels::page>
    {{-- KPI strip --}}
    <div class="sp-kpis">
        <div class="sp-kpi" style="border-left:3px solid #be123c;">
            <div class="sp-kpi-label">Awaiting decision</div>
            <div class="sp-kpi-value" style="color:#be123c;">{{ $kpi['awaiting'] }}</div>
        </div>
        <div class="sp-kpi" style="border-left:3px solid #10b981;">
            <div class="sp-kpi-label">Approved (30 d)</div>
            <div class="sp-kpi-value" style="color:#10b981;">{{ $kpi['approved'] }}</div>
        </div>
        <div class="sp-kpi" style="border-left:3px solid #f59e0b;">
            <div class="sp-kpi-label">Returned (30 d)</div>
            <div class="sp-kpi-value" style="color:#f59e0b;">{{ $kpi['returned'] }}</div>
        </div>
        <div class="sp-kpi" style="border-left:3px solid #ef4444;">
            <div class="sp-kpi-label">Rejected (30 d)</div>
            <div class="sp-kpi-value" style="color:#ef4444;">{{ $kpi['rejected'] }}</div>
        </div>
    </div>

    {{-- Monitoring banner --}}
    <div class="sp-card" style="margin-top:var(--sp-gap);border-left:4px solid #4338ca;background:linear-gradient(90deg,#eef2ff 0%, #fff 60%);">
        <div style="display:flex;gap:.85rem;align-items:flex-start;">
            <div style="width:36px;height:36px;border-radius:.6rem;background:#4338ca;color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <x-filament::icon icon="heroicon-o-eye" style="width:20px;height:20px;" />
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-weight:700;color:#0f172a;">Principal monitoring view</div>
                <div style="font-size:.84rem;color:#475569;margin-top:.2rem;line-height:1.5;">
                    Candidates listed here have completed all assessment stages and are awaiting <b>Yayasan Board</b> approval. You can attach a recommendation note. You will be notified automatically when the board records a decision.
                </div>
            </div>
        </div>
    </div>

    {{-- Approval queue --}}
    <div style="margin-top:var(--sp-gap);display:flex;flex-direction:column;gap:var(--sp-gap);">
        @forelse ($queue as $c)
            @php
                $v = $c->vacancy;
                $score = collect($c->meta['scores'] ?? [])->avg();
                $written = $c->meta['scores']['written'] ?? null;
                $interview = $c->meta['scores']['interview'] ?? null;
                $psycho = $c->meta['psycho_status'] ?? 'pending';
                $medical = $c->meta['medical_status'] ?? 'pending';
                $initials = collect(explode(' ', $c->name))->map(fn($p)=>mb_substr($p,0,1))->take(2)->implode('');
                $notes = $c->meta['yayasan_notes'] ?? [];
            @endphp
            <div class="sp-card sp-yay-card">
                {{-- Header row --}}
                <div class="sp-yay-head">
                    <div class="sp-yay-avatar">{{ $initials }}</div>
                    <div class="sp-yay-id">
                        <div class="sp-yay-name">{{ $c->name }}</div>
                        <div class="sp-yay-meta">
                            {{ $v?->title ?? 'Unassigned vacancy' }}
                            @if ($v?->campus) · {{ strtoupper($v->campus) }} @endif
                            @if ($c->years_experience) · {{ $c->years_experience }} yrs experience @endif
                        </div>
                        <div class="sp-yay-pills">
                            <span class="sp-pill sp-pill-green">Strong Hire</span>
                            <span class="sp-pill sp-pill-blue">All checks passed</span>
                            @if ($score)
                                <span class="sp-pill sp-pill-indigo">Score {{ (int) $score }}/100</span>
                            @endif
                        </div>
                    </div>
                    <div class="sp-yay-actions">
                        {{ ($this->noteAction)(['id' => $c->id]) }}
                        {{ ($this->returnAction)(['id' => $c->id]) }}
                        {{ ($this->rejectAction)(['id' => $c->id]) }}
                        {{ ($this->approveAction)(['id' => $c->id]) }}
                    </div>
                </div>

                <hr class="sp-yay-divider">

                {{-- Score grid --}}
                <div class="sp-yay-scores">
                    <div>
                        <div class="sp-yay-score-lbl">Written</div>
                        <div class="sp-yay-score-val">{{ $written ?? '—' }}<span>/100</span></div>
                    </div>
                    <div>
                        <div class="sp-yay-score-lbl">Interview</div>
                        <div class="sp-yay-score-val">{{ $interview ?? '—' }}<span>/100</span></div>
                    </div>
                    <div>
                        <div class="sp-yay-score-lbl">Psycho</div>
                        <span class="sp-pill {{ $psycho === 'passed' ? 'sp-pill-green' : 'sp-pill-amber' }}">{{ ucfirst($psycho) }}</span>
                    </div>
                    <div>
                        <div class="sp-yay-score-lbl">Medical</div>
                        <span class="sp-pill {{ $medical === 'passed' ? 'sp-pill-green' : 'sp-pill-amber' }}">{{ ucfirst($medical) }}</span>
                    </div>
                </div>

                <hr class="sp-yay-divider">

                {{-- Recommendation + risk --}}
                <div class="sp-yay-notes-grid">
                    <div class="sp-yay-note">
                        <div class="sp-yay-note-h">Principal recommendation</div>
                        @if (count($notes))
                            @foreach (array_slice($notes, -2) as $n)
                                <div class="sp-yay-note-item">
                                    <p>{{ $n['note'] }}</p>
                                    <div class="sp-yay-note-by">— {{ $n['by'] }} · {{ \Carbon\Carbon::parse($n['at'])->diffForHumans() }}</div>
                                </div>
                            @endforeach
                        @else
                            <p class="sp-yay-note-empty">No note yet. Use <b>Add note</b> to attach context for the Yayasan board.</p>
                        @endif
                    </div>
                    <div class="sp-yay-note">
                        <div class="sp-yay-note-h">Risk indicators</div>
                        <div class="sp-yay-pills">
                            <span class="sp-pill sp-pill-green">No flags</span>
                            <span class="sp-pill sp-pill-green">Background OK</span>
                            <span class="sp-pill sp-pill-green">References verified</span>
                        </div>
                        <div class="sp-yay-meta" style="margin-top:.75rem;">
                            Queued for board {{ $c->updated_at?->diffForHumans() }}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="sp-card" style="text-align:center;padding:3rem 1.5rem;">
                <x-filament::icon icon="heroicon-o-check-badge" style="width:48px;height:48px;color:#94a3b8;margin:0 auto;" />
                <div style="font-weight:700;color:#0f172a;margin-top:.85rem;">Inbox empty</div>
                <div style="font-size:.85rem;color:#64748b;margin-top:.25rem;">No candidates currently awaiting Yayasan board approval.</div>
            </div>
        @endforelse
    </div>

    {{-- Recent decisions trail --}}
    @if ($recent->isNotEmpty())
        <div class="sp-card" style="margin-top:var(--sp-gap);">
            <div class="sp-card-h">Recent board decisions</div>
            <div class="sp-card-sub">Last 8 actions recorded in the audit log.</div>
            <div style="display:flex;flex-direction:column;gap:.4rem;">
                @foreach ($recent as $log)
                    @php
                        $color = str_contains($log->action, 'approved') ? '#10b981'
                               : (str_contains($log->action, 'rejected') ? '#ef4444' : '#f59e0b');
                    @endphp
                    <div class="sp-row">
                        <div class="sp-row-main">
                            <span class="sp-row-name">{{ $log->detail }}</span>
                            <span class="sp-row-sub">{{ $log->user_name }} · {{ $log->occurred_at?->diffForHumans() }}</span>
                        </div>
                        <span style="width:8px;height:8px;border-radius:9999px;background:{{ $color }};flex-shrink:0;"></span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <x-filament-actions::modals />
</x-filament-panels::page>
