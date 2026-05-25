@php
    $rank = $pinned && isset($sub->pivot) ? (int) $sub->pivot->rank : null;
    $note = $pinned && isset($sub->pivot) ? $sub->pivot->note : null;
    $phoneE164 = $sub->phone ? preg_replace('/[^\d]/','',$sub->phone) : null;
    $msg = rawurlencode("Hi {$sub->name}, can you cover a lesson? Reply if you're available — thanks!");
@endphp
<div class="sp-sub {{ $pinned ? 'sp-sub--pinned' : 'sp-sub--auto' }}">
    @if($sub->avatar_url)
        <img class="sp-sub__avatar" src="{{ $sub->avatar_url }}" alt="">
    @else
        <div class="sp-sub__avatar sp-sub__avatar--initials">{{ collect(explode(' ', $sub->name))->take(2)->map(fn($w)=>strtoupper(substr($w,0,1)))->implode('') }}</div>
    @endif
    <div class="sp-sub__body">
        <div class="sp-sub__head">
            @if($rank)<span class="sp-sub__rank">#{{ $rank }}</span>@endif
            <span class="sp-sub__name">{{ $sub->name }}</span>
            @if($sub->title)<span class="sp-sub__title">{{ \App\Models\Teacher::TITLES[$sub->title] ?? $sub->title }}</span>@endif
        </div>
        <div class="sp-sub__meta">
            @if($sub->subject)<span>{{ $sub->subject }}</span>@endif
            @if($sub->campus)<span>· {{ strtoupper($sub->campus) }}</span>@endif
        </div>
        @if($note)<div class="sp-sub__note">"{{ $note }}"</div>@endif
        <div class="sp-sub__actions">
            @if($sub->phone)
                <a class="sp-sub__btn" href="tel:{{ $sub->phone }}">📞 Call</a>
                <a class="sp-sub__btn sp-sub__btn--wa" target="_blank" href="https://wa.me/{{ $phoneE164 }}?text={{ $msg }}">💬 WhatsApp</a>
            @endif
            @if($sub->email)
                <a class="sp-sub__btn" href="mailto:{{ $sub->email }}">✉️ Email</a>
            @endif
        </div>
    </div>
</div>
