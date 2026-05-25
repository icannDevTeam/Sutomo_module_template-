<div class="sp-obs-list">
    @forelse($observations ?? [] as $o)
        @php $avg = $o->average_score; $color = $avg === null ? '#94a3b8' : ($avg >= 4 ? '#16a34a' : ($avg >= 3 ? '#d97706' : '#dc2626')); @endphp
        <div class="sp-obs">
            <div class="sp-obs__head">
                <span class="sp-obs__date">{{ $o->observed_at->format('d M Y · H:i') }}</span>
                @if($avg !== null)<span class="sp-obs__avg" style="background:{{ $color }}">avg {{ $avg }}/5</span>@endif
            </div>
            <div class="sp-obs__meta">
                @if($o->lesson_subject)<strong>{{ $o->lesson_subject }}</strong>@endif
                @if($o->lesson_class_code) · {{ $o->lesson_class_code }}@endif
                @if($o->observer)· observed by {{ $o->observer->name }}@endif
            </div>
            @if(!empty($o->dimensions))
                <div class="sp-obs__dims">
                    @foreach(\App\Models\TeacherObservation::DIMENSIONS as $key => $label)
                        @if(isset($o->dimensions[$key]) && $o->dimensions[$key] !== null)
                            <span class="sp-obs__dim">{{ $label }}: <strong>{{ $o->dimensions[$key] }}/5</strong></span>
                        @endif
                    @endforeach
                </div>
            @endif
            @if($o->strengths)<div class="sp-obs__sec"><b>Strengths:</b> {{ $o->strengths }}</div>@endif
            @if($o->action_items)<div class="sp-obs__sec"><b>Action items:</b> {{ $o->action_items }}</div>@endif
            @if($o->follow_up_date)<div class="sp-obs__follow">📌 Follow-up: {{ $o->follow_up_date->format('d M Y') }}</div>@endif
        </div>
    @empty
        <div class="sp-obs-empty">No observations recorded yet.</div>
    @endforelse
</div>
