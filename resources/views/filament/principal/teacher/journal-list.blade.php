<div class="sp-journal-list">
    @forelse($entries ?? [] as $j)
        <div class="sp-journal">
            <div class="sp-journal__head">
                <span class="sp-journal__date">{{ $j->entry_date->format('d M Y') }}</span>
                @if($j->is_private)<span class="sp-journal__badge">🔒 Private</span>@endif
                @if($j->shared_with_mentor)<span class="sp-journal__badge sp-journal__badge--shared">👥 Shared with mentor</span>@endif
            </div>
            <div class="sp-journal__body">{{ $j->body }}</div>
        </div>
    @empty
        <div class="sp-journal-empty">No journal entries.</div>
    @endforelse
</div>
