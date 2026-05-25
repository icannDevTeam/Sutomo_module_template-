<div class="sp-feed">
    @forelse($events ?? [] as $e)
        <div class="sp-feed__row">
            <span class="sp-feed__icon" style="background:{{ $e['color'] }}22;color:{{ $e['color'] }}">{{ $e['icon'] }}</span>
            <div class="sp-feed__body">
                <div class="sp-feed__verb">{{ $e['verb'] }}</div>
                @if(!empty($e['detail']))<div class="sp-feed__detail">{{ $e['detail'] }}</div>@endif
                <div class="sp-feed__meta">
                    <span>{{ \Illuminate\Support\Carbon::parse($e['date'])->diffForHumans() }}</span>
                    @if(!empty($e['actor']))<span>· by {{ $e['actor'] }}</span>@endif
                </div>
            </div>
        </div>
    @empty
        <div class="sp-feed__empty">No recent activity.</div>
    @endforelse
</div>
