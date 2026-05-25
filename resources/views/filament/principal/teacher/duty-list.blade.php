@php
    /** @var \Illuminate\Support\Collection $duties */
    use App\Models\DutyAssignment;
@endphp
@if ($duties->isEmpty())
    <div class="sp-teacher-empty">No duty assignments on record.</div>
@else
    <div class="sp-duty-list">
        @foreach ($duties as $d)
            <div class="sp-duty-row sp-duty-row--{{ $d->status }}">
                <div class="sp-duty-row__date">
                    <span class="sp-duty-row__day">{{ optional($d->starts_at)->format('d') }}</span>
                    <span class="sp-duty-row__mon">{{ optional($d->starts_at)->format('M') }}</span>
                </div>
                <div class="sp-duty-row__body">
                    <div class="sp-duty-row__title">{{ $d->title }}</div>
                    <div class="sp-duty-row__meta">
                        {{ optional($d->starts_at)->format('H:i') }}
                        @if ($d->ends_at) – {{ $d->ends_at->format('H:i') }} @endif
                        @if ($d->location) · {{ $d->location }} @endif
                    </div>
                </div>
                <span class="sp-leave-badge sp-leave-badge--{{ $d->status === 'completed' ? 'approved' : ($d->status === 'declined' ? 'rejected' : 'pending') }}">
                    {{ DutyAssignment::STATUSES[$d->status] ?? $d->status }}
                </span>
            </div>
        @endforeach
    </div>
@endif
