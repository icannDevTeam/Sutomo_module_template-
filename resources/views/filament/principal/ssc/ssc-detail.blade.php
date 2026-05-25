@php
    use App\Models\SscRequest;
    $statusColor = SscRequest::STATUS_COLORS[$record->status] ?? 'gray';
    $type = SscRequest::TYPES[$record->type] ?? $record->type;
    $priorityColors = ['high' => 'danger', 'normal' => 'gray', 'low' => 'info'];
    $priorityColor = $priorityColors[$record->priority] ?? 'gray';
@endphp

<div class="sp-ssc-modal">
    <div class="sp-ssc-modal__head">
        <div>
            <div class="sp-ssc-modal__code">{{ $record->code }}</div>
            <div class="sp-ssc-modal__title">{{ $type }}</div>
            <div class="sp-ssc-modal__student">Student: <strong>{{ $record->student?->name ?? '—' }}</strong></div>
        </div>
        <div class="sp-ssc-modal__badges">
            <span class="sp-doc-badge sp-doc-badge--{{ $statusColor }}">
                {{ SscRequest::STATUSES[$record->status] ?? $record->status }}
            </span>
            <span class="sp-doc-badge sp-doc-badge--{{ $priorityColor }}">
                {{ ucfirst($record->priority ?? 'normal') }} priority
            </span>
        </div>
    </div>

    <div class="sp-ssc-modal__meta">
        <div>
            <div class="sp-doc-preview__label">Requested</div>
            <div class="sp-doc-preview__value">{{ optional($record->requested_at)->format('d M Y') ?: '—' }}</div>
        </div>
        <div>
            <div class="sp-doc-preview__label">Type</div>
            <div class="sp-doc-preview__value">{{ $type }}</div>
        </div>
    </div>

    @if(! empty($record->clearance) && is_array($record->clearance))
        <div class="sp-ssc-modal__clearance">
            <div class="sp-doc-preview__label">Clearance Checklist</div>
            <ul>
                @foreach($record->clearance as $key => $value)
                    <li>{{ is_string($key) ? $key : $value }}: <strong>{{ is_bool($value) ? ($value ? '✓' : '✗') : $value }}</strong></li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($record->notes)
        <div class="sp-ssc-modal__notes">
            <div class="sp-doc-preview__label">Notes</div>
            <div>{{ $record->notes }}</div>
        </div>
    @endif
</div>
