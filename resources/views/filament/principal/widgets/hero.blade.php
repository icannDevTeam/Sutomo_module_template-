<div style="background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 60%,#f59e0b 110%);color:white;border-radius:1rem;padding:1.25rem 1.5rem;box-shadow:0 10px 30px -10px rgba(79,70,229,.45);position:relative;overflow:hidden;">
    <div style="position:absolute;inset:0;background-image:radial-gradient(circle at 0% 0%, rgba(255,255,255,.18), transparent 50%), radial-gradient(circle at 100% 100%, rgba(245,158,11,.25), transparent 55%);pointer-events:none;"></div>
    <div style="position:relative;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:1rem;">
        <div style="min-width:0;">
            <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.18em;opacity:.85;">{{ $today }} · {{ $time }} WIB</div>
            <div style="font-size:1.5rem;font-weight:700;margin-top:.25rem;">{{ $greeting }}, {{ $user }} 👋</div>
            <div style="opacity:.9;margin-top:.25rem;font-size:.9rem;">
                <strong>{{ number_format($students) }}</strong> active students ·
                <strong>{{ $pendingApps }}</strong> applications in pipeline ·
                <strong>{{ $observation }}</strong> on observation
            </div>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <a href="{{ url('/principal/applications') }}" style="background:rgba(255,255,255,.18);backdrop-filter:blur(6px);padding:.55rem .9rem;border-radius:.6rem;color:white;text-decoration:none;font-weight:500;font-size:.85rem;border:1px solid rgba(255,255,255,.25);">Enrollment</a>
            <a href="{{ url('/principal/behavior-logs') }}" style="background:rgba(255,255,255,.18);backdrop-filter:blur(6px);padding:.55rem .9rem;border-radius:.6rem;color:white;text-decoration:none;font-weight:500;font-size:.85rem;border:1px solid rgba(255,255,255,.25);">Behavior</a>
            <a href="{{ url('/principal/teacher-leaves') }}" style="background:rgba(255,255,255,.18);backdrop-filter:blur(6px);padding:.55rem .9rem;border-radius:.6rem;color:white;text-decoration:none;font-weight:500;font-size:.85rem;border:1px solid rgba(255,255,255,.25);">Leaves</a>
        </div>
    </div>
</div>
