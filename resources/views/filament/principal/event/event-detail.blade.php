@php
    use App\Models\SchoolEvent;
    $statusColor = SchoolEvent::STATUS_COLORS[$record->status] ?? 'gray';
    $category = SchoolEvent::CATEGORIES[$record->category] ?? $record->category;
    $days = $record->starts_at && $record->ends_at
        ? $record->starts_at->diffInDays($record->ends_at) + 1
        : 1;
@endphp

<div class="sp-event-modal">
    <div class="sp-event-modal__head">
        <div>
            <div class="sp-event-modal__code">{{ $record->code }}</div>
            <div class="sp-event-modal__title">{{ $record->title }}</div>
            <div class="sp-event-modal__sub">{{ $category }} · {{ strtoupper($record->campus ?? '—') }}</div>
        </div>
        <span class="sp-doc-badge sp-doc-badge--{{ $statusColor }}">
            {{ SchoolEvent::STATUSES[$record->status] ?? $record->status }}
        </span>
    </div>

    <div class="sp-event-modal__meta">
        <div>
            <div class="sp-doc-preview__label">Starts</div>
            <div class="sp-doc-preview__value">{{ optional($record->starts_at)->format('d M Y') ?: '—' }}</div>
        </div>
        <div>
            <div class="sp-doc-preview__label">Ends</div>
            <div class="sp-doc-preview__value">{{ optional($record->ends_at)->format('d M Y') ?: '—' }}</div>
        </div>
        <div>
            <div class="sp-doc-preview__label">Duration</div>
            <div class="sp-doc-preview__value">{{ $days }} day(s)</div>
        </div>
        <div>
            <div class="sp-doc-preview__label">Person in Charge</div>
            <div class="sp-doc-preview__value">{{ $record->pic ?: '—' }}</div>
        </div>
        <div>
            <div class="sp-doc-preview__label">Participants</div>
            <div class="sp-doc-preview__value">{{ $record->participants ?: '—' }}</div>
        </div>
    </div>

    @if($record->description)
        <div class="sp-event-modal__desc">
            <div class="sp-doc-preview__label">Description</div>
            <div>{{ $record->description }}</div>
        </div>
    @endif
</div>
