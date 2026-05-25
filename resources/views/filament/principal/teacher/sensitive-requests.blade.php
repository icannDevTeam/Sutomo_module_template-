<div class="sp-sreq">
    @forelse($requests ?? [] as $r)
        <div class="sp-sreq__row">
            <span class="sp-sreq__type">{{ \App\Models\SensitiveApprovalRequest::ACTION_TYPES[$r->action_type] ?? $r->action_type }}</span>
            <span class="sp-sreq__status sp-sreq__status--{{ \App\Models\SensitiveApprovalRequest::STATUS_COLORS[$r->status] ?? 'gray' }}">
                {{ \App\Models\SensitiveApprovalRequest::STATUSES[$r->status] ?? $r->status }}
            </span>
            <span class="sp-sreq__when">{{ $r->created_at->diffForHumans() }}</span>
            <a class="sp-sreq__link" href="{{ url('/principal/sensitive-approvals/'.$r->id) }}">Review →</a>
        </div>
    @empty
        <div class="sp-sreq__empty">No pending sensitive requests.</div>
    @endforelse
</div>
