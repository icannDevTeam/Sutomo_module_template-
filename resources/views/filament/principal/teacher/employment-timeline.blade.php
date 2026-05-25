@php
    $palette = ['success'=>'#16a34a','warning'=>'#d97706','danger'=>'#dc2626','info'=>'#2563eb','gray'=>'#6b7280'];
    $colors = \App\Models\TeacherEmploymentEvent::TYPE_COLORS;
@endphp
<div class="sp-emp-timeline">
    @forelse($events ?? [] as $e)
        @php $c = $palette[$colors[$e->event_type] ?? 'gray']; @endphp
        <div class="sp-emp-event">
            <div class="sp-emp-event__dot" style="background:{{ $c }}"></div>
            <div class="sp-emp-event__body">
                <div class="sp-emp-event__head">
                    <span class="sp-emp-event__type" style="color:{{ $c }}">{{ \App\Models\TeacherEmploymentEvent::TYPES[$e->event_type] ?? $e->event_type }}</span>
                    <span class="sp-emp-event__date">{{ $e->event_date->format('d M Y') }}</span>
                </div>
                @if($e->from_value || $e->to_value)
                    <div class="sp-emp-event__change">
                        @if($e->from_value)<span class="sp-emp-event__from">{{ $e->from_value }}</span> →@endif
                        @if($e->to_value)<span class="sp-emp-event__to">{{ $e->to_value }}</span>@endif
                    </div>
                @endif
                @if($e->note)<div class="sp-emp-event__note">{{ $e->note }}</div>@endif
            </div>
        </div>
    @empty
        <div class="sp-emp-empty">No employment events recorded.</div>
    @endforelse
</div>
