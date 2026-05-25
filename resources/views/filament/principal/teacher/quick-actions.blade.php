@php
    $tid = $teacher->id;
    $phoneE164 = $teacher->phone ? preg_replace('/[^\d]/', '', $teacher->phone) : null;
@endphp
<div class="sp-qa">
    <a class="sp-qa__btn" href="{{ url("/principal/teacher-leaves/create?teacher_id={$tid}") }}">🌴 Log Leave</a>
    <a class="sp-qa__btn" href="{{ url("/principal/duty-assignments/create?teacher_id={$tid}") }}">📋 Assign Duty</a>
    <a class="sp-qa__btn" href="{{ url("/principal/teacher-observations/create?teacher_id={$tid}") }}">👁 Add Observation</a>
    @if($teacher->email)
        <a class="sp-qa__btn" href="mailto:{{ $teacher->email }}?subject=Re%3A%20Sutomo">✉️ Email</a>
    @endif
    @if($phoneE164)
        <a class="sp-qa__btn sp-qa__btn--wa" target="_blank" href="https://wa.me/{{ $phoneE164 }}">💬 WhatsApp</a>
    @endif
    <button class="sp-qa__btn sp-qa__btn--print" onclick="window.print()">🖨 Print Profile</button>
</div>
