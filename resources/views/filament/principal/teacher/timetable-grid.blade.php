@php
    use App\Services\Timetable\TeacherSchedule;
    $grid = $grid ?? [];
    $days = TeacherSchedule::DAYS;

    // Subject hash → stable color
    $palette = ['#6366f1','#0ea5e9','#10b981','#f59e0b','#ec4899','#8b5cf6','#14b8a6','#ef4444','#eab308'];
    $colorFor = function (?string $subject) use ($palette) {
        if (! $subject) return null;
        return $palette[abs(crc32($subject)) % count($palette)];
    };
@endphp

@if(empty($grid))
    <div class="sp-tt-empty">No published timetable for this teacher's code.</div>
@else
    <div class="sp-tt-wrap">
        <table class="sp-tt-grid">
            <thead>
                <tr>
                    <th class="sp-tt-period">Period</th>
                    @foreach($days as $d)
                        <th>{{ $d }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($grid as $period => $cols)
                    @php
                        $hasAny = collect($cols)->contains(fn ($l) => $l !== null);
                    @endphp
                    @if($hasAny)
                        <tr>
                            <th class="sp-tt-period">{{ $period }}</th>
                            @foreach($days as $d)
                                @php $lesson = $cols[$d] ?? null; @endphp
                                <td>
                                    @if($lesson)
                                        <div class="sp-tt-cell" style="background:{{ $colorFor($lesson->subject) }};">
                                            <div class="sp-tt-subject">{{ $lesson->subject }}</div>
                                            <div class="sp-tt-class">{{ $lesson->class_code }}</div>
                                            @if($lesson->room)<div class="sp-tt-room">{{ $lesson->room }}</div>@endif
                                        </div>
                                    @else
                                        <span class="sp-tt-empty-cell">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
@endif
