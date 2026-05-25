@php
    $current = $current ?? null;
    $history = $history ?? collect();
    $fmt = fn($n) => 'Rp '.number_format((int)$n, 0, ',', '.');
@endphp
<div class="sp-comp">
    @if($current)
        <div class="sp-comp__current">
            <div class="sp-comp__current-label">Current package (effective {{ $current->effective_from->format('d M Y') }})</div>
            <div class="sp-comp__current-amount">{{ $fmt($current->total) }}<span class="sp-comp__currency">{{ $current->currency }}</span></div>
            <div class="sp-comp__breakdown">
                <span>Base: <strong>{{ $fmt($current->base_salary) }}</strong></span>
                @if(!empty($current->allowances))
                    @foreach($current->allowances as $k => $v)
                        <span>{{ $k }}: <strong>{{ $fmt($v) }}</strong></span>
                    @endforeach
                @endif
            </div>
            @if($current->notes)<div class="sp-comp__note">{{ $current->notes }}</div>@endif
        </div>
    @else
        <div class="sp-comp__empty">No compensation record on file.</div>
    @endif

    @if($history->isNotEmpty())
        <div class="sp-comp__history">
            <div class="sp-comp__history-title">History</div>
            @foreach($history as $h)
                <div class="sp-comp__row">
                    <span class="sp-comp__row-date">{{ $h->effective_from->format('d M Y') }}</span>
                    <span class="sp-comp__row-amount">{{ $fmt($h->total) }}</span>
                    @if($h->notes)<span class="sp-comp__row-note">{{ $h->notes }}</span>@endif
                </div>
            @endforeach
        </div>
    @endif
</div>
