@php
    // $days: array of [['date'=>Carbon,'status'=>string],...]  last 30
    $colors = \App\Models\TeacherAttendance::STATUS_COLORS;
    $palette = ['success'=>'#22c55e','warning'=>'#f59e0b','danger'=>'#ef4444','info'=>'#3b82f6','gray'=>'#cbd5e1'];
@endphp
<div class="sp-att-cal">
    @if(empty($days))
        <div class="sp-att-cal-empty">No attendance records yet.</div>
    @else
        @foreach($days as $d)
            @php $c = $palette[$colors[$d['status']] ?? 'gray']; @endphp
            <div class="sp-att-cal__dot" style="background:{{ $c }}"
                 title="{{ \Illuminate\Support\Carbon::parse($d['date'])->format('d M Y') }} — {{ \App\Models\TeacherAttendance::STATUSES[$d['status']] ?? $d['status'] }}"></div>
        @endforeach
    @endif
</div>
