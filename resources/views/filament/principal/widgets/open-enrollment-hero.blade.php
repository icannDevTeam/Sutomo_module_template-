@php
    $isOpen = ($period?->status ?? 'draft') === 'open';
@endphp
<a href="{{ route('filament.principal.pages.open-enrollment') }}"
   class="sp-oe-cta {{ $isOpen ? 'is-open' : 'is-closed' }}">
    <div class="sp-oe-cta-glow"></div>
    <div class="sp-oe-cta-body">
        <div class="sp-oe-cta-eyebrow">
            <span class="sp-oe-dot"></span>
            Enrollment {{ $isOpen ? 'OPEN' : 'CLOSED' }}
            @if ($period) · {{ $period->name }} @endif
        </div>
        <div class="sp-oe-cta-title">Open Enrollment</div>
        <div class="sp-oe-cta-sub">
            One hub for enrollment info, applications, onboarding flow and approvals.
        </div>
    </div>
    <div class="sp-oe-cta-stats">
        <div><span>{{ number_format($total) }}</span><small>Applications</small></div>
        <div><span>{{ number_format($incoming) }}</span><small>Pending payment</small></div>
        <div><span>{{ number_format($accepted) }}</span><small>In onboarding</small></div>
    </div>
    <div class="sp-oe-cta-arrow">
        <x-filament::icon icon="heroicon-m-arrow-right" style="width:20px;height:20px;" />
    </div>
</a>
