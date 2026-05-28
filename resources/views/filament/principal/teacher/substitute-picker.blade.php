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
    .sp-pick { font-size: 13px; color: #1f2937; }
    .sp-pick__head {
        background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 8px;
        padding: 12px 14px; margin-bottom: 14px;
        display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 12px;
    }
    .sp-pick__head .lbl { font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; }
    .sp-pick__head .val { font-weight: 600; color: #111827; }

    .sp-pick__tier { margin-bottom: 14px; }
    .sp-pick__tier-title {
        font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
        color: #6b7280; margin-bottom: 6px; padding-left: 4px;
    }
    .sp-pick__empty { color: #9ca3af; font-style: italic; padding: 6px 4px; font-size: 12px; }

    .sp-pick__card {
        display: grid;
        grid-template-columns: 36px 1fr 90px 1fr 110px;
        gap: 10px; align-items: center;
        padding: 10px 12px; margin-bottom: 6px;
        background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px;
        transition: all .15s ease;
    }
    .sp-pick__card.is-selected { border-color: #6a1b9a; box-shadow: 0 0 0 3px rgba(106,27,154,.15); }
    .sp-pick__card.is-disabled { opacity: .55; background: #fafafa; }

    .sp-pick__avatar {
        width: 36px; height: 36px; border-radius: 50%; background: #e5e7eb;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; color: #4b5563; font-size: 13px;
    }
    .sp-pick__name { font-weight: 600; color: #111827; }
    .sp-pick__meta { font-size: 11px; color: #6b7280; }

    .sp-pick__badge {
        display: inline-block; padding: 3px 8px; border-radius: 999px;
        font-size: 11px; font-weight: 600; text-align: center;
    }
    .sp-pick__badge--success { background: #dcfce7; color: #166534; }
    .sp-pick__badge--warning { background: #fef3c7; color: #92400e; }
    .sp-pick__badge--danger  { background: #fee2e2; color: #991b1b; }

    .sp-pick__links { display: flex; gap: 6px; flex-wrap: wrap; font-size: 11px; }
    .sp-pick__link {
        display: inline-flex; align-items: center; gap: 3px;
        padding: 3px 7px; border-radius: 6px; text-decoration: none;
        background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb;
    }
    .sp-pick__link:hover { background: #e5e7eb; }
    .sp-pick__link--wa { background: #d1fae5; color: #065f46; border-color: #a7f3d0; }
    .sp-pick__link--wa:hover { background: #a7f3d0; }

    .sp-pick__btn {
        padding: 6px 12px; border-radius: 6px; border: 1px solid #6a1b9a;
        background: #6a1b9a; color: #ffffff; font-weight: 600; font-size: 12px;
        cursor: pointer;
    }
    .sp-pick__btn:hover { background: #581685; }
    .sp-pick__btn--selected { background: #166534; border-color: #166534; }
    .sp-pick__btn--disabled { background: #d1d5db; border-color: #d1d5db; color: #6b7280; cursor: not-allowed; }

    .sp-pick__conflicts { font-size: 11px; color: #b91c1c; margin-top: 4px; }
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
            <div class="sp-pick__meta">{{ $leave->teacher?->subject ?? '' }} · {{ $leave->teacher?->campus ?? '' }}</div>
        </div>
        <div>
            <div class="lbl">Type</div>
            <div class="val">{{ TeacherLeave::TYPES[$leave->type] ?? $leave->type }}</div>
        </div>
        <div>
            <div class="lbl">Dates</div>
            <div class="val">{{ optional($leave->starts_at)->format('d M') }} – {{ optional($leave->ends_at)->format('d M Y') }}</div>
            <div class="sp-pick__meta">{{ $days ? $days . ' day(s)' : '' }}</div>
        </div>
        <div>
            <div class="lbl">Reason</div>
            <div class="val" style="font-weight: 400; font-size: 12px;">{{ $leave->reason ?: '—' }}</div>
        </div>
    </div>

    @forelse ([1, 2, 3, 4] as $tier)
        @php $group = $grouped->get($tier, collect()); @endphp
        <div class="sp-pick__tier">
            <div class="sp-pick__tier-title">{{ \App\Support\SubstituteSuggester::TIER_LABELS[$tier] }} ({{ $group->count() }})</div>

            @if($group->isEmpty())
                <div class="sp-pick__empty">No candidates in this tier.</div>
            @else
                @foreach($group as $c)
                    @php
                        $t = $c['teacher'];
                        $initials = collect(explode(' ', $t->name))->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('');
                    @endphp
                    <div class="sp-pick__card"
                         :class="{ 'is-selected': picked == {{ $t->id }} }"
                         @class(['is-disabled' => ! $c['is_assignable']])>
                        <div class="sp-pick__avatar">{{ strtoupper($initials) }}</div>

                        <div>
                            <div class="sp-pick__name">{{ $t->name }}</div>
                            <div class="sp-pick__meta">
                                {{ $t->subject ?: '—' }} · {{ $t->dept ?: '—' }} · {{ $t->campus ?: '—' }}
                            </div>
                            @if(! empty($c['conflict_reasons']))
                                <div class="sp-pick__conflicts">
                                    {{ implode(' · ', $c['conflict_reasons']) }}
                                </div>
                            @endif
                        </div>

                        <div>
                            <span class="sp-pick__badge sp-pick__badge--{{ $c['availability_color'] }}">
                                {{ $c['availability_label'] }}
                            </span>
                        </div>

                        <div class="sp-pick__links">
                            @if($t->phone)
                                <a class="sp-pick__link" href="tel:{{ $t->phone }}">📞 {{ $t->phone }}</a>
                            @endif
                            @if($c['wa_link'])
                                <a class="sp-pick__link sp-pick__link--wa" href="{{ $c['wa_link'] }}" target="_blank" rel="noopener">💬 WhatsApp</a>
                            @endif
                            @if($t->email)
                                <a class="sp-pick__link" href="mailto:{{ $t->email }}">✉ Email</a>
                            @endif
                            @if(! $t->phone && ! $t->email)
                                <span class="sp-pick__meta">No contact on file</span>
                            @endif
                        </div>

                        <div>
                            @if($c['is_assignable'])
                                <button type="button"
                                        class="sp-pick__btn"
                                        :class="{ 'sp-pick__btn--selected': picked == {{ $t->id }} }"
                                        @click="picked = {{ $t->id }}"
                                        x-text="picked == {{ $t->id }} ? '✓ Selected' : 'Select'">
                                    Select
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
        Verify with the teacher before confirming — availability is calculated from approved leaves, attendance records, and existing duty assignments. Timetable lesson conflicts are not yet checked.
    </div>
</div>
