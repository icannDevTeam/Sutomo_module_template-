@php
    $palette = ['success'=>'#16a34a','warning'=>'#d97706','danger'=>'#dc2626','info'=>'#2563eb','gray'=>'#6b7280'];
    $colors = \App\Models\TeacherGoal::STATUS_COLORS;
@endphp
<div class="sp-goal-list">
    @forelse($goals ?? [] as $g)
        @php $c = $palette[$colors[$g->status] ?? 'gray']; $p = (int) $g->progress; @endphp
        <div class="sp-goal">
            <div class="sp-goal__head">
                <span class="sp-goal__title">{{ $g->title }}</span>
                <span class="sp-goal__status" style="background:{{ $c }}">{{ \App\Models\TeacherGoal::STATUSES[$g->status] ?? $g->status }}</span>
            </div>
            @if($g->description)<div class="sp-goal__desc">{{ $g->description }}</div>@endif
            <div class="sp-goal__bar"><div class="sp-goal__fill" style="width:{{ $p }}%; background:{{ $c }}"></div></div>
            <div class="sp-goal__meta">
                <span>{{ $p }}% complete</span>
                @if($g->target_date)<span>· target {{ $g->target_date->format('d M Y') }}</span>@endif
                @if($g->academic_year)<span>· AY {{ $g->academic_year }}</span>@endif
            </div>
        </div>
    @empty
        <div class="sp-goal-empty">No goals set for this academic year.</div>
    @endforelse
</div>
