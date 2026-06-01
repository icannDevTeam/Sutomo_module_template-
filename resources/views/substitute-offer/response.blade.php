@php
    use App\Models\TeacherLeave;
    $leave = $offer->leave;
    $teacher = $offer->teacher;
    $leaveTeacher = $leave?->teacher?->name ?? 'a teacher';
    $typeLabel = TeacherLeave::TYPES[$leave?->type ?? ''] ?? 'Leave';

    $palette = [
        'interested' => ['bg' => '#eff6ff', 'border' => '#3b82f6', 'icon' => '🙋', 'title' => 'Interest Recorded'],
        'assigned'   => ['bg' => '#ecfdf5', 'border' => '#10b981', 'icon' => '✅', 'title' => 'You Got The Cover'],
        'declined'   => ['bg' => '#fef2f2', 'border' => '#ef4444', 'icon' => '❌', 'title' => 'Marked Not Available'],
        'filled'     => ['bg' => '#fffbeb', 'border' => '#f59e0b', 'icon' => '⏳', 'title' => 'Substitute Already Assigned'],
        'closed'     => ['bg' => '#f3f4f6', 'border' => '#9ca3af', 'icon' => '🚪', 'title' => 'Cover Search Closed'],
        'expired'    => ['bg' => '#f3f4f6', 'border' => '#9ca3af', 'icon' => '⌛', 'title' => 'Invitation Expired'],
        'error'      => ['bg' => '#fef2f2', 'border' => '#ef4444', 'icon' => '⚠️', 'title' => 'Something Went Wrong'],
    ][$state] ?? ['bg' => '#f3f4f6', 'border' => '#9ca3af', 'icon' => 'ℹ️', 'title' => 'Notice'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cover Request — Sutomo School</title>
<style>
    body { margin:0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; background:#f8fafc; color:#0f172a; }
    .wrap { max-width:560px; margin:48px auto; padding:24px; }
    .brand { text-align:center; font-weight:700; font-size:14px; letter-spacing:.08em; color:#475569; margin-bottom:20px; text-transform:uppercase; }
    .card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:28px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
    .badge { display:inline-flex; align-items:center; gap:8px; padding:10px 16px; border-radius:999px; background:{{ $palette['bg'] }}; border:1px solid {{ $palette['border'] }}; font-weight:600; font-size:14px; }
    .icon { font-size:18px; }
    .title { font-size:22px; font-weight:700; margin:16px 0 6px; }
    .msg { color:#334155; line-height:1.5; margin-bottom:20px; }
    .details { background:#f8fafc; border-radius:10px; padding:16px; border:1px solid #e2e8f0; font-size:14px; }
    .row { display:flex; justify-content:space-between; padding:6px 0; }
    .row + .row { border-top:1px solid #e2e8f0; }
    .label { color:#64748b; }
    .val { font-weight:600; }
    .footer { text-align:center; color:#94a3b8; font-size:12px; margin-top:24px; }
</style>
</head>
<body>
<div class="wrap">
    <div class="brand">Sutomo School · Principal's Office</div>
    <div class="card">
        <div class="badge"><span class="icon">{{ $palette['icon'] }}</span> {{ $palette['title'] }}</div>
        <div class="title">Hi {{ $teacher?->name ?? 'there' }},</div>
        <div class="msg">{{ $message }}</div>

        @if($leave)
        <div class="details">
            <div class="row"><span class="label">Teacher on leave</span><span class="val">{{ $leaveTeacher }}</span></div>
            <div class="row"><span class="label">Type</span><span class="val">{{ $typeLabel }}</span></div>
            <div class="row"><span class="label">From</span><span class="val">{{ optional($leave->starts_at)->format('D, d M Y') }}</span></div>
            <div class="row"><span class="label">To</span><span class="val">{{ optional($leave->ends_at)->format('D, d M Y') }}</span></div>
            @if($leave->reason)
            <div class="row"><span class="label">Reason</span><span class="val">{{ $leave->reason }}</span></div>
            @endif
        </div>
        @endif
    </div>
    <div class="footer">This is an automated message. You can close this window.</div>
</div>
</body>
</html>
