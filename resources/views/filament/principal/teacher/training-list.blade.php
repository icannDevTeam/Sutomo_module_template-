@php
    /** @var \Illuminate\Support\Collection $trainings */
    use App\Models\TeacherTraining;
@endphp
@if ($trainings->isEmpty())
    <div class="sp-teacher-empty">No training history yet.</div>
@else
    <div class="sp-train-list">
        @foreach ($trainings as $t)
            <div class="sp-train-row sp-train-row--{{ $t->status }}">
                <div class="sp-train-row__cat sp-train-row__cat--{{ $t->category ?? 'pedagogy' }}">
                    {{ TeacherTraining::CATEGORIES[$t->category] ?? '—' }}
                </div>
                <div class="sp-train-row__body">
                    <div class="sp-train-row__title">{{ $t->title }}</div>
                    <div class="sp-train-row__meta">
                        @if ($t->provider) {{ $t->provider }} · @endif
                        @if ($t->starts_on) {{ $t->starts_on->format('M Y') }} @endif
                        @if ($t->hours) · {{ $t->hours }} hrs @endif
                    </div>
                    @if ($t->notes)<div class="sp-train-row__notes">{{ $t->notes }}</div>@endif
                </div>
                <span class="sp-leave-badge sp-leave-badge--{{ $t->status === 'completed' ? 'approved' : 'pending' }}">
                    {{ TeacherTraining::STATUSES[$t->status] ?? $t->status }}
                </span>
            </div>
        @endforeach
    </div>
@endif
