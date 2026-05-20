@php
    $isImage = in_array($ext, ['png','jpg','jpeg','gif','webp']);
    $isPdf   = $ext === 'pdf';
@endphp

<div class="sp-rcpt">
    {{-- Summary strip --}}
    <div class="sp-rcpt-meta">
        <div>
            <div class="sp-rcpt-lbl">Applicant</div>
            <div class="sp-rcpt-val">{{ $application->name }}</div>
            <div class="sp-rcpt-sub">{{ $application->code }} · {{ strtoupper($application->campus ?? '—') }} · Grade {{ $application->grade }}</div>
        </div>
        <div>
            <div class="sp-rcpt-lbl">Method</div>
            <div class="sp-rcpt-val">{{ \App\Models\Application::PAYMENT_METHODS[$application->payment_method] ?? '—' }}</div>
            <div class="sp-rcpt-sub">Invoice {{ $application->invoice_no ?? '—' }}</div>
        </div>
        <div>
            <div class="sp-rcpt-lbl">Amount</div>
            <div class="sp-rcpt-val">Rp {{ number_format((float) $application->payment_amount, 0, ',', '.') }}</div>
            <div class="sp-rcpt-sub">Paid {{ $application->payment_paid_at?->format('d M Y H:i') ?? '—' }}</div>
        </div>
        <div>
            <div class="sp-rcpt-lbl">Wali</div>
            <div class="sp-rcpt-val">{{ $application->parent_name }}</div>
            <div class="sp-rcpt-sub">{{ $application->parent_phone }}</div>
        </div>
    </div>

    {{-- Preview --}}
    <div class="sp-rcpt-frame">
        @if ($isImage)
            <img src="{{ $url }}" alt="Receipt preview" />
        @elseif ($isPdf)
            <iframe src="{{ $url }}#toolbar=0" loading="lazy"></iframe>
        @else
            <div class="sp-rcpt-fallback">
                <x-filament::icon icon="heroicon-o-document" style="width:40px;height:40px;color:#94a3b8;" />
                <div style="margin-top:.6rem;font-weight:600;">Preview not available</div>
                <div style="font-size:.82rem;color:#64748b;margin-top:.25rem;">Open the file in a new tab to inspect it.</div>
            </div>
        @endif
    </div>

    <div class="sp-rcpt-actions">
        <a href="{{ $url }}" target="_blank" rel="noopener" class="sp-rcpt-link">
            <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" style="width:16px;height:16px;" />
            Open in new tab
        </a>
        <span class="sp-rcpt-hint">Pressing <b>Confirm payment</b> moves this applicant into the Confirmed queue.</span>
    </div>
</div>
