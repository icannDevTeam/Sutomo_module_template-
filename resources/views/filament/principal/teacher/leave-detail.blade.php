@php
    use App\Models\TeacherLeave;
    $statusColor = TeacherLeave::STATUS_COLORS[$record->status] ?? 'gray';
    $days = $record->starts_at && $record->ends_at
        ? $record->starts_at->diffInDays($record->ends_at) + 1
        : null;
@endphp

<div class="sp-leave-modal">
    <div class="sp-leave-modal__head">
        <div>
            <div class="sp-leave-modal__teacher">{{ $record->teacher?->name ?? '—' }}</div>
            <div class="sp-leave-modal__type">{{ TeacherLeave::TYPES[$record->type] ?? $record->type }} Leave</div>
        </div>
        <span class="sp-doc-badge sp-doc-badge--{{ $statusColor }}">
            {{ TeacherLeave::STATUSES[$record->status] ?? $record->status }}
        </span>
    </div>

    <div class="sp-leave-modal__meta">
        <div>
            <div class="sp-doc-preview__label">From</div>
            <div class="sp-doc-preview__value">{{ optional($record->starts_at)->format('d M Y') }}</div>
        </div>
        <div>
            <div class="sp-doc-preview__label">To</div>
            <div class="sp-doc-preview__value">{{ optional($record->ends_at)->format('d M Y') }}</div>
        </div>
        <div>
            <div class="sp-doc-preview__label">Duration</div>
            <div class="sp-doc-preview__value">{{ $days ? $days . ' day(s)' : '—' }}</div>
        </div>
        <div>
            <div class="sp-doc-preview__label">Substitute</div>
            <div class="sp-doc-preview__value">{{ $record->substitute?->name ?? '— not assigned —' }}</div>
        </div>
    </div>

    @if($record->reason)
        <div class="sp-leave-modal__reason">
            <div class="sp-doc-preview__label">Reason</div>
            <div>{{ $record->reason }}</div>
        </div>
    @endif

    @if($record->decided_by)
        <div class="sp-leave-modal__decision">
            <strong>Decision:</strong>
            {{ TeacherLeave::STATUSES[$record->status] ?? $record->status }}
            by {{ $record->decided_by }}
            @if($record->decided_at) on {{ $record->decided_at->format('d M Y, H:i') }}@endif
        </div>
    @endif

    <div class="sp-leave-modal__actions">
        <a href="{{ route('teacher-leave.print', $record) }}" target="_blank" rel="noopener" class="sp-doc-preview__download">
            🖨 Print Leave Letter
        </a>
    </div>
</div>
