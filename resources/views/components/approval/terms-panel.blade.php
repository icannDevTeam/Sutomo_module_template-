{{-- Terms submitted in the latest revision, with field-level diff vs the previous one. --}}
@props(['request', 'compare' => true])
@php
    $revs = $request->relationLoaded('revisions') ? $request->revisions : $request->revisions()->get();
    $rev  = $revs->last();
    $prev = $revs->count() > 1 ? $revs->get($revs->count() - 2) : null;
    $tier = fn (?string $t) => match ($t) {
        'pkwt_1' => 'PKWT-I', 'pkwt_2' => 'PKWT-II', 'pkwt_3' => 'PKWT-III', 'guru_sk' => 'Guru SK', null => '—',
        default => strtoupper(str_replace('_', '-', $t)),
    };
    $fmtDate = fn ($d) => $d ? \Illuminate\Support\Carbon::parse($d)->format('d M Y') : '—';
    $fields = [
        ['Recommendation', $rev?->recommendation ? ucfirst(str_replace('_', ' ', $rev->recommendation)) : '—', $prev?->recommendation ? ucfirst(str_replace('_', ' ', $prev->recommendation)) : null],
        ['Current tier',   $tier($rev?->current_type),  $prev ? $tier($prev->current_type) : null],
        ['Proposed tier',  $tier($rev?->proposed_type), $prev ? $tier($prev->proposed_type) : null],
        ['Proposed start', $fmtDate($rev?->proposed_starts_at), $prev ? $fmtDate($prev->proposed_starts_at) : null],
        ['Proposed end',   $fmtDate($rev?->proposed_ends_at),   $prev ? $fmtDate($prev->proposed_ends_at) : null],
        ['Academic year',  $rev?->academic_year ?? '—', $prev?->academic_year],
    ];
@endphp
<div {{ $attributes }}>
    @if($rev)
        <div class="ap-terms">
            @foreach($fields as [$k, $v, $pv])
                @php $changed = $compare && $prev && $pv !== null && $pv !== $v; @endphp
                <div>
                    <div class="ap-term__k">{{ $k }}</div>
                    <div class="ap-term__v {{ $changed ? 'is-changed' : '' }}">{{ $v }}</div>
                    @if($changed)<div class="ap-term__prev">{{ $pv }}</div>@endif
                </div>
            @endforeach
        </div>
        @if($rev->justification)
            <div class="ap-section" style="margin-top:1rem">
                <h4 class="ap-section-h">Justification <span class="ap-muted" style="text-transform:none;letter-spacing:0;font-weight:500">Revision {{ $rev->revision_number }} · {{ $rev->submitted_at?->format('d M Y H:i') }}</span></h4>
                <p class="ap-prose">{{ $rev->justification }}</p>
            </div>
        @endif
        @if($prev && $compare && $prev->justification !== $rev->justification && $prev->justification)
            <details style="margin-top:.75rem">
                <summary class="ap-muted" style="font-size:.78rem;cursor:pointer">Previous justification (revision {{ $prev->revision_number }})</summary>
                <p class="ap-prose ap-muted" style="margin-top:.4rem">{{ $prev->justification }}</p>
            </details>
        @endif
    @else
        <p class="ap-muted" style="font-size:.84rem">No submission details recorded.</p>
    @endif
</div>
