@php
    use App\Models\Teacher;
    $r = $record;
    $titleLabel = Teacher::TITLES[$r->title] ?? null;
    $titleColor = Teacher::TITLE_COLORS[$r->title] ?? 'gray';
@endphp

<div class="sp-thead">
    @if($r->avatar_url)
        <img src="{{ $r->avatar_url }}" alt="" class="sp-thead__avatar">
    @else
        <div class="sp-thead__avatar sp-thead__avatar--initials">{{ strtoupper(mb_substr($r->name, 0, 1)) }}</div>
    @endif

    <div class="sp-thead__body">
        <div class="sp-thead__top">
            <span class="sp-thead__name">{{ $r->name }}</span>
            @if($titleLabel)
                <span class="sp-doc-badge sp-doc-badge--{{ $titleColor }}">{{ $titleLabel }}</span>
            @endif
        </div>
        <div class="sp-thead__meta">
            <span><strong>{{ $r->code }}</strong></span>
            @if($r->subject) <span>· {{ $r->subject }}</span> @endif
            @if($r->dept) <span>· {{ $r->dept }}</span> @endif
            @if($r->campus) <span>· {{ strtoupper($r->campus) }}</span> @endif
        </div>
        @if($tags && $tags->isNotEmpty())
            <div class="sp-thead__tags">
                @foreach($tags as $t)
                    <span class="sp-tag" @if($t->color) style="background:{{ $t->color }};border-color:{{ $t->color }};color:#fff;" @endif>{{ $t->name }}</span>
                @endforeach
            </div>
        @endif
    </div>
</div>
