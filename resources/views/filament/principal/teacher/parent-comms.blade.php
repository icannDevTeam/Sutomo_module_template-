@php
    // $summary: ['count'=>n,'last'=>Carbon|null] ; $recent: Collection
@endphp
<div class="sp-comm">
    <div class="sp-comm__stats">
        <span class="sp-comm__stat"><strong>{{ $summary['count'] ?? 0 }}</strong> total</span>
        @if(!empty($summary['last']))
            <span class="sp-comm__stat">Last contact: <strong>{{ \Illuminate\Support\Carbon::parse($summary['last'])->diffForHumans() }}</strong></span>
        @endif
    </div>
    <div class="sp-comm__list">
        @forelse($recent ?? [] as $c)
            <div class="sp-comm__row">
                <span class="sp-comm__method">{{ \App\Models\ParentCommunication::METHODS[$c->contact_method] ?? $c->contact_method }}</span>
                <span class="sp-comm__when">{{ $c->occurred_at->format('d M Y') }}</span>
                <span class="sp-comm__parent">{{ $c->parent_name ?? '—' }}</span>
                @if($c->student)<span class="sp-comm__student">(re: {{ $c->student->name }})</span>@endif
                @if($c->summary)<span class="sp-comm__summary">{{ $c->summary }}</span>@endif
            </div>
        @empty
            <div class="sp-comm__empty">No parent contacts logged.</div>
        @endforelse
    </div>
</div>
