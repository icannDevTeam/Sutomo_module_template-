@php $peers = $peers ?? collect(); @endphp
@if($peers->isEmpty())
    <div class="sp-peer-empty">No department peers found.</div>
@else
    <div class="sp-peer-grid">
        @foreach($peers as $p)
            <div class="sp-peer">
                @if($p->avatar_url)
                    <img src="{{ $p->avatar_url }}" alt="" class="sp-peer__avatar">
                @else
                    <div class="sp-peer__avatar sp-peer__avatar--initials">{{ strtoupper(mb_substr($p->name, 0, 1)) }}</div>
                @endif
                <div class="sp-peer__body">
                    <div class="sp-peer__name">{{ $p->name }}</div>
                    <div class="sp-peer__meta">
                        {{ \App\Models\Teacher::TITLES[$p->title] ?? ($p->title ?: '—') }}
                        @if($p->subject) · {{ $p->subject }} @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
