@if(empty($warnings))
    <div class="sp-warn-empty">✓ No active warnings for this teacher.</div>
@else
    <div class="sp-warn-list">
        @foreach($warnings as $w)
            <div class="sp-warn sp-warn--{{ $w['level'] }}">
                <span class="sp-warn__icon">{{ $w['icon'] }}</span>
                <span class="sp-warn__text">{{ $w['text'] }}</span>
            </div>
        @endforeach
    </div>
@endif
