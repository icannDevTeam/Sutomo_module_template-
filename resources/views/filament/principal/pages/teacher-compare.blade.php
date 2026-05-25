<x-filament-panels::page>
    <form wire:submit.prevent>
        {{ $this->form }}
    </form>

    @if($cards->isEmpty())
        <div class="sp-compare-empty">Pick up to 3 teachers above to compare.</div>
    @else
        <div class="sp-compare-grid">
            @foreach($cards as $c)
                @php $t = $c['teacher']; @endphp
                <div class="sp-compare-card">
                    <div class="sp-compare-card__head">
                        @if($t->avatar_url)
                            <img class="sp-compare-card__avatar" src="{{ $t->avatar_url }}" alt="">
                        @else
                            <div class="sp-compare-card__avatar sp-compare-card__avatar--init">{{ strtoupper(substr($t->name, 0, 2)) }}</div>
                        @endif
                        <div>
                            <div class="sp-compare-card__name">{{ $t->name }}</div>
                            <div class="sp-compare-card__meta">{{ $t->title ? (\App\Models\Teacher::TITLES[$t->title] ?? $t->title) : '—' }} · {{ $t->dept ?? '—' }}</div>
                        </div>
                    </div>
                    <dl class="sp-compare-stats">
                        <div><dt>Subject</dt><dd>{{ $t->subject ?? '—' }}</dd></div>
                        <div><dt>Campus</dt><dd>{{ strtoupper($t->campus ?? '—') }}</dd></div>
                        <div><dt>Status</dt><dd>{{ $t->status ?? '—' }}</dd></div>
                        <div><dt>Homeroom</dt><dd>{{ $c['homeroom_count'] }}</dd></div>
                        <div><dt>Attendance (mo)</dt><dd>{{ $c['attendance'] !== null ? $c['attendance'].'%' : '—' }}</dd></div>
                        <div><dt>Certs</dt><dd>{{ $c['cert_count'] }}</dd></div>
                        <div><dt>Observations</dt><dd>{{ $c['obs_count'] }}</dd></div>
                        <div><dt>Mentor</dt><dd>{{ $t->mentor?->name ?? '—' }}</dd></div>
                    </dl>
                </div>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
