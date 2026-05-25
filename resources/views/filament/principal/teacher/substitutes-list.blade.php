@php
    // $pinned: Collection<Teacher> with pivot rank+note ; $suggested: Collection<Teacher>
@endphp
<div class="sp-sub-wrap">
    <div class="sp-sub-section">
        <div class="sp-sub-section__title">⭐ Preferred (ranked)</div>
        @forelse($pinned ?? [] as $sub)
            @include('filament.principal.teacher._sub-card', ['sub' => $sub, 'pinned' => true])
        @empty
            <div class="sp-sub-empty">No preferred substitutes pinned yet.</div>
        @endforelse
    </div>
    <div class="sp-sub-section">
        <div class="sp-sub-section__title">🤖 Auto-suggested</div>
        @forelse($suggested ?? [] as $sub)
            @include('filament.principal.teacher._sub-card', ['sub' => $sub, 'pinned' => false])
        @empty
            <div class="sp-sub-empty">No matches in the same campus/subject.</div>
        @endforelse
    </div>
</div>
