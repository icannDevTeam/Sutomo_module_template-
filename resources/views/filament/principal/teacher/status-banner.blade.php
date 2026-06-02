@php
    use App\Models\TeacherLeave;
    use Carbon\Carbon;

    $today = Carbon::today();
    $activeLeave = TeacherLeave::where('teacher_id', $teacher->id)
        ->where('status', 'approved')
        ->whereDate('starts_at', '<=', $today)
        ->whereDate('ends_at', '>=', $today)
        ->first();

    $statusText = $activeLeave
        ? 'On Leave (' . \App\Models\LeaveType::labelFor($activeLeave->type) . ')'
        : 'Available';
    $statusTone = $activeLeave ? 'warning' : 'success';

    $now = $current ?? null;
    $nowText = $now
        ? 'Now: Period ' . $now->period . ' · ' . $now->subject . ' · ' . $now->class_code
        : 'No lesson scheduled right now';
@endphp

<div class="sp-banner sp-banner--{{ $statusTone }}">
    <div class="sp-banner__left">
        <span class="sp-banner__pulse"></span>
        <div>
            <div class="sp-banner__status">{{ $statusText }}</div>
            <div class="sp-banner__now">{{ $nowText }}</div>
        </div>
    </div>
    <div class="sp-banner__right">
        <div class="sp-banner__date">{{ $today->format('l, d M Y') }}</div>
    </div>
</div>
