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
# Cover Request — Express Interest

Hi {{ $teacher?->name ?? 'there' }},

**{{ $leaveTeacher }}** has a **{{ $typeLabel }}** request and the principal is opening a cover search.

@component('mail::panel')
**Teacher on leave:** {{ $leaveTeacher }}
**Type:** {{ $typeLabel }}
**From:** {{ $from }}
**To:** {{ $to }} ({{ $days }} day{{ $days === 1 ? '' : 's' }})
@if($leave?->reason)
**Reason:** {{ $leave->reason }}
@endif
@endcomponent

If you can cover this slot, click **I'm Interested** below. The principal will review everyone who expressed interest and confirm the final substitute.

@component('mail::button', ['url' => $acceptUrl, 'color' => 'success'])
I'm Interested
@endcomponent

@component('mail::button', ['url' => $declineUrl, 'color' => 'error'])
Not Available
@endcomponent

This invitation expires on **{{ $expiresAt->format('D, d M Y · H:i') }}** (or sooner if enough teachers have expressed interest).

Thanks,
Principal's Office · Sutomo School
@endcomponent
