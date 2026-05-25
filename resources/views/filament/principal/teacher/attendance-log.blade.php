@php
    $colors = \App\Models\TeacherAttendance::STATUS_COLORS;
    $palette = ['success'=>'#16a34a','warning'=>'#d97706','danger'=>'#dc2626','info'=>'#2563eb','gray'=>'#6b7280'];
@endphp
<div class="sp-att-log">
    @forelse($logs ?? [] as $a)
        @php $c = $palette[$colors[$a->status] ?? 'gray']; @endphp
        <div class="sp-att-row">
            <span class="sp-att-row__date">{{ $a->date->format('D, d M Y') }}</span>
            <span class="sp-att-row__status" style="background:{{ $c }}">{{ \App\Models\TeacherAttendance::STATUSES[$a->status] ?? $a->status }}</span>
            <span class="sp-att-row__time">
                @if($a->check_in_at){{ $a->check_in_at->format('H:i') }}@endif
                @if($a->check_out_at) – {{ $a->check_out_at->format('H:i') }}@endif
            </span>
            @if($a->note)<span class="sp-att-row__note">{{ $a->note }}</span>@endif
        </div>
    @empty
        <div class="sp-att-log-empty">No log entries.</div>
    @endforelse
</div>
