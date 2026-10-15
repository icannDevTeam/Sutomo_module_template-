@props(['events', 'limit' => null])
@php
    $tone = fn (string $t) => match (true) {
        str_starts_with($t, 'decision.approved'), $t === 'document.accepted', $t === 'signing.completed', $t === 'hr_registration.acknowledged', $t === 'completion.marked' => 'approved',
        $t === 'decision.rejected', $t === 'cancellation.marked', $t === 'request.withdrawn' => 'rejected',
        $t === 'correction.requested', $t === 'document.correction', $t === 'hr.reassigned', $t === 'nudge.sent' => 'waiting',
        $t === 'review.started', $t === 'review.taken_over', $t === 'hr.in_progress' => 'review',
        default => 'neutral',
    };
    $sorted = $events->sortByDesc('occurred_at')->values();
    if ($limit) $sorted = $sorted->take($limit);
    $byDay = $sorted->groupBy(fn ($e) => $e->occurred_at?->toDateString() ?? 'unknown');
@endphp
<div {{ $attributes->merge(['class' => 'ap-timeline']) }}>
    @forelse($byDay as $day => $items)
        @php
            $dayLabel = $day === 'unknown' ? 'Undated'
                : (\Illuminate\Support\Carbon::parse($day)->isToday() ? 'Today' : \Illuminate\Support\Carbon::parse($day)->format('d M Y'));
        @endphp
        <div class="ap-tl-day">{{ $dayLabel }}</div>
        @foreach($items as $e)
            <div class="ap-tl ap-tl--{{ $tone($e->event_type) }}">
                <div class="ap-tl__what">{{ \App\Models\ContractRequestEvent::TYPES[$e->event_type] ?? $e->event_type }}</div>
                @if($e->note)<div class="ap-tl__note">{{ $e->note }}</div>@endif
                <div class="ap-tl__who">{{ $e->actor_name ?? 'System' }}@if($e->actor_role) · {{ ucfirst(str_replace('_', ' ', $e->actor_role)) }}@endif · {{ $e->occurred_at?->format('H:i') }}</div>
            </div>
        @endforeach
    @empty
        <p class="ap-muted" style="font-size:.84rem;margin:0">No activity recorded.</p>
    @endforelse
</div>
