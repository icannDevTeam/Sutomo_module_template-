@php
    /** @var \Illuminate\Support\Collection $leaves */
    /** @var array $summary */
    use App\Models\TeacherLeave;
@endphp

<div class="sp-leave-history">
    <div class="sp-leave-summary">
        <div class="sp-leave-summary__chip sp-leave-summary__chip--total">
            <span class="sp-leave-summary__num">{{ $summary['days_used_year'] }}</span>
            <span class="sp-leave-summary__lbl">days used · {{ now()->year }}</span>
        </div>
        <div class="sp-leave-summary__chip sp-leave-summary__chip--pending">
            <span class="sp-leave-summary__num">{{ $summary['pending'] }}</span>
            <span class="sp-leave-summary__lbl">pending</span>
        </div>
        <div class="sp-leave-summary__chip sp-leave-summary__chip--approved">
            <span class="sp-leave-summary__num">{{ $summary['approved'] }}</span>
            <span class="sp-leave-summary__lbl">approved</span>
        </div>
        <div class="sp-leave-summary__chip sp-leave-summary__chip--rejected">
            <span class="sp-leave-summary__num">{{ $summary['rejected'] }}</span>
            <span class="sp-leave-summary__lbl">rejected</span>
        </div>
    </div>

    @if ($leaves->isEmpty())
        <div class="sp-leave-empty">No leave requests on record.</div>
    @else
        <div class="sp-leave-list">
            @foreach ($leaves as $leave)
                @php
                    $days = max(1, $leave->starts_at->diffInDays($leave->ends_at) + 1);
                    $statusClass = 'sp-leave-row--' . $leave->status;
                @endphp
                <div class="sp-leave-row {{ $statusClass }}">
                    <div class="sp-leave-row__date">
                        <span class="sp-leave-row__day">{{ $leave->starts_at->format('d') }}</span>
                        <span class="sp-leave-row__mon">{{ $leave->starts_at->format('M Y') }}</span>
                    </div>
                    <div class="sp-leave-row__body">
                        <div class="sp-leave-row__title">
                            {{ TeacherLeave::TYPES[$leave->type] ?? $leave->type }}
                            <span class="sp-leave-row__days">· {{ $days }} {{ \Illuminate\Support\Str::plural('day', $days) }}</span>
                        </div>
                        <div class="sp-leave-row__meta">
                            {{ $leave->starts_at->format('d M') }} → {{ $leave->ends_at->format('d M Y') }}
                            @if ($leave->substitute_teacher_id && $leave->relationLoaded('substitute') === false)
                                · sub: {{ optional($leave->substitute)->name ?? '—' }}
                            @endif
                        </div>
                        @if ($leave->reason)
                            <div class="sp-leave-row__reason">{{ $leave->reason }}</div>
                        @endif
                    </div>
                    <div class="sp-leave-row__status">
                        <span class="sp-leave-badge sp-leave-badge--{{ $leave->status }}">
                            {{ TeacherLeave::STATUSES[$leave->status] ?? $leave->status }}
                        </span>
                        @if ($leave->decided_by)
                            <div class="sp-leave-row__decider">by {{ $leave->decided_by }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
