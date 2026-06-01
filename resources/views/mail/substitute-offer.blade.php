@php
    use App\Models\TeacherLeave;
    $leaveTeacher = $leave?->teacher?->name ?? 'a teacher';
    $typeLabel = TeacherLeave::TYPES[$leave?->type ?? ''] ?? 'Leave';
    $from = optional($leave?->starts_at)->format('D, d M Y');
    $to   = optional($leave?->ends_at)->format('D, d M Y');
    $days = ($leave && $leave->starts_at && $leave->ends_at)
        ? $leave->starts_at->diffInDays($leave->ends_at) + 1
        : 1;
@endphp
@component('mail::message')
# Cover Request

Hi {{ $teacher?->name ?? 'there' }},

**{{ $leaveTeacher }}** has filed a **{{ $typeLabel }}** request and we'd like you to cover their classes.

@component('mail::panel')
**Teacher on leave:** {{ $leaveTeacher }}
**Type:** {{ $typeLabel }}
**From:** {{ $from }}
**To:** {{ $to }} ({{ $days }} day{{ $days === 1 ? '' : 's' }})
@if($leave?->reason)
**Reason:** {{ $leave->reason }}
@endif
@endcomponent

We've offered this slot to up to 3 teachers. **First to accept gets it** — others will be notified once it's filled.

@component('mail::button', ['url' => $acceptUrl, 'color' => 'success'])
✅ Accept Cover
@endcomponent

@component('mail::button', ['url' => $declineUrl, 'color' => 'error'])
❌ Decline
@endcomponent

These links expire on **{{ $expiresAt->format('D, d M Y · H:i') }}** (or sooner if the slot is filled).

Thanks,
Principal's Office · Sutomo School
@endcomponent
