<x-filament-panels::page>
    @php
        /** @var \App\Models\Teacher $record */
        /** @var array $tabs */
        $statusColor = match ($record->status ?? null) {
            'permanent' => 'success', 'contract' => 'info', 'probation' => 'warning',
            'opl' => 'gray', 'leave' => 'warning', 'alumni' => 'danger', default => 'gray',
        };
    @endphp

    <style>
        .mt-header { display:flex; align-items:flex-start; gap:18px; padding:18px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; }
        .mt-avatar { width:72px; height:72px; border-radius:999px; background:linear-gradient(135deg,#6366f1,#a855f7); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:24px; flex:none; }
        .mt-id h2 { font-size:22px; font-weight:700; color:#0f172a; margin:0; }
        .mt-id .mt-sub { color:#64748b; font-size:13px; margin-top:4px; }
        .mt-id .mt-meta { display:flex; gap:14px; margin-top:8px; flex-wrap:wrap; font-size:12px; color:#475569; }
        .mt-id .mt-meta strong { color:#0f172a; }
        .mt-badge { display:inline-block; padding:3px 10px; border-radius:999px; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.05em; }
        .mt-badge--success { background:#dcfce7; color:#166534; }
        .mt-badge--info    { background:#dbeafe; color:#1e40af; }
        .mt-badge--warning { background:#fef3c7; color:#92400e; }
        .mt-badge--danger  { background:#fee2e2; color:#991b1b; }
        .mt-badge--gray    { background:#f1f5f9; color:#475569; }

        .mt-tabs { display:flex; gap:2px; margin-top:18px; border-bottom:1px solid #e5e7eb; overflow-x:auto; }
        .mt-tab { padding:10px 16px; font-size:13px; font-weight:500; color:#64748b; cursor:pointer; border:none; background:transparent; white-space:nowrap; border-bottom:2px solid transparent; }
        .mt-tab:hover { color:#0f172a; }
        .mt-tab--active { color:#6366f1; border-bottom-color:#6366f1; font-weight:600; }

        .mt-panel { background:#fff; border:1px solid #e5e7eb; border-top:none; border-radius:0 0 12px 12px; padding:20px; min-height:280px; }
        .mt-section-title { font-size:13px; font-weight:600; color:#0f172a; text-transform:uppercase; letter-spacing:.05em; margin:0 0 12px; }
        .mt-section + .mt-section { margin-top:24px; }

        .mt-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px 24px; font-size:13px; }
        .mt-grid > div > span:first-child { color:#64748b; display:block; font-size:11px; text-transform:uppercase; letter-spacing:.05em; }
        .mt-grid > div > span:last-child { color:#0f172a; font-weight:500; }

        .mt-table { width:100%; border-collapse:collapse; font-size:13px; }
        .mt-table th { text-align:left; padding:8px 12px; background:#f8fafc; color:#475569; font-weight:600; font-size:11px; text-transform:uppercase; letter-spacing:.05em; border-bottom:1px solid #e5e7eb; }
        .mt-table td { padding:10px 12px; border-bottom:1px solid #f1f5f9; vertical-align:top; }
        .mt-table tr:hover td { background:#f8fafc; }
        .mt-empty { color:#94a3b8; font-size:13px; text-align:center; padding:32px 20px; font-style:italic; }

        .mt-note { background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:12px 14px; margin-bottom:10px; }
        .mt-note--pinned { background:#fef3c7; border-color:#fbbf24; }
        .mt-note__head { display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; font-size:11px; color:#64748b; }
        .mt-note__body { font-size:14px; color:#0f172a; white-space:pre-wrap; line-height:1.5; }
        .mt-note__actions { display:flex; gap:8px; margin-top:8px; }
        .mt-note__btn { background:transparent; border:none; color:#6366f1; font-size:11px; cursor:pointer; padding:0; }
        .mt-note__btn--danger { color:#ef4444; }

        .mt-letter { border:1px solid #e5e7eb; border-radius:10px; padding:14px; margin-bottom:12px; background:#fff; }
        .mt-letter__head { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:8px; }
        .mt-letter__title { font-weight:600; font-size:14px; color:#0f172a; }
        .mt-letter__meta { font-size:11px; color:#64748b; margin-top:2px; }
        .mt-letter__body { font-size:13px; color:#334155; white-space:pre-wrap; line-height:1.5; padding:10px 0; border-top:1px dashed #e5e7eb; margin-top:6px; }
        .mt-letter__response { background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:10px; margin-top:10px; font-size:13px; color:#1e3a8a; white-space:pre-wrap; }
        .mt-letter__actions { display:flex; gap:8px; margin-top:10px; }
        .mt-btn { padding:6px 12px; border-radius:6px; font-size:12px; font-weight:500; border:1px solid #d1d5db; background:#fff; cursor:pointer; }
        .mt-btn:hover { background:#f8fafc; }
        .mt-btn--primary { background:#6366f1; border-color:#6366f1; color:#fff; }
        .mt-btn--primary:hover { background:#4f46e5; }
        .mt-btn--gray { background:#f8fafc; }

        .mt-textarea { width:100%; min-height:80px; padding:8px 10px; border:1px solid #d1d5db; border-radius:6px; font-family:inherit; font-size:13px; }

        .mt-cal { display:grid; grid-template-columns:repeat(7,1fr); gap:3px; }
        .mt-cal__cell { aspect-ratio:1/1; border-radius:4px; background:#f1f5f9; font-size:10px; color:#94a3b8; display:flex; align-items:center; justify-content:center; }
        .mt-cal__cell--present { background:#dcfce7; color:#166534; }
        .mt-cal__cell--absent  { background:#fee2e2; color:#991b1b; }
        .mt-cal__cell--late    { background:#fef3c7; color:#92400e; }
        .mt-cal__cell--leave   { background:#dbeafe; color:#1e40af; }
        .mt-cal__cell--holiday { background:#f1f5f9; color:#64748b; }
        .mt-cal__legend { display:flex; gap:14px; margin-top:10px; font-size:11px; color:#64748b; flex-wrap:wrap; }
        .mt-cal__legend span { display:flex; align-items:center; gap:4px; }
        .mt-cal__legend i { width:10px; height:10px; border-radius:3px; display:inline-block; }

        .mt-quota { display:flex; flex-direction:column; gap:10px; }
        .mt-quota__row { font-size:12px; }
        .mt-quota__head { display:flex; justify-content:space-between; margin-bottom:4px; }
        .mt-quota__bar { height:6px; background:#f1f5f9; border-radius:999px; overflow:hidden; }
        .mt-quota__bar > div { height:100%; border-radius:999px; }
    </style>

    {{-- ============ HEADER ============ --}}
    <div class="mt-header">
        <div class="mt-avatar">{{ strtoupper(mb_substr($record->name, 0, 1)) }}</div>
        <div class="mt-id" style="flex:1;">
            <h2>{{ $record->name }}</h2>
            <div class="mt-sub">
                {{ $record->subject ?? '—' }} ·
                {{ $record->dept ?? '—' }} ·
                {{ $record->campus ?? '—' }}
            </div>
            <div class="mt-meta">
                <span><strong>Code:</strong> {{ $record->code ?? '—' }}</span>
                <span><strong>Email:</strong> {{ $record->email ?? '—' }}</span>
                <span><strong>Phone:</strong> {{ $record->phone ?? '—' }}</span>
                @if($record->joined_at)
                    <span><strong>Joined:</strong> {{ $record->joined_at->format('d M Y') }}</span>
                @endif
                @if($record->contract_end)
                    <span><strong>Contract ends:</strong> {{ $record->contract_end->format('d M Y') }}</span>
                @endif
            </div>
        </div>
        <span class="mt-badge mt-badge--{{ $statusColor }}">{{ $record->status ?? '—' }}</span>
    </div>

    {{-- ============ TABS ============ --}}
    <div class="mt-tabs">
        @foreach($tabs as $key => $label)
            <button type="button"
                    class="mt-tab {{ $currentTab === $key ? 'mt-tab--active' : '' }}"
                    wire:click="setTab('{{ $key }}')">
                {{ $label }}
                @if($key === 'notes' && $notes->count()) <span style="opacity:.6;">({{ $notes->count() }})</span> @endif
                @if($key === 'query_letters' && $queryLetters->count()) <span style="opacity:.6;">({{ $queryLetters->count() }})</span> @endif
                @if($key === 'leaves' && $leaves->count()) <span style="opacity:.6;">({{ $leaves->count() }})</span> @endif
            </button>
        @endforeach
    </div>

    <div class="mt-panel">
        {{-- ============ PROFILE ============ --}}
        @if($currentTab === 'profile')
            @php $pr = $record->promotionReadiness(); @endphp
            <div class="mt-section" style="border-left:4px solid {{ ['success'=>'#10b981','warning'=>'#f59e0b','danger'=>'#ef4444'][$pr['color']] ?? '#9ca3af' }};">
                <h3 class="mt-section-title">Promotion Readiness</h3>
                <div style="display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;">
                    <span style="display:inline-flex; align-items:center; gap:.5rem; font-weight:600; font-size:1.05rem;">
                        <span style="width:12px; height:12px; border-radius:9999px; background:{{ ['success'=>'#10b981','warning'=>'#f59e0b','danger'=>'#ef4444'][$pr['color']] ?? '#9ca3af' }};"></span>
                        {{ $pr['label'] }}
                    </span>
                    <span style="font-size:.8rem; color:#6b7280;">
                        @if($pr['source'] === 'manual')
                            Manual override{{ $pr['setBy'] ? ' by '.$pr['setBy'] : '' }}{{ $pr['setAt'] ? ' on '.$pr['setAt']->format('d M Y') : '' }}
                        @else
                            Auto-computed
                        @endif
                    </span>
                </div>
                <div style="margin-top:.5rem; font-size:.85rem; color:#374151;">{{ $pr['reason'] }}</div>
            </div>
            <div class="mt-section">
                <h3 class="mt-section-title">Identity</h3>
                <div class="mt-grid">
                    <div><span>Full name</span><span>{{ $record->name }}</span></div>
                    <div><span>Employee no.</span><span>{{ $record->employee_no ?? '—' }}</span></div>
                    <div><span>Gender</span><span>{{ ucfirst($record->gender ?? '—') }}</span></div>
                    <div><span>Date of birth</span><span>{{ $record->dob?->format('d M Y') ?? '—' }}</span></div>
                    <div><span>City</span><span>{{ $record->city ?? '—' }}</span></div>
                    <div><span>Education</span><span>{{ $record->education ?? '—' }}</span></div>
                </div>
            </div>
            <div class="mt-section">
                <h3 class="mt-section-title">Assignment</h3>
                <div class="mt-grid">
                    <div><span>Subject</span><span>{{ $record->subject ?? '—' }}</span></div>
                    <div><span>Department</span><span>{{ $record->dept ?? '—' }}</span></div>
                    <div><span>Campus</span><span>{{ $record->campus ?? '—' }}</span></div>
                    <div><span>Employment</span><span>{{ $record->employment ?? '—' }}</span></div>
                    <div><span>Tenure</span><span>{{ $record->tenure ?? '—' }}</span></div>
                    <div><span>Contract</span><span>{{ $record->contract ?? '—' }}</span></div>
                </div>
            </div>
        @endif

        {{-- ============ ATTENDANCE ============ --}}
        @if($currentTab === 'attendance')
            @php
                $byDate = $attendance->keyBy(fn($a) => \Illuminate\Support\Carbon::parse($a->date)->toDateString());
                $month = \Illuminate\Support\Carbon::now()->startOfMonth();
                $firstDow = (int) $month->dayOfWeekIso;
                $daysIn = $month->daysInMonth;
                $counts = ['present'=>0,'absent'=>0,'late'=>0,'leave'=>0,'holiday'=>0];
                foreach($attendance as $a) { $k = $a->status; if(isset($counts[$k])) $counts[$k]++; }
            @endphp
            <div class="mt-section">
                <h3 class="mt-section-title">Last 60 days summary</h3>
                <div style="display:flex; gap:18px; font-size:13px;">
                    <span><strong>{{ $counts['present'] }}</strong> present</span>
                    <span><strong>{{ $counts['absent'] }}</strong> absent</span>
                    <span><strong>{{ $counts['late'] }}</strong> late</span>
                    <span><strong>{{ $counts['leave'] }}</strong> leave</span>
                </div>
            </div>
            <div class="mt-section">
                <h3 class="mt-section-title">{{ $month->format('F Y') }} calendar</h3>
                <div class="mt-cal">
                    @for($i=1; $i<$firstDow; $i++)<div class="mt-cal__cell" style="background:transparent;"></div>@endfor
                    @for($d=1; $d<=$daysIn; $d++)
                        @php
                            $dateStr = $month->copy()->day($d)->toDateString();
                            $att = $byDate[$dateStr] ?? null;
                            $cls = $att ? 'mt-cal__cell--' . $att->status : '';
                        @endphp
                        <div class="mt-cal__cell {{ $cls }}" title="{{ $dateStr }}{{ $att ? ' · '.$att->status : '' }}">{{ $d }}</div>
                    @endfor
                </div>
                <div class="mt-cal__legend">
                    <span><i style="background:#dcfce7;"></i> Present</span>
                    <span><i style="background:#fee2e2;"></i> Absent</span>
                    <span><i style="background:#fef3c7;"></i> Late</span>
                    <span><i style="background:#dbeafe;"></i> Leave</span>
                </div>
            </div>
            <div class="mt-section">
                <h3 class="mt-section-title">Recent log</h3>
                @if($attendance->isEmpty())
                    <div class="mt-empty">No attendance records.</div>
                @else
                    <table class="mt-table">
                        <thead><tr><th>Date</th><th>Status</th><th>Check-in</th><th>Check-out</th><th>Note</th></tr></thead>
                        <tbody>
                            @foreach($attendance->take(20) as $a)
                                <tr>
                                    <td>{{ \Illuminate\Support\Carbon::parse($a->date)->format('D, d M Y') }}</td>
                                    <td><span class="mt-badge mt-badge--{{ ['present'=>'success','absent'=>'danger','late'=>'warning','leave'=>'info','holiday'=>'gray'][$a->status] ?? 'gray' }}">{{ $a->status }}</span></td>
                                    <td>{{ $a->check_in_at ? \Illuminate\Support\Carbon::parse($a->check_in_at)->format('H:i') : '—' }}</td>
                                    <td>{{ $a->check_out_at ? \Illuminate\Support\Carbon::parse($a->check_out_at)->format('H:i') : '—' }}</td>
                                    <td>{{ $a->note ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endif

        {{-- ============ DUTIES ============ --}}
        @if($currentTab === 'duties')
            @if($duties->isEmpty())
                <div class="mt-empty">No duty assignments.</div>
            @else
                <table class="mt-table">
                    <thead><tr><th>Title</th><th>Location</th><th>Recurrence</th><th>Starts</th><th>Ends</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($duties as $d)
                            <tr>
                                <td><strong>{{ $d->title }}</strong></td>
                                <td>{{ $d->location ?? '—' }}</td>
                                <td>{{ $d->recurrence ?? '—' }}</td>
                                <td>{{ $d->starts_at ? \Illuminate\Support\Carbon::parse($d->starts_at)->format('d M Y H:i') : '—' }}</td>
                                <td>{{ $d->ends_at ? \Illuminate\Support\Carbon::parse($d->ends_at)->format('d M Y H:i') : '—' }}</td>
                                <td><span class="mt-badge mt-badge--{{ ['accepted'=>'success','declined'=>'danger','completed'=>'info','pending'=>'warning'][$d->status] ?? 'gray' }}">{{ $d->status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endif

        {{-- ============ SUBSTITUTIONS ============ --}}
        @if($currentTab === 'substitutions')
            <div class="mt-section">
                <h3 class="mt-section-title">Times this teacher covered for others</h3>
                @if($coveredFor->isEmpty())
                    <div class="mt-empty">No covered leaves yet.</div>
                @else
                    <table class="mt-table">
                        <thead><tr><th>Original teacher</th><th>Type</th><th>From</th><th>To</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($coveredFor as $l)
                                <tr>
                                    <td>{{ $l->teacher?->name ?? '—' }}</td>
                                    <td>{{ $l->type }}</td>
                                    <td>{{ $l->starts_at?->format('d M Y') ?? '—' }}</td>
                                    <td>{{ $l->ends_at?->format('d M Y') ?? '—' }}</td>
                                    <td>{{ $l->status }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            <div class="mt-section">
                <h3 class="mt-section-title">Substitute invitations received</h3>
                @if($substitutesGiven->isEmpty())
                    <div class="mt-empty">No substitute invitations.</div>
                @else
                    <table class="mt-table">
                        <thead><tr><th>For</th><th>Sent</th><th>Status</th><th>Responded</th></tr></thead>
                        <tbody>
                            @foreach($substitutesGiven as $o)
                                <tr>
                                    <td>{{ $o->leave?->teacher?->name ?? '—' }}</td>
                                    <td>{{ $o->sent_at?->format('d M Y H:i') ?? '—' }}</td>
                                    <td><span class="mt-badge mt-badge--{{ \App\Models\SubstituteOffer::STATUS_COLORS[$o->status] ?? 'gray' }}">{{ $o->statusLabel() }}</span></td>
                                    <td>{{ $o->responded_at?->format('d M Y H:i') ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endif

        {{-- ============ QUALIFICATIONS ============ --}}
        @if($currentTab === 'qualifications')
            <div class="mt-section">
                <h3 class="mt-section-title">Formal certifications</h3>
                @if($certifications->isEmpty())
                    <div class="mt-empty">No formal certifications recorded.</div>
                @else
                    <table class="mt-table">
                        <thead><tr><th>Name</th><th>Issued</th><th>Expires</th><th>Govt approved</th></tr></thead>
                        <tbody>
                            @foreach($certifications as $c)
                                <tr>
                                    <td>{{ $c->name }}</td>
                                    <td>{{ $c->issued_at ? \Illuminate\Support\Carbon::parse($c->issued_at)->format('d M Y') : '—' }}</td>
                                    <td>{{ $c->expires_at ? \Illuminate\Support\Carbon::parse($c->expires_at)->format('d M Y') : '—' }}</td>
                                    <td>{{ $c->is_government_approved ? 'Yes' : 'No' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            <div class="mt-section">
                <h3 class="mt-section-title">Clearances</h3>
                @if($clearances->isEmpty())
                    <div class="mt-empty">No clearances on file.</div>
                @else
                    <table class="mt-table">
                        <thead><tr><th>Type</th><th>Status</th><th>Issued</th></tr></thead>
                        <tbody>
                            @foreach($clearances as $c)
                                <tr>
                                    <td>{{ $c->type }}</td>
                                    <td>{{ $c->status }}</td>
                                    <td>{{ $c->issued_at ? \Illuminate\Support\Carbon::parse($c->issued_at)->format('d M Y') : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            <div class="mt-section">
                <h3 class="mt-section-title">Uploaded documents</h3>
                @if($documents->isEmpty())
                    <div class="mt-empty">No documents uploaded.</div>
                @else
                    <table class="mt-table">
                        <thead><tr><th>Label</th><th>Type</th><th>Status</th><th>Expires</th></tr></thead>
                        <tbody>
                            @foreach($documents as $d)
                                <tr>
                                    <td>{{ $d->label ?? $d->type }}</td>
                                    <td>{{ $d->type }}</td>
                                    <td><span class="mt-badge mt-badge--{{ ['approved'=>'success','rejected'=>'danger','pending'=>'warning'][$d->status] ?? 'gray' }}">{{ $d->status }}</span></td>
                                    <td>{{ $d->expires_at ? \Illuminate\Support\Carbon::parse($d->expires_at)->format('d M Y') : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            <div class="mt-section">
                <h3 class="mt-section-title">Trainings / CPD</h3>
                @if($trainings->isEmpty())
                    <div class="mt-empty">No trainings recorded.</div>
                @else
                    <table class="mt-table">
                        <thead><tr><th>Name</th><th>Provider</th><th>Starts</th><th>Hours</th><th>AY</th></tr></thead>
                        <tbody>
                            @foreach($trainings as $tr)
                                <tr>
                                    <td>{{ $tr->name }}</td>
                                    <td>{{ $tr->provider ?? '—' }}</td>
                                    <td>{{ $tr->starts_on ? \Illuminate\Support\Carbon::parse($tr->starts_on)->format('d M Y') : '—' }}</td>
                                    <td>{{ $tr->hours_certified ?? '—' }}</td>
                                    <td>{{ $tr->academic_year ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endif

        {{-- ============ LEAVES ============ --}}
        @if($currentTab === 'leaves')
            @php
                $year = now()->year;
                $limit = (int) $record->quotaFor();
                $used = (int) $record->leaveDaysUsed($year);
                $pct = $limit > 0 ? min(100, ($used/$limit)*100) : 0;
                $color = $used >= $limit ? '#ef4444' : ($pct >= 75 ? '#f59e0b' : '#10b981');
                $remaining = max(0, $limit - $used);
            @endphp
            <div class="mt-section">
                <h3 class="mt-section-title">Quota usage · {{ $year }}</h3>
                <div class="mt-quota">
                    <div class="mt-quota__row">
                        <div class="mt-quota__head">
                            <strong>Leave quota</strong>
                            <span style="color:#64748b;">{{ $used }} / {{ $limit }} days · <strong style="color:{{ $color }};">{{ $remaining }} left</strong></span>
                        </div>
                        <div class="mt-quota__bar"><div style="width:{{ $pct }}%; background:{{ $color }};"></div></div>
                        <div style="font-size:11px; color:#94a3b8; margin-top:4px;">Only quota-affecting leave types are counted.</div>
                    </div>
                </div>
            </div>
            <div class="mt-section">
                <h3 class="mt-section-title">Leave history</h3>
                @if($leaves->isEmpty())
                    <div class="mt-empty">No leave requests on record.</div>
                @else
                    <table class="mt-table">
                        <thead><tr><th>Type</th><th>From</th><th>To</th><th>Status</th><th>Substitute</th><th>Reason</th></tr></thead>
                        <tbody>
                            @foreach($leaves as $l)
                                <tr>
                                    <td>{{ \App\Models\LeaveType::labelFor($l->type) }}</td>
                                    <td>{{ $l->starts_at?->format('d M Y') ?? '—' }}</td>
                                    <td>{{ $l->ends_at?->format('d M Y') ?? '—' }}</td>
                                    <td><span class="mt-badge mt-badge--{{ ['approved'=>'success','rejected'=>'danger','pending'=>'warning'][$l->status] ?? 'gray' }}">{{ $l->status }}</span></td>
                                    <td>{{ optional($l->substitute ?? null)->name ?? '—' }}</td>
                                    <td style="max-width:240px;">{{ $l->reason ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endif

        {{-- ============ QUERY LETTERS ============ --}}
        @if($currentTab === 'query_letters')
            @if($queryLetters->isEmpty())
                <div class="mt-empty">No query letters issued. Use “Issue query letter” in the header to create one.</div>
            @else
                @foreach($queryLetters as $ql)
                    <div class="mt-letter">
                        <div class="mt-letter__head">
                            <div>
                                <div class="mt-letter__title">{{ $ql->title }}</div>
                                <div class="mt-letter__meta">
                                    Issued {{ $ql->issued_at?->format('d M Y') ?? '—' }}
                                    @if($ql->issuedBy) · by {{ $ql->issuedBy->name }} @endif
                                </div>
                            </div>
                            <span class="mt-badge mt-badge--{{ $ql->statusColor() }}">{{ $ql->statusLabel() }}</span>
                        </div>
                        <div class="mt-letter__body">{{ $ql->body }}</div>

                        @if($ql->response)
                            <div class="mt-letter__response">
                                <strong>Response ({{ $ql->responded_at?->format('d M Y') }}):</strong><br>
                                {{ $ql->response }}
                            </div>
                        @endif

                        @if($respondingLetter && $respondingLetter->id === $ql->id)
                            <div style="margin-top:10px;">
                                <textarea class="mt-textarea" wire:model="responseDraft" placeholder="Teacher's response..."></textarea>
                                <div class="mt-letter__actions">
                                    <button type="button" class="mt-btn mt-btn--primary" wire:click="saveResponse">Save response</button>
                                    <button type="button" class="mt-btn" wire:click="cancelResponse">Cancel</button>
                                </div>
                            </div>
                        @else
                            <div class="mt-letter__actions">
                                @if($ql->status !== 'closed')
                                    <button type="button" class="mt-btn" wire:click="openResponse({{ $ql->id }})">
                                        {{ $ql->response ? 'Edit response' : 'Record response' }}
                                    </button>
                                    <button type="button" class="mt-btn mt-btn--gray" wire:click="closeLetter({{ $ql->id }})">Close letter</button>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        @endif

        {{-- ============ OBSERVATIONS ============ --}}
        @if($currentTab === 'observations')
            @if($observations->isEmpty())
                <div class="mt-empty">No classroom observations yet.</div>
            @else
                <table class="mt-table">
                    <thead><tr><th>Date</th><th>Subject</th><th>Class</th><th>Observer</th><th>Avg score</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($observations as $o)
                            <tr>
                                <td>{{ $o->observed_at ? \Illuminate\Support\Carbon::parse($o->observed_at)->format('d M Y') : '—' }}</td>
                                <td>{{ $o->lesson_subject ?? '—' }}</td>
                                <td>{{ $o->lesson_class_code ?? '—' }}</td>
                                <td>{{ $o->observer?->name ?? '—' }}</td>
                                <td>{{ $o->average_score !== null ? number_format($o->average_score, 2) : '—' }}</td>
                                <td><span class="mt-badge mt-badge--{{ ['approved'=>'success','rejected'=>'danger','pending'=>'warning'][$o->status] ?? 'gray' }}">{{ $o->status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endif

        {{-- ============ GOALS ============ --}}
        @if($currentTab === 'goals')
            @if($goals->isEmpty())
                <div class="mt-empty">No goals set.</div>
            @else
                <table class="mt-table">
                    <thead><tr><th>Title</th><th>AY</th><th>Target</th><th>Progress</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($goals as $g)
                            <tr>
                                <td><strong>{{ $g->title }}</strong>
                                    @if($g->description)<div style="color:#64748b; font-size:12px; margin-top:2px;">{{ $g->description }}</div>@endif
                                </td>
                                <td>{{ $g->academic_year ?? '—' }}</td>
                                <td>{{ $g->target_date ? \Illuminate\Support\Carbon::parse($g->target_date)->format('d M Y') : '—' }}</td>
                                <td>
                                    <div style="width:120px;">
                                        <div class="mt-quota__bar"><div style="width:{{ (int) $g->progress }}%; background:#6366f1;"></div></div>
                                        <span style="font-size:11px; color:#64748b;">{{ (int) $g->progress }}%</span>
                                    </div>
                                </td>
                                <td><span class="mt-badge mt-badge--{{ ['achieved'=>'success','at_risk'=>'danger','on_track'=>'info'][$g->status] ?? 'gray' }}">{{ $g->status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endif

        {{-- ============ NOTES ============ --}}
        @if($currentTab === 'notes')
            @if($notes->isEmpty())
                <div class="mt-empty">No principal notes yet. Use “Add note” in the header.</div>
            @else
                @foreach($notes as $n)
                    <div class="mt-note {{ $n->pinned ? 'mt-note--pinned' : '' }}">
                        <div class="mt-note__head">
                            <span>
                                <strong>{{ $n->author?->name ?? 'Principal' }}</strong>
                                · {{ $n->created_at->diffForHumans() }}
                                @if($n->pinned) · 📌 pinned @endif
                            </span>
                        </div>
                        <div class="mt-note__body">{{ $n->body }}</div>
                        <div class="mt-note__actions">
                            <button type="button" class="mt-note__btn" wire:click="togglePinNote({{ $n->id }})">
                                {{ $n->pinned ? 'Unpin' : 'Pin' }}
                            </button>
                            <button type="button" class="mt-note__btn mt-note__btn--danger"
                                    wire:click="deleteNote({{ $n->id }})"
                                    wire:confirm="Delete this note?">Delete</button>
                        </div>
                    </div>
                @endforeach
            @endif
        @endif
    </div>
</x-filament-panels::page>
