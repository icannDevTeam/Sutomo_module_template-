{{--
    One coverage entry on the substitution timetable.
    $cell        — row from App\Support\SubstitutionTimetable::buildWindow()
    $perspective — 'roster' (row is the covering teacher) | 'grid' (row is the period)
--}}
@php
    $isDuty  = $cell['source'] === 'duty';
    $stateLb = \App\Support\SubstitutionTimetable::STATES[$cell['state']] ?? ucfirst($cell['state']);

    $title = $isDuty
        ? $cell['subject']
        : trim(($cell['class_code'] ?? '') . ' · ' . ($cell['subject'] ?? ''), ' ·');

    $meta = match (true) {
        $isDuty => collect([$cell['time'] ?? null, $cell['room'], $perspective === 'grid' ? $cell['substitute'] : null])->filter()->implode(' · '),
        $perspective === 'grid' => ($cell['substitute'] ? $cell['substitute'] : 'No substitute') . ' ← ' . $cell['original'],
        default => 'for ' . $cell['original'],
    };

    $tooltip = $isDuty
        ? "{$cell['subject']} · {$cell['substitute']}" . ($cell['room'] ? " · {$cell['room']}" : '') . " · {$stateLb} ({$cell['status']})"
        : "Period {$cell['period']} · {$title}" . ($cell['room'] ? " · room {$cell['room']}" : '')
            . "\nAbsent: {$cell['original']}\nCovering: " . ($cell['substitute'] ?? 'nobody')
            . "\nStatus: {$stateLb}";
@endphp

@if ($isDuty)
    <div class="sp-st-chip sp-st-chip--duty" title="{{ $tooltip }}">
        <div class="sp-st-chip__top">
            <span class="sp-st-chip__period"><x-heroicon-m-clipboard-document-list /></span>
            <span class="sp-st-chip__title">{{ $title }}</span>
        </div>
        @if ($meta)<div class="sp-st-chip__meta">{{ $meta }}</div>@endif
        @if ($cell['status'] === 'pending')<div class="sp-st-chip__state">Not yet accepted</div>@endif
    </div>
@else
    <div class="sp-st-chip sp-st-chip--{{ $cell['state'] }}" title="{{ $tooltip }}">
        <span class="sp-st-chip__top">
            @if ($perspective === 'roster')<span class="sp-st-chip__period">{{ $cell['period'] }}</span>@endif
            <span class="sp-st-chip__title">{{ $title }}</span>
        </span>
        <span class="sp-st-chip__meta">{{ $meta }}</span>
        @if ($cell['state'] !== 'confirmed')
            <span class="sp-st-chip__state">{{ $stateLb }}</span>
        @endif
    </div>
@endif
