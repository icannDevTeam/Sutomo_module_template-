@php
    use App\Models\TeacherLeave;
    /** @var \App\Models\TeacherLeave $leave */
    /** @var \Illuminate\Support\Collection $candidates */
    $grouped = $candidates->groupBy('tier');
    $days = $leave->starts_at && $leave->ends_at
        ? $leave->starts_at->diffInDays($leave->ends_at) + 1
        : null;
@endphp

<style>
    /* Match the styling of the SubmitLeaveOnBehalf "Suggested substitutes" panel. */
    .sp-pick { font-size: 13px; color: #1f2937; }

    .sp-pick__head {
        background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
        padding: 12px 14px; margin-bottom: 14px;
        display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 12px;
    }
    .sp-pick__head .lbl { font-size: 10px; text-transform: uppercase; letter-spacing: .05em; color: #64748b; font-weight: 700; }
    .sp-pick__head .val { font-weight: 600; color: #0f172a; font-size: 13px; margin-top: 2px; }
    .sp-pick__head .meta { font-size: 11px; color: #64748b; margin-top: 1px; }

    .sp-pick__tier { margin-bottom: 12px; }
    .sp-pick__tier-title {
        font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
        color: #64748b; margin-bottom: 6px;
    }
    .sp-pick__empty { color: #94a3b8; font-style: italic; padding: 4px 2px; font-size: 11px; }

    .sp-pick__row {
        display: grid; grid-template-columns: 1fr auto;
        gap: 10px; align-items: center;
        padding: 8px 10px; margin-bottom: 4px;
        background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px;
        transition: all .12s ease;
    }
    .sp-pick__row.is-selected {
        background: #ecfdf5; border-color: #10b981;
        box-shadow: 0 0 0 2px rgba(16,185,129,.18);
    }
    .sp-pick__row.is-disabled { opacity: .55; background: #fafafa; }

    .sp-pick__name { font-weight: 600; color: #0f172a; font-size: 13px; }
    .sp-pick__meta { font-size: 11px; color: #64748b; }
    .sp-pick__conflicts { font-size: 10px; color: #b91c1c; margin-top: 3px; }

    .sp-pick__badges { display: flex; gap: 4px; flex-wrap: wrap; margin-top: 4px; }
    .sp-pick__badge {
        display: inline-block; padding: 2px 8px; border-radius: 999px;
        font-size: 10px; font-weight: 600;
    }
    .sp-pick__badge--success { background:#dcfce7; color:#166534; }
    .sp-pick__badge--warning { background:#fef3c7; color:#92400e; }
    .sp-pick__badge--danger  { background:#fee2e2; color:#991b1b; }
    .sp-pick__badge--info    { background:#dbeafe; color:#1e40af; }
    .sp-pick__badge--gray    { background:#f1f5f9; color:#475569; }

    .sp-pick__btn {
        padding: 5px 12px; border-radius: 6px; border: 1px solid #6366f1;
        background: #fff; color: #6366f1; font-weight: 600; font-size: 11px;
        cursor: pointer; white-space: nowrap;
    }
    .sp-pick__btn:hover { background: #eef2ff; }
    .sp-pick__btn--selected {
        background: #10b981; border-color: #10b981; color: #fff;
    }
    .sp-pick__btn--disabled {
        background: #f1f5f9; border-color: #e2e8f0; color: #94a3b8; cursor: not-allowed;
    }
    .sp-pick__btn--disabled:hover { background: #f1f5f9; }

    .sp-pick__footer-note {
        margin-top: 10px; padding: 8px 12px; background: #fffbeb;
        border: 1px solid #fde68a; border-radius: 6px; font-size: 11px; color: #78350f;
    }
</style>

<div class="sp-pick" x-data="{ picked: @entangle('mountedTableActionsData.0.substitute_teacher_id').live }">
    <div class="sp-pick__head">
        <div>
            <div class="lbl">Teacher on leave</div>
            <div class="val">{{ $leave->teacher?->name ?? '—' }}</div>
            <div class="meta">{{ $leave->teacher?->subject ?? '' }} · {{ $leave->teacher?->campus ?? '' }}</div>
        </div>
        <div>
            <div class="lbl">Type</div>
            <div class="val">{{ \App\Models\LeaveType::labelFor($leave->type) }}</div>
        </div>
        <div>
            <div class="lbl">Dates</div>
            <div class="val">{{ optional($leave->starts_at)->format('d M') }} – {{ optional($leave->ends_at)->format('d M Y') }}</div>
            <div class="meta">{{ $days ? $days . ' day(s)' : '' }}</div>
        </div>
        <div>
            <div class="lbl">Reason</div>
            <div class="val" style="font-weight: 400; font-size: 12px;">{{ $leave->reason ?: '—' }}</div>
        </div>
    </div>

    @if(! empty($eligibility) && ($eligibility['total_slots'] ?? 0) > 0)
        <div style="margin-bottom: 14px; padding: 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px;">
            @include('filament.principal.teacher.eligibility-grid', [
                'eligibility'      => $eligibility,
                'showPick'         => true,
                'alpinePickTarget' => 'picked',
            ])
        </div>
    @endif

    @forelse ([0, 1, 2, 3, 4, 5] as $tier)
        @php $group = $grouped->get($tier, collect()); @endphp
        <div class="sp-pick__tier">
            <div class="sp-pick__tier-title">{{ \App\Support\SubstituteSuggester::TIER_LABELS[$tier] }} ({{ $group->count() }})</div>

            @if($group->isEmpty())
                <div class="sp-pick__empty">No candidates in this tier.</div>
            @else
                @foreach($group as $c)
                    @php $t = $c['teacher']; @endphp
                    <div class="sp-pick__row"
                         :class="{ 'is-selected': picked == {{ $t->id }} }"
                         @class(['is-disabled' => ! $c['is_assignable']])>
                        <div style="min-width:0;">
                            <div class="sp-pick__name">{{ $t->name }}</div>
                            <div class="sp-pick__meta">
                                {{ $t->subject ?: '—' }} · {{ $t->dept ?: '—' }} · {{ $t->campus ?: '—' }}
                            </div>
                            <div class="sp-pick__badges">
                                <span class="sp-pick__badge sp-pick__badge--{{ $c['availability_color'] }}">
                                    {{ $c['availability_label'] }}
                                </span>
                                @if(($c['category'] ?? 'standard') !== 'standard')
                                    <span class="sp-pick__badge sp-pick__badge--{{ $c['category_color'] }}">
                                        {{ $c['category_label'] }}
                                    </span>
                                @endif
                            </div>
                            @if(! empty($c['conflict_reasons']))
                                <div class="sp-pick__conflicts">
                                    {{ implode(' · ', $c['conflict_reasons']) }}
                                </div>
                            @endif
                        </div>

                        <div>
                            @if($c['is_assignable'])
                                <button type="button"
                                        class="sp-pick__btn"
                                        :class="{ 'sp-pick__btn--selected': picked == {{ $t->id }} }"
                                        @click="picked = {{ $t->id }}"
                                        x-text="picked == {{ $t->id }} ? '✓ Picked' : 'Pick'">
                                    Pick
                                </button>
                            @else
                                <button type="button" class="sp-pick__btn sp-pick__btn--disabled" disabled
                                        title="{{ implode('; ', $c['conflict_reasons']) }}">
                                    Unavailable
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    @empty
    @endforelse

    <div class="sp-pick__footer-note">
        The picked teacher will be notified inside their Sutomo dashboard — no phone or WhatsApp outreach needed. Availability is calculated from approved leaves, attendance records, and existing duty assignments.
    </div>
</div>
