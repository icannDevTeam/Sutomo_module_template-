<div class="sp-audit">
    @forelse($logs ?? [] as $a)
        <div class="sp-audit__row">
            <span class="sp-audit__when">{{ \Illuminate\Support\Carbon::parse($a->occurred_at ?? $a->created_at)->format('d M Y H:i') }}</span>
            <span class="sp-audit__action">{{ $a->action }}</span>
            <span class="sp-audit__actor">{{ $a->user_name ?? '—' }} ({{ $a->role ?? '—' }})</span>
            @if($a->from_value || $a->to_value)
                <span class="sp-audit__change">
                    @if($a->from_value)<span class="sp-audit__from">{{ $a->from_value }}</span> →@endif
                    @if($a->to_value)<span class="sp-audit__to">{{ $a->to_value }}</span>@endif
                </span>
            @endif
            @if($a->note)<span class="sp-audit__note">{{ $a->note }}</span>@endif
        </div>
    @empty
        <div class="sp-audit__empty">No audit log entries for this teacher.</div>
    @endforelse
</div>
