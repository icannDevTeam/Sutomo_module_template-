<div class="sp-mentor">
    @if($mentor ?? null)
        <div class="sp-mentor__line">
            <span class="sp-mentor__label">Mentored by:</span>
            <span class="sp-mentor__value"><strong>{{ $mentor->name }}</strong> @if($mentor->title) · <em>{{ \App\Models\Teacher::TITLES[$mentor->title] ?? $mentor->title }}</em>@endif</span>
        </div>
    @else
        <div class="sp-mentor__line sp-mentor__line--muted">No mentor assigned.</div>
    @endif
    <div class="sp-mentor__line">
        <span class="sp-mentor__label">Mentoring:</span>
        @if(($mentees ?? collect())->isNotEmpty())
            <span class="sp-mentor__value">
                @foreach($mentees as $m)
                    <span class="sp-mentor__chip">{{ $m->name }}</span>
                @endforeach
            </span>
        @else
            <span class="sp-mentor__value sp-mentor__value--muted">No mentees.</span>
        @endif
    </div>
</div>
