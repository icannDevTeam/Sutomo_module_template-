<div class="fi-wi" style="margin-bottom:0">
    <div style="
        position:relative;
        overflow:hidden;
        border-radius:1rem;
        padding:1.1rem 1.4rem;
        color:#ffffff;
        background-image:
            radial-gradient(circle at 92% 130%, rgba(14,165,233,.55) 0%, transparent 55%),
            radial-gradient(circle at 0% -20%, rgba(124,58,237,.45) 0%, transparent 55%),
            linear-gradient(135deg,#1e3a8a 0%,#1d4ed8 38%,#2563eb 70%,#0ea5e9 100%);
        box-shadow:0 12px 28px -16px rgba(29,78,216,.55);
    ">
        {{-- decorative blob --}}
        <div style="position:absolute;right:-3rem;top:-4rem;width:14rem;height:14rem;border-radius:9999px;background:rgba(255,255,255,.10);filter:blur(48px);pointer-events:none;"></div>

        <div style="position:relative;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:1rem 1.25rem;">
            {{-- Left: greeting --}}
            <div style="min-width:0;flex:1 1 280px;">
                <div style="display:inline-flex;align-items:center;gap:.45rem;padding:.2rem .6rem;border-radius:9999px;background:rgba(255,255,255,.14);font-size:10.5px;letter-spacing:.08em;text-transform:uppercase;font-weight:600;">
                    <span style="display:inline-block;width:.4rem;height:.4rem;border-radius:9999px;background:#34d399;box-shadow:0 0 0 3px rgba(52,211,153,.25);"></span>
                    {{ $today }} · {{ $time }} WIB
                </div>
                <h1 style="margin:.4rem 0 .15rem;font-size:1.35rem;font-weight:700;line-height:1.2;letter-spacing:-.01em;">
                    {{ $greeting }}, {{ $user }} 👋
                </h1>
                <p style="margin:0;font-size:.82rem;color:rgba(255,255,255,.85);">
                    <b style="color:#fff;">{{ $interviewsToday }}</b> interview{{ $interviewsToday === 1 ? '' : 's' }} today ·
                    <b style="color:#fff;">{{ $pending }}</b> approval{{ $pending === 1 ? '' : 's' }} pending ·
                    <b style="color:#fff;">{{ $openVacancies }}</b> open vacancies ·
                    <b style="color:#fff;">{{ $newThisWeek }}</b> new this week
                </p>
            </div>

            {{-- Right: actions --}}
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
                <a href="{{ route('filament.admin.pages.pipeline') }}"
                   style="display:inline-flex;align-items:center;gap:.4rem;border-radius:.6rem;background:rgba(255,255,255,.16);padding:.5rem .85rem;font-size:.8rem;font-weight:500;color:#fff;text-decoration:none;border:1px solid rgba(255,255,255,.18);">
                    <x-filament::icon icon="heroicon-m-squares-2x2" style="width:.95rem;height:.95rem;" /> Pipeline
                </a>
                <a href="{{ route('filament.admin.pages.yayasan-approvals') }}"
                   style="display:inline-flex;align-items:center;gap:.4rem;border-radius:.6rem;background:#ffffff;color:#1d4ed8;padding:.5rem .85rem;font-size:.8rem;font-weight:600;text-decoration:none;box-shadow:0 3px 10px -3px rgba(0,0,0,.25);">
                    <x-filament::icon icon="heroicon-m-building-library" style="width:.95rem;height:.95rem;" /> Yayasan
                </a>
                <a href="{{ route('filament.admin.resources.candidates.create') }}"
                   style="display:inline-flex;align-items:center;gap:.4rem;border-radius:.6rem;background:rgba(16,185,129,.95);padding:.5rem .85rem;font-size:.8rem;font-weight:600;color:#fff;text-decoration:none;box-shadow:0 3px 10px -3px rgba(16,185,129,.6);">
                    <x-filament::icon icon="heroicon-m-plus" style="width:.95rem;height:.95rem;" /> New candidate
                </a>
            </div>
        </div>
    </div>
</div>
