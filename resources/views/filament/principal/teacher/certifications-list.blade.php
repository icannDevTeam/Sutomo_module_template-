@php
    $gov = collect($certifications ?? [])->where('is_government_approved', true);
    $other = collect($certifications ?? [])->where('is_government_approved', false);
    $statusColor = fn($s) => match(true) {
        $s === 'expired'      => '#dc2626',
        $s === 'expiring_30'  => '#ea580c',
        $s === 'expiring_60'  => '#d97706',
        $s === 'expiring_90'  => '#ca8a04',
        default               => '#16a34a',
    };
@endphp
<div class="sp-cert-wrap">
    @if($gov->isEmpty() && $other->isEmpty())
        <div class="sp-cert-empty">No formal certifications recorded.</div>
    @endif
    @if($gov->isNotEmpty())
        <div class="sp-cert-group">
            <div class="sp-cert-group__title">🇮🇩 Government Approved</div>
            <div class="sp-cert-list">
                @foreach($gov as $c)
                    <div class="sp-cert">
                        <div class="sp-cert__top">
                            <span class="sp-cert__name">{{ $c->name }}</span>
                            @if($c->expires_at)
                                <span class="sp-cert__badge" style="background:{{ $statusColor($c->expiry_status) }}">
                                    {{ $c->expires_at->format('d M Y') }}
                                </span>
                            @endif
                        </div>
                        <div class="sp-cert__meta">
                            @if($c->issuer)<span>{{ $c->issuer }}</span>@endif
                            @if($c->accreditation_no)<span>· #{{ $c->accreditation_no }}</span>@endif
                            @if($c->country)<span>· {{ $c->country }}</span>@endif
                        </div>
                        @if($c->file_path)
                            <a class="sp-cert__file" target="_blank" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($c->file_path) }}">📄 Download</a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    @if($other->isNotEmpty())
        <div class="sp-cert-group">
            <div class="sp-cert-group__title">🌍 Other / International</div>
            <div class="sp-cert-list">
                @foreach($other as $c)
                    <div class="sp-cert sp-cert--other">
                        <div class="sp-cert__top">
                            <span class="sp-cert__name">{{ $c->name }}</span>
                            @if($c->expires_at)
                                <span class="sp-cert__badge" style="background:{{ $statusColor($c->expiry_status) }}">{{ $c->expires_at->format('d M Y') }}</span>
                            @endif
                        </div>
                        <div class="sp-cert__meta">
                            @if($c->issuer)<span>{{ $c->issuer }}</span>@endif
                            @if($c->country && $c->country !== 'ID')<span>· {{ $c->country }}</span>@endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
