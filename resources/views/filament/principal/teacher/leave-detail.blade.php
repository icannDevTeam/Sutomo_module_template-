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
            <div class="sp-leave-modal__type">{{ \App\Models\LeaveType::labelFor($record->type) }} Leave</div>
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

    @php
        $offers = $record->offers()->with('teacher')->get();
        $interested = $offers->where('status', 'interested');
        $declined = $offers->where('status', 'declined');
        $pending = $offers->where('status', 'pending');
        $cap = (int) ($record->auto_search_cap ?: 5);
    @endphp
    @if($offers->isNotEmpty() || $record->auto_search_status !== 'disabled')
        <div style="margin-top:18px; border-top:1px solid #e5e7eb; padding-top:14px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                <div style="font-weight:600; color:#0f172a; display:flex; align-items:center; gap:8px;">
                    Auto Substitute Search
                    <span class="sp-doc-badge sp-doc-badge--{{ $record->autoSearchStatusColor() }}">
                        {{ $record->autoSearchStatusLabel() }}
                    </span>
                </div>
                @if($record->auto_search_status === 'open' && $record->auto_search_closes_at)
                    <span style="font-size:11px; color:#64748b;">
                        Closes {{ $record->auto_search_closes_at->diffForHumans() }} ·
                        {{ $interested->count() }}/{{ $cap }} interested
                    </span>
                @endif
            </div>

            @if($interested->isNotEmpty())
                <div style="background:#eff6ff; border:1px solid #93c5fd; border-radius:10px; padding:12px; margin-bottom:10px;">
                    <div style="font-weight:600; font-size:13px; color:#1e3a8a; margin-bottom:8px;">
                        {{ $interested->count() }} teacher{{ $interested->count() === 1 ? '' : 's' }} expressed interest
                    </div>
                    <div style="display:flex; flex-direction:column; gap:6px;">
                        @foreach($interested as $offer)
                            <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 12px; background:#fff; border:1px solid #bfdbfe; border-radius:8px;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div>
                                        <div style="font-weight:600; font-size:13px;">{{ $offer->teacher?->name ?? 'Unknown' }}</div>
                                        <div style="font-size:11px; color:#64748b;">
                                            {{ $offer->teacher?->subject ?? '—' }}
                                            @if($offer->responded_at) · {{ $offer->responded_at->diffForHumans() }} @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div style="margin-top:8px; font-size:11px; color:#475569;">
                        Use the <strong>Pick Substitute</strong> button below to assign one of them.
                    </div>
                </div>
            @endif

            @if($pending->isNotEmpty() || $declined->isNotEmpty())
                <details style="margin-top:6px;">
                    <summary style="cursor:pointer; font-size:12px; color:#64748b; padding:4px 0;">
                        Show {{ $pending->count() }} pending · {{ $declined->count() }} declined
                    </summary>
                    <div style="display:flex; flex-direction:column; gap:6px; margin-top:8px;">
                        @foreach($pending->merge($declined) as $offer)
                            @php $color = \App\Models\SubstituteOffer::STATUS_COLORS[$offer->status] ?? 'gray'; @endphp
                            <div style="display:flex; align-items:center; justify-content:space-between; padding:6px 10px; border:1px solid #e5e7eb; border-radius:6px; background:#f9fafb; font-size:12px;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="font-weight:500;">{{ $offer->teacher?->name ?? 'Unknown' }}</span>
                                    <span style="color:#94a3b8;">· {{ $offer->teacher?->subject ?? '—' }}</span>
                                </div>
                                <span class="sp-doc-badge sp-doc-badge--{{ $color }}">{{ $offer->statusLabel() }}</span>
                            </div>
                        @endforeach
                    </div>
                </details>
            @endif
        </div>
    @endif

    <div class="sp-leave-modal__actions">
        <a href="{{ route('teacher-leave.print', $record) }}" target="_blank" rel="noopener" class="sp-doc-preview__download">
            Print Leave Letter
        </a>
    </div>
</div>
