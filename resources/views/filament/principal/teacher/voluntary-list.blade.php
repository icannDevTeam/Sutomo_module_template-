@php
    /** @var \Illuminate\Support\Collection $items */
    use App\Models\VoluntaryRequest;
@endphp
@if ($items->isEmpty())
    <div class="sp-teacher-empty">No voluntary requests on record.</div>
@else
    <div class="sp-vol-list">
        @foreach ($items as $v)
            <div class="sp-vol-row sp-vol-row--{{ $v->status }}">
                <div class="sp-vol-row__body">
                    <div class="sp-vol-row__title">{{ $v->program }}</div>
                    <div class="sp-vol-row__meta">
                        Submitted {{ optional($v->submitted_at ?? $v->created_at)->diffForHumans() }}
                        @if ($v->decided_by) · decided by {{ $v->decided_by }}@endif
                    </div>
                    @if ($v->reason)<div class="sp-vol-row__reason">{{ $v->reason }}</div>@endif
                    @if ($v->decision_note)<div class="sp-vol-row__note">Note: {{ $v->decision_note }}</div>@endif
                </div>
                <span class="sp-leave-badge sp-leave-badge--{{ $v->status === 'approved' ? 'approved' : ($v->status === 'declined' ? 'rejected' : 'pending') }}">
                    {{ VoluntaryRequest::STATUSES[$v->status] ?? $v->status }}
                </span>
            </div>
        @endforeach
    </div>
@endif
