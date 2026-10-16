<x-filament-panels::page>
@php
    $scoreColor = function(?int $s): string {
        if ($s === null) return '#94a3b8';
        if ($s >= 90) return '#059669';
        if ($s >= 80) return '#0284c7';
        if ($s >= 70) return '#d97706';
        return '#dc2626';
    };
    $scoreLabel = function(?int $s): string {
        if ($s === null) return '—';
        if ($s >= 90) return 'Excellent';
        if ($s >= 80) return 'Good';
        if ($s >= 70) return 'Acceptable';
        return 'Below Bar';
    };
    $avg = function(App\Models\Candidate $c): ?float {
        $vals = array_filter([$c->score_written, $c->score_interview, $c->score_micro], fn($v) => $v !== null);
        return count($vals) ? round(array_sum($vals) / count($vals), 1) : null;
    };
@endphp

<style>
.ha-layout { display:grid; grid-template-columns:1fr; gap:1.5rem; }
@media (min-width:1100px) { .ha-layout { grid-template-columns:360px 1fr; align-items:start; } }
.ha-list  { display:flex; flex-direction:column; gap:.5rem; }
.ha-card  {
    background:#fff; border:1.5px solid #e2e8f0; border-radius:12px;
    padding:1rem 1.1rem; cursor:pointer;
    transition:border-color .15s,box-shadow .15s;
    display:flex; flex-direction:column; gap:.55rem;
}
.ha-card:hover { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.1); }
.ha-card.is-selected { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.12); background:#fafaff; }
.ha-card.is-hold { border-left:4px solid #d97706; }
.ha-card.is-high { border-left:4px solid #6366f1; }
.ha-card__top { display:flex; justify-content:space-between; align-items:flex-start; gap:.5rem; }
.ha-card__name { font-weight:700; font-size:.95rem; color:#0f172a; }
.ha-card__pos  { font-size:.77rem; color:#64748b; margin-top:.1rem; }
.ha-priority   { font-size:.68rem; font-weight:700; padding:.2rem .5rem; border-radius:9999px; background:#eef2ff; color:#4338ca; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap; }
.ha-priority.hold { background:#fef3c7; color:#92400e; }
.ha-scores { display:flex; gap:.85rem; flex-wrap:wrap; }
.ha-score { display:flex; flex-direction:column; align-items:center; gap:.1rem; }
.ha-score__val { font-size:1.05rem; font-weight:800; line-height:1; }
.ha-score__lbl { font-size:.66rem; color:#94a3b8; text-transform:uppercase; letter-spacing:.04em; }
.ha-avg { display:flex; align-items:center; gap:.35rem; font-size:.8rem; font-weight:700; }
.ha-avg__ring { width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.78rem; font-weight:800; color:#fff; }
/* Detail */
.ha-detail { background:#fff; border:1.5px solid #e2e8f0; border-radius:14px; overflow:hidden; position:sticky; top:1.5rem; }
.ha-detail__header { background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%); color:#fff; padding:1.4rem 1.5rem 1.2rem; }
.ha-detail__name { font-size:1.2rem; font-weight:800; line-height:1.2; }
.ha-detail__sub  { font-size:.82rem; opacity:.85; margin-top:.3rem; }
.ha-detail__body { padding:1.25rem 1.5rem; display:flex; flex-direction:column; gap:1.1rem; }
.ha-score-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:.75rem; }
.ha-score-cell { background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:.75rem .6rem; text-align:center; }
.ha-score-cell__val   { font-size:1.5rem; font-weight:800; line-height:1; }
.ha-score-cell__lbl   { font-size:.7rem; color:#64748b; margin-top:.2rem; }
.ha-score-cell__grade { font-size:.68rem; font-weight:700; margin-top:.15rem; }
.ha-meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:.5rem; font-size:.82rem; }
.ha-meta-row   { display:flex; flex-direction:column; gap:.1rem; }
.ha-meta-row__k { color:#94a3b8; font-size:.72rem; text-transform:uppercase; letter-spacing:.04em; }
.ha-meta-row__v { color:#0f172a; font-weight:600; }
.ha-section-hd { font-size:.72rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.06em; margin-bottom:.6rem; }
.ha-actions { display:flex; flex-direction:column; gap:.6rem; }
.ha-btn { width:100%; padding:.75rem 1rem; border-radius:9px; font-size:.9rem; font-weight:700; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:.5rem; transition:opacity .15s,transform .1s; }
.ha-btn:active { transform:scale(.98); }
.ha-btn--approve { background:#059669; color:#fff; }
.ha-btn--approve:hover { background:#047857; }
.ha-btn--reject  { background:#fff; color:#dc2626; border:1.5px solid #fca5a5; }
.ha-btn--reject:hover  { background:#fef2f2; }
.ha-btn--hold    { background:#fff; color:#92400e; border:1.5px solid #fcd34d; }
.ha-btn--hold:hover    { background:#fef3c7; }
.ha-btn--resume  { background:#fff; color:#6366f1; border:1.5px solid #c7d2fe; }
.ha-btn--resume:hover  { background:#eef2ff; }
/* Tabs */
.ha-tabs { display:flex; gap:0; border-bottom:2px solid #e2e8f0; margin-bottom:1.25rem; }
.ha-tab  { padding:.6rem 1.1rem; font-size:.86rem; font-weight:600; color:#64748b; cursor:pointer; border:none; background:none; border-bottom:2.5px solid transparent; margin-bottom:-2px; transition:color .15s; }
.ha-tab.active { color:#6366f1; border-bottom-color:#6366f1; }
/* Decided */
.hd-list { display:flex; flex-direction:column; gap:.6rem; }
.hd-card { background:#fff; border:1.5px solid #e2e8f0; border-radius:10px; padding:.9rem 1.1rem; display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap; }
.hd-decision { font-size:.72rem; font-weight:700; padding:.2rem .6rem; border-radius:9999px; text-transform:uppercase; letter-spacing:.04em; }
.hd-decision.approved { background:#d1fae5; color:#065f46; }
.hd-decision.rejected { background:#fee2e2; color:#991b1b; }
/* Modal */
.ha-modal-backdrop { position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:50; display:flex; align-items:center; justify-content:center; padding:1rem; }
.ha-modal { background:#fff; border-radius:16px; width:100%; max-width:460px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,.2); }
.ha-modal__header { background:#fffbeb; padding:1.1rem 1.25rem; border-bottom:1px solid #fde68a; }
.ha-modal__title  { font-weight:700; font-size:1rem; color:#92400e; }
.ha-modal__body   { padding:1.25rem; display:flex; flex-direction:column; gap:.75rem; }
.ha-modal__footer { padding:.9rem 1.25rem; border-top:1px solid #f1f5f9; display:flex; justify-content:flex-end; gap:.5rem; }
</style>

{{-- Tab bar --}}
<div class="ha-tabs" id="ha-tabbar">
    <button class="ha-tab active" data-pane="ha-pending">
        Pending Review
        @if($pending->count() + $held->count() > 0)
            <span style="background:#6366f1;color:#fff;border-radius:9999px;font-size:.7rem;padding:.1rem .45rem;margin-left:.3rem;">{{ $pending->count() + $held->count() }}</span>
        @endif
    </button>
    <button class="ha-tab" data-pane="ha-decided">
        Decided
        <span style="background:#e2e8f0;color:#475569;border-radius:9999px;font-size:.7rem;padding:.1rem .45rem;margin-left:.3rem;">{{ $approved->count() + $rejected->count() }}</span>
    </button>
</div>

{{-- PENDING PANE --}}
<div id="ha-pending">
    @if($pending->isEmpty() && $held->isEmpty())
        <div style="text-align:center;padding:3rem 1rem;">
            <div style="font-size:2.5rem;margin-bottom:.75rem;">🎉</div>
            <div style="font-weight:700;font-size:1.05rem;color:#475569;">No candidates awaiting review</div>
            <div style="font-size:.85rem;color:#94a3b8;margin-top:.3rem;">All hiring candidates have been reviewed by the board.</div>
        </div>
    @else
        <div class="ha-layout" x-data="{ sel: {{ ($pending->merge($held))->first()?->id ?? 'null' }} }">
            {{-- Left list --}}
            <div>
                @if($pending->isNotEmpty())
                <div style="font-size:.74rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.6rem;">Awaiting Decision · {{ $pending->count() }}</div>
                <div class="ha-list">
                    @foreach($pending as $c)
                    @php $cavg = $avg($c); @endphp
                    <div class="ha-card {{ $c->priority === 'high' ? 'is-high' : '' }}"
                        :class="{ 'is-selected': sel === {{ $c->id }} }"
                        x-on:click="sel = {{ $c->id }}">
                        <div class="ha-card__top">
                            <div>
                                <div class="ha-card__name">{{ $c->name }}</div>
                                <div class="ha-card__pos">{{ $c->vacancy?->title ?? '—' }}@if($c->vacancy?->campus) · {{ strtoupper($c->vacancy->campus) }}@endif</div>
                            </div>
                            @if($c->priority === 'high')<span class="ha-priority">Priority</span>@endif
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;">
                            <div class="ha-scores">
                                @foreach(['W' => $c->score_written, 'I' => $c->score_interview, 'M' => $c->score_micro] as $lbl => $val)
                                <div class="ha-score">
                                    <span class="ha-score__val" style="color:{{ $scoreColor($val) }}">{{ $val ?? '—' }}</span>
                                    <span class="ha-score__lbl">{{ $lbl }}</span>
                                </div>
                                @endforeach
                            </div>
                            @if($cavg)
                            <div class="ha-avg">
                                <div class="ha-avg__ring" style="background:{{ $scoreColor((int)$cavg) }}">{{ $cavg }}</div>
                                <span style="color:#64748b;font-size:.75rem;">avg</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                @if($held->isNotEmpty())
                <div style="font-size:.74rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin:.9rem 0 .6rem;">On Hold · {{ $held->count() }}</div>
                <div class="ha-list">
                    @foreach($held as $c)
                    <div class="ha-card is-hold"
                        :class="{ 'is-selected': sel === {{ $c->id }} }"
                        x-on:click="sel = {{ $c->id }}">
                        <div class="ha-card__top">
                            <div>
                                <div class="ha-card__name">{{ $c->name }}</div>
                                <div class="ha-card__pos">{{ $c->vacancy?->title ?? '—' }}</div>
                            </div>
                            <span class="ha-priority hold">On Hold</span>
                        </div>
                        @if(!empty($c->meta['yayasan']['hold_note']))
                        <div style="font-size:.78rem;color:#92400e;background:#fef3c7;border-radius:6px;padding:.35rem .6rem;">{{ $c->meta['yayasan']['hold_note'] }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Right detail --}}
            <div>
                @foreach($pending->merge($held) as $c)
                @php
                    $cavg  = $avg($c);
                    $isHeld = ($c->meta['yayasan']['status'] ?? '') === 'on_hold';
                @endphp
                <div x-show="sel === {{ $c->id }}" x-cloak class="ha-detail" wire:key="detail-{{ $c->id }}">
                    <div class="ha-detail__header">
                        <div class="ha-detail__name">{{ $c->name }}</div>
                        <div class="ha-detail__sub">
                            {{ $c->vacancy?->title ?? 'Unknown Position' }}
                            @if($c->vacancy?->campus) · {{ strtoupper($c->vacancy->campus) }}@endif
                            @if($c->priority === 'high') &nbsp;&middot; Priority
@endif
                        </div>
                    </div>
                    <div class="ha-detail__body">
                        @php
                            $subjects2 = is_array($c->subjects) ? $c->subjects : json_decode($c->subjects ?? '[]', true);
                            $certifs2  = is_array($c->certifications) ? $c->certifications : json_decode($c->certifications ?? '[]', true);
                            $langs2    = is_array($c->languages) ? $c->languages : json_decode($c->languages ?? '[]', true);
                            $pastSch2  = is_array($c->past_schools) ? $c->past_schools : json_decode($c->past_schools ?? '[]', true);
                            $psycho2   = $c->meta['psycho'] ?? null;
                            $medical2  = $c->meta['medical'] ?? null;
                            $cvPath2   = $c->meta['cv_path'] ?? $c->cv_url ?? null;
                            $transcPath2 = $c->meta['transcript_path'] ?? null;
                            $portPath2   = $c->meta['portfolio_path'] ?? null;
                            $statusBg  = fn($s) => match($s ?? '') { 'passed' => '#d1fae5', 'failed' => '#fee2e2', default => '#f1f5f9' };
                            $statusFg  = fn($s) => match($s ?? '') { 'passed' => '#065f46', 'failed' => '#991b1b', default => '#475569' };
                            $statusLbl = fn($s) => match($s ?? '') { 'passed' => 'Passed', 'failed' => 'Failed', 'pending' => 'Pending', default => 'Not yet' };
                        @endphp

                        {{-- Assessment Scores --}}
                        <div>
                            <div class="ha-section-hd">Assessment Scores</div>
                            <div class="ha-score-grid">
                                @foreach(['Written' => $c->score_written, 'Interview' => $c->score_interview, 'Micro-Teaching' => $c->score_micro] as $lbl => $val)
                                <div class="ha-score-cell">
                                    <div class="ha-score-cell__val" style="color:{{ $scoreColor($val) }}">{{ $val ?? '—' }}</div>
                                    <div class="ha-score-cell__lbl">{{ $lbl }}</div>
                                    @if($val !== null)
                                    <div class="ha-score-cell__grade" style="color:{{ $scoreColor($val) }}">{{ $scoreLabel($val) }}</div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @if($cavg)
                            <div style="margin-top:.6rem;text-align:center;font-size:.82rem;color:#475569;">
                                Average: <strong style="color:{{ $scoreColor((int)$cavg) }}">{{ $cavg }}/100</strong> — {{ $scoreLabel((int)$cavg) }}
                            </div>
                            @endif
                        </div>

                        {{-- Principal Notes --}}
                        @if($c->notes)
                        <div>
                            <div class="ha-section-hd">Principal Notes</div>
                            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-left:3px solid #6366f1;border-radius:8px;padding:.75rem 1rem;font-size:.84rem;color:#334155;line-height:1.6;">
                                {{ $c->notes }}
                            </div>
                        </div>
                        @endif

                        {{-- Profile --}}
                        <div>
                            <div class="ha-section-hd">Candidate Profile</div>
                            <div class="ha-meta-grid">
                                <div class="ha-meta-row"><span class="ha-meta-row__k">Full Name</span><span class="ha-meta-row__v">{{ $c->name }}</span></div>
                                <div class="ha-meta-row"><span class="ha-meta-row__k">Age</span><span class="ha-meta-row__v">{{ $c->age ? $c->age . ' yrs' : '—' }}</span></div>
                                <div class="ha-meta-row"><span class="ha-meta-row__k">Education</span><span class="ha-meta-row__v">{{ $c->education ?? '—' }}</span></div>
                                <div class="ha-meta-row"><span class="ha-meta-row__k">Experience</span><span class="ha-meta-row__v">{{ $c->years ? $c->years . ' yr' . ($c->years > 1 ? 's' : '') : '—' }}</span></div>
                                <div class="ha-meta-row"><span class="ha-meta-row__k">City</span><span class="ha-meta-row__v">{{ $c->city ?? '—' }}</span></div>
                                <div class="ha-meta-row"><span class="ha-meta-row__k">Phone</span><span class="ha-meta-row__v">{{ $c->phone ?? '—' }}</span></div>
                                <div class="ha-meta-row"><span class="ha-meta-row__k">Email</span><span class="ha-meta-row__v" style="word-break:break-all;">{{ $c->email ?? '—' }}</span></div>
                                <div class="ha-meta-row"><span class="ha-meta-row__k">Available</span><span class="ha-meta-row__v">{{ $c->availability ? \Carbon\Carbon::parse($c->availability)->format('d M Y') : '—' }}</span></div>
                                <div class="ha-meta-row"><span class="ha-meta-row__k">Desired Salary</span><span class="ha-meta-row__v">{{ $c->desired_salary ? 'Rp ' . number_format((int)$c->desired_salary, 0, ',', '.') : '—' }}</span></div>
                                <div class="ha-meta-row"><span class="ha-meta-row__k">Preferred Campus</span><span class="ha-meta-row__v">{{ $c->preferred_campus ? strtoupper($c->preferred_campus) : '—' }}</span></div>
                            </div>
                        </div>

                        {{-- Subjects & Teaching --}}
                        <div>
                            <div class="ha-section-hd">Subjects &amp; Teaching</div>
                            @if(count($subjects2))
                            <div style="display:flex;flex-wrap:wrap;gap:.35rem;margin-bottom:.5rem;">
                                @foreach($subjects2 as $idx => $sub)
                                <span style="padding:.25rem .65rem;border-radius:9999px;font-size:.75rem;font-weight:600;background:{{ $idx === 0 ? '#eef2ff' : '#f1f5f9' }};color:{{ $idx === 0 ? '#4338ca' : '#475569' }};border:1px solid {{ $idx === 0 ? '#c7d2fe' : '#e2e8f0' }}">
                                    {{ $sub }}{{ $idx === 0 ? ' (main)' : '' }}
                                </span>
                                @endforeach
                            </div>
                            @else
                            <span style="font-size:.82rem;color:#94a3b8;">No subjects listed</span>
                            @endif
                            @if(count($certifs2))
                            <div style="margin-top:.5rem;">
                                <div style="font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.3rem;">Certifications</div>
                                @foreach($certifs2 as $cert)
                                <div style="font-size:.8rem;color:#334155;display:flex;align-items:center;gap:.4rem;margin-bottom:.2rem;">
                                    <span style="color:#059669;font-size:.7rem;">&#10003;</span> {{ $cert }}
                                </div>
                                @endforeach
                            </div>
                            @endif
                            @if(count($langs2))
                            <div style="margin-top:.5rem;font-size:.8rem;color:#64748b;">
                                Languages: <strong style="color:#334155;">{{ implode(', ', $langs2) }}</strong>
                            </div>
                            @endif
                        </div>

                        {{-- School History --}}
                        @if($c->current_school || count($pastSch2))
                        <div>
                            <div class="ha-section-hd">School History</div>
                            @if($c->current_school)
                            <div style="display:flex;align-items:baseline;gap:.5rem;margin-bottom:.35rem;">
                                <span style="font-size:.7rem;font-weight:700;background:#d1fae5;color:#065f46;padding:.15rem .5rem;border-radius:9999px;">Current</span>
                                <span style="font-size:.83rem;color:#0f172a;font-weight:600;">{{ $c->current_school }}</span>
                            </div>
                            @endif
                            @foreach($pastSch2 as $sch)
                            <div style="display:flex;align-items:baseline;gap:.5rem;margin-bottom:.25rem;">
                                <span style="font-size:.7rem;font-weight:600;color:#94a3b8;">Past</span>
                                <span style="font-size:.82rem;color:#475569;">{{ $sch }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        {{-- Assessments: Psycho + Medical --}}
                        <div>
                            <div class="ha-section-hd">Pre-Employment Assessments</div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem;">
                                @foreach(['Psycho Test' => $psycho2, 'Medical Check' => $medical2] as $testLbl => $test)
                                <div style="border:1.5px solid #e2e8f0;border-radius:10px;padding:.7rem .85rem;">
                                    <div style="font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">{{ $testLbl }}</div>
                                    @if($test)
                                    <div style="margin-top:.35rem;display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
                                        <span style="font-size:.85rem;font-weight:700;background:{{ $statusBg($test['status'] ?? '') }};color:{{ $statusFg($test['status'] ?? '') }};padding:.2rem .6rem;border-radius:9999px;">
                                            {{ $statusLbl($test['status'] ?? '') }}
                                        </span>
                                        @if(!empty($test['date']))
                                        <span style="font-size:.74rem;color:#94a3b8;">{{ \Carbon\Carbon::parse($test['date'])->format('d M Y') }}</span>
                                        @endif
                                    </div>
                                    @else
                                    <div style="font-size:.8rem;color:#94a3b8;margin-top:.3rem;">Not recorded</div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Documents --}}
                        <div>
                            <div class="ha-section-hd">Documents</div>
                            <div style="display:flex;flex-direction:column;gap:.45rem;">
                                @foreach(['CV / Resume' => $cvPath2, 'Academic Transcript' => $transcPath2, 'Portfolio' => $portPath2] as $docLbl => $docPath)
                                @if($docPath)
                                <div style="display:flex;align-items:center;justify-content:space-between;padding:.55rem .85rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                                    <div style="display:flex;align-items:center;gap:.5rem;font-size:.83rem;font-weight:600;color:#334155;">
                                        <svg style="width:16px;height:16px;color:#6366f1;flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        {{ $docLbl }}
                                    </div>
                                    <a href="{{ asset('storage/' . $docPath) }}" target="_blank" rel="noopener"
                                        style="font-size:.76rem;font-weight:700;color:#6366f1;text-decoration:none;padding:.25rem .65rem;border:1.5px solid #c7d2fe;border-radius:6px;background:#fff;">
                                        View / Download
                                    </a>
                                </div>
                                @else
                                <div style="display:flex;align-items:center;gap:.5rem;padding:.5rem .85rem;background:#fafafa;border:1px dashed #e2e8f0;border-radius:8px;font-size:.8rem;color:#94a3b8;">
                                    <svg style="width:14px;height:14px;flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    {{ $docLbl }} — not uploaded
                                </div>
                                @endif
                                @endforeach
                            </div>
                        </div>

                        @if($isHeld && !empty($c->meta['yayasan']['hold_note']))
                        <div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:.6rem .9rem;font-size:.82rem;color:#92400e;">
                            <strong>Hold reason:</strong> {{ $c->meta['yayasan']['hold_note'] }}
                        </div>
                        @endif
                        <div class="ha-actions">
                            @php $held2 = $isHeld; @endphp
                            @if(!$held2)
                                <button class="ha-btn ha-btn--approve"
                                    wire:click="approve({{ $c->id }})"
                                    wire:confirm="Approve {{ $c->name }} for {{ $c->vacancy?->title }}? This advances them to OPL and cannot be undone."
                                    wire:loading.attr="disabled" wire:target="approve({{ $c->id }})">
                                    <svg style="width:18px;height:18px;flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span wire:loading.remove wire:target="approve({{ $c->id }})">Approve — Advance to OPL</span>
                                    <span wire:loading wire:target="approve({{ $c->id }})">Processing…</span>
                                </button>
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
                                    <button class="ha-btn ha-btn--hold" wire:click="openHoldModal({{ $c->id }})">
                                        ⏸ Place on Hold
                                    </button>
                                    <button class="ha-btn ha-btn--reject"
                                        wire:click="reject({{ $c->id }})"
                                        wire:confirm="Reject {{ $c->name }}? This moves them out of the hiring pipeline."
                                        wire:loading.attr="disabled" wire:target="reject({{ $c->id }})">
                                        <span wire:loading.remove wire:target="reject({{ $c->id }})">✕ Reject</span>
                                        <span wire:loading wire:target="reject({{ $c->id }})">…</span>
                                    </button>
                                </div>
                            @endif
                            @if($held2)
                                <button class="ha-btn ha-btn--approve"
                                    wire:click="approve({{ $c->id }})"
                                    wire:confirm="Approve {{ $c->name }}? This advances them to OPL."
                                    wire:loading.attr="disabled">
                                    <svg style="width:18px;height:18px;flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Approve — Advance to OPL
                                </button>
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
                                    <button class="ha-btn ha-btn--resume" wire:click="resumeFromHold({{ $c->id }})">
                                        ▶ Return to Review
                                    </button>
                                    <button class="ha-btn ha-btn--reject"
                                        wire:click="reject({{ $c->id }})"
                                        wire:confirm="Reject {{ $c->name }}?"
                                        wire:loading.attr="disabled">
                                        ✕ Reject
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
                {{-- first-load placeholder --}}
                <div x-show="sel === null" class="ha-detail" style="padding:2.5rem 1.5rem;text-align:center;color:#94a3b8;">
                    <div style="font-size:2rem;margin-bottom:.6rem;">👈</div>
                    <div style="font-weight:600;font-size:.95rem;color:#475569;">Select a candidate</div>
                    <div style="font-size:.82rem;margin-top:.3rem;">Choose a candidate from the list to review scores and make a decision.</div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- DECIDED PANE --}}
<div id="ha-decided" style="display:none;">
    @if($approved->isEmpty() && $rejected->isEmpty())
        <div style="text-align:center;padding:3rem 1rem;color:#94a3b8;">
            <div style="font-weight:600;">No decisions recorded yet</div>
        </div>
    @else
        <div style="margin-bottom:.75rem;font-size:.82rem;color:#64748b;">
            {{ $approved->count() }} approved · {{ $rejected->count() }} rejected
        </div>
        <div class="hd-list">
            @foreach($approved->merge($rejected)->sortByDesc('updated_at') as $c)
            @php
                $dec  = ($c->meta['yayasan']['status'] ?? 'approved') === 'approved' ? 'approved' : 'rejected';
                $decDate = $c->meta['yayasan']['date'] ?? null;
                $decBy   = $c->meta['yayasan']['committee'] ?? $c->meta['yayasan']['by'] ?? null;
            @endphp
            <div class="hd-card">
                <div>
                    <div style="font-weight:700;font-size:.92rem;color:#0f172a;">{{ $c->name }}</div>
                    <div style="font-size:.78rem;color:#64748b;margin-top:.15rem;">{{ $c->vacancy?->title ?? '—' }}@if($c->vacancy?->campus) · {{ $c->vacancy->campus }}@endif</div>
                    @if($decBy || $decDate)
                    <div style="font-size:.74rem;color:#94a3b8;margin-top:.2rem;">
                        {{ $decBy ? 'by ' . $decBy : '' }}{{ $decDate ? ' · ' . \Carbon\Carbon::parse($decDate)->format('d M Y') : '' }}
                    </div>
                    @endif
                </div>
                <div style="display:flex;align-items:center;gap:.75rem;">
                    @if($c->score_written || $c->score_interview)
                    <div style="font-size:.78rem;color:#64748b;text-align:right;">
                        @if($c->score_written)<div>Written: <strong>{{ $c->score_written }}</strong></div>@endif
                        @if($c->score_interview)<div>Interview: <strong>{{ $c->score_interview }}</strong></div>@endif
                    </div>
                    @endif
                    <span class="hd-decision {{ $dec }}">{{ ucfirst($dec) }}</span>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Hold modal --}}
@if($holdCandidateId)
<div class="ha-modal-backdrop" wire:click.self="$set('holdCandidateId', null)">
    <div class="ha-modal">
        <div class="ha-modal__header">
            <div class="ha-modal__title">⏸ Place Candidate on Hold</div>
            <div style="font-size:.8rem;color:#92400e;margin-top:.2rem;">The candidate remains at the Yayasan stage. Record a reason so the board can follow up.</div>
        </div>
        <div class="ha-modal__body">
            <label style="font-size:.8rem;font-weight:600;color:#475569;display:block;margin-bottom:.35rem;">Hold reason <span style="color:#dc2626">*</span></label>
            <textarea wire:model="holdNote" rows="3"
                style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:.6rem .75rem;font:inherit;font-size:.87rem;resize:vertical;"
                placeholder="e.g. Waiting for reference letter, additional assessment needed…"></textarea>
        </div>
        <div class="ha-modal__footer">
            <button wire:click="$set('holdCandidateId', null)"
                style="padding:.55rem 1rem;border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;font-weight:600;font-size:.86rem;cursor:pointer;">Cancel</button>
            <button wire:click="submitHold" wire:loading.attr="disabled" wire:target="submitHold"
                style="padding:.55rem 1.25rem;border-radius:8px;background:#d97706;color:#fff;font-weight:700;font-size:.86rem;border:none;cursor:pointer;">
                <span wire:loading.remove wire:target="submitHold">Place on Hold</span>
                <span wire:loading wire:target="submitHold">Saving…</span>
            </button>
        </div>
    </div>
</div>
@endif

<script>
document.querySelectorAll('#ha-tabbar .ha-tab').forEach((tab, i) => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('#ha-tabbar .ha-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById('ha-pending').style.display = i === 0 ? '' : 'none';
        document.getElementById('ha-decided').style.display = i === 1 ? '' : 'none';
    });
});
</script>
</x-filament-panels::page>
