@php $classes = $classes ?? collect(); @endphp
@if($classes->isEmpty())
    <div class="sp-homeroom-empty">Not assigned as a homeroom teacher for any class.</div>
@else
    <div class="sp-homeroom-grid">
        @foreach($classes as $c)
            <div class="sp-homeroom-card">
                <div class="sp-homeroom-card__code">{{ $c->code }}</div>
                <div class="sp-homeroom-card__name">{{ $c->name ?? '—' }}</div>
                <div class="sp-homeroom-card__meta">
                    @if($c->grade_level) Grade {{ $c->grade_level }} · @endif
                    {{ $c->students()->count() }} students
                </div>
            </div>
        @endforeach
    </div>
@endif
