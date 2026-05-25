@php
    /** @var \App\Models\Teacher $teacher */
    /** @var int|null $quota */
    /** @var \Illuminate\Support\Collection $children */
    $used = $children->count();
    $max  = $quota ?? max($used, 1);
    $pct  = $max > 0 ? min(100, round(($used / $max) * 100)) : 0;
@endphp
<div class="sp-quota">
    <div class="sp-quota__top">
        <div>
            <span class="sp-quota__num">{{ $used }}</span>
            <span class="sp-quota__lbl">enrolled @if ($quota) of <strong>{{ $quota }}</strong> allowed @endif</span>
        </div>
        @if ($quota && $used >= $quota)
            <span class="sp-quota__badge sp-quota__badge--full">Quota Full</span>
        @elseif ($quota)
            <span class="sp-quota__badge sp-quota__badge--ok">{{ $quota - $used }} slot{{ ($quota - $used) === 1 ? '' : 's' }} left</span>
        @endif
    </div>
    @if ($quota)
        <div class="sp-quota__bar"><div class="sp-quota__bar-fill" style="width: {{ $pct }}%"></div></div>
    @endif

    @if ($children->isEmpty())
        <div class="sp-teacher-empty">No children currently linked.</div>
    @else
        <div class="sp-quota__list">
            @foreach ($children as $c)
                <div class="sp-quota__chip">
                    <span class="sp-quota__name">{{ $c->name ?? $c->full_name ?? ('Student #' . $c->id) }}</span>
                    @if ($c->grade ?? null)
                        <span class="sp-quota__grade">{{ $c->grade }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
