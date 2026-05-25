@php $classes = $classes ?? []; @endphp
@if(empty($classes) || (is_object($classes) && $classes->isEmpty()))
    <div class="sp-classes-empty">No classes assigned to this teacher's code in the active timetable.</div>
@else
    <div class="sp-classes-grid">
        @foreach($classes as $c)
            <div class="sp-class-chip">
                <div class="sp-class-chip__code">{{ $c['class_code'] }}</div>
                <div class="sp-class-chip__subject">{{ $c['subject'] }}</div>
            </div>
        @endforeach
    </div>
@endif
