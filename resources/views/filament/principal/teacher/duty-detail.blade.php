@php
    use App\Models\DutyAssignment;

    $teacherDuties = DutyAssignment::query()
        ->where('teacher_id', $record->teacher_id)
        ->orderByDesc('starts_at')
        ->get();

    $byYear = $teacherDuties->groupBy(function ($d) {
        return $d->academic_year ?: DutyAssignment::academicYearFor($d->starts_at);
    })->sortKeysDesc();

    $currentAY = DutyAssignment::currentAcademicYear();
    $selectedDays = collect(is_array($record->days_of_week) ? $record->days_of_week : [])
        ->map(fn ($d) => (int) $d)
        ->all();

    $statusColor = DutyAssignment::STATUS_COLORS[$record->status] ?? 'gray';
@endphp

<div class="sp-duty-detail">

    {{-- Header summary --}}
    <div class="sp-duty-detail__head">
        <div>
            <div class="sp-duty-detail__title">{{ $record->title }}</div>
            <div class="sp-duty-detail__sub">
                Assigned to <strong>{{ $record->teacher?->name ?? '—' }}</strong>
                @if($record->location) · {{ $record->location }} @endif
            </div>
        </div>
        <div class="sp-duty-detail__badges">
            <span class="sp-doc-badge sp-doc-badge--{{ $statusColor }}">
                {{ DutyAssignment::STATUSES[$record->status] ?? $record->status }}
            </span>
            <span class="sp-doc-badge sp-doc-badge--{{ $record->recurrence === 'weekly' ? 'info' : 'gray' }}">
                {{ DutyAssignment::RECURRENCES[$record->recurrence] ?? $record->recurrence }}
            </span>
            <span class="sp-doc-badge sp-doc-badge--gray">
                AY {{ $record->academic_year ?: DutyAssignment::academicYearFor($record->starts_at) }}
            </span>
        </div>
    </div>

    {{-- Meta grid --}}
    <div class="sp-duty-detail__meta">
        <div>
            <div class="sp-doc-preview__label">Starts</div>
            <div class="sp-doc-preview__value">{{ optional($record->starts_at)->format('d M Y, H:i') ?: '—' }}</div>
        </div>
        <div>
            <div class="sp-doc-preview__label">Ends</div>
            <div class="sp-doc-preview__value">{{ optional($record->ends_at)->format('d M Y, H:i') ?: '—' }}</div>
        </div>
        <div>
            <div class="sp-doc-preview__label">Assigned By</div>
            <div class="sp-doc-preview__value">{{ $record->assigned_by ?: '—' }}</div>
        </div>
        <div>
            <div class="sp-doc-preview__label">Responded</div>
            <div class="sp-doc-preview__value">{{ optional($record->responded_at)->format('d M Y, H:i') ?: '—' }}</div>
        </div>
    </div>

    @if($record->decline_reason)
        <div class="sp-doc-preview__note"><strong>Note:</strong> {{ $record->decline_reason }}</div>
    @endif

    {{-- Days of week strip --}}
    @if($record->recurrence === 'weekly')
        <div class="sp-duty-week">
            <div class="sp-duty-week__title">Weekly schedule</div>
            <div class="sp-duty-week__grid">
                @foreach(DutyAssignment::DAYS as $n => $abbr)
                    <div class="sp-duty-week__day {{ in_array($n, $selectedDays, true) ? 'is-on' : '' }}">
                        <div class="sp-duty-week__abbr">{{ $abbr }}</div>
                        <div class="sp-duty-week__dot"></div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- History grouped by academic year --}}
    <div class="sp-duty-history">
        <div class="sp-duty-history__title">
            History for {{ $record->teacher?->name ?? 'teacher' }}
            <span class="sp-duty-history__count">{{ $teacherDuties->count() }} total</span>
        </div>

        @forelse($byYear as $year => $duties)
            @php
                $isCurrent = $year === $currentAY;
                $weekly = $duties->where('recurrence', 'weekly');
                $oneOff = $duties->where('recurrence', 'once');

                // Aggregate weekday coverage across this AY for this teacher
                $weekdayCoverage = [];
                foreach ($weekly as $d) {
                    foreach ((array) ($d->days_of_week ?? []) as $dow) {
                        $weekdayCoverage[(int) $dow] = ($weekdayCoverage[(int) $dow] ?? 0) + 1;
                    }
                }
            @endphp

            <div class="sp-duty-year {{ $isCurrent ? 'is-current' : '' }}">
                <div class="sp-duty-year__head">
                    <div class="sp-duty-year__label">
                        AY {{ $year }}
                        @if($isCurrent)<span class="sp-duty-year__pill">Current</span>@endif
                    </div>
                    <div class="sp-duty-year__stats">
                        <span>{{ $duties->count() }} duties</span>
                        <span>{{ $weekly->count() }} weekly</span>
                        <span>{{ $oneOff->count() }} one-off</span>
                    </div>
                </div>

                @if(! empty($weekdayCoverage))
                    <div class="sp-duty-week sp-duty-week--mini">
                        <div class="sp-duty-week__grid">
                            @foreach(DutyAssignment::DAYS as $n => $abbr)
                                @php $count = $weekdayCoverage[$n] ?? 0; @endphp
                                <div class="sp-duty-week__day {{ $count > 0 ? 'is-on' : '' }}" title="{{ $count }} weekly duty(s)">
                                    <div class="sp-duty-week__abbr">{{ $abbr }}</div>
                                    <div class="sp-duty-week__count">{{ $count > 0 ? $count : '—' }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="sp-duty-year__rows">
                    @foreach($duties as $d)
                        @php
                            $rowColor = DutyAssignment::STATUS_COLORS[$d->status] ?? 'gray';
                            $isThis = $d->id === $record->id;
                            $days = collect(is_array($d->days_of_week) ? $d->days_of_week : [])
                                ->map(fn ($x) => DutyAssignment::DAYS[(int) $x] ?? null)
                                ->filter()->join(' · ');
                        @endphp
                        <div class="sp-duty-row sp-duty-row--{{ $rowColor }} {{ $isThis ? 'is-this' : '' }}">
                            <div class="sp-duty-row__main">
                                <div class="sp-duty-row__title">
                                    {{ $d->title }}
                                    @if($isThis)<span class="sp-duty-row__pin">viewing</span>@endif
                                </div>
                                <div class="sp-duty-row__meta">
                                    {{ optional($d->starts_at)->format('d M Y, H:i') }}
                                    @if($d->location) · {{ $d->location }} @endif
                                    @if($d->recurrence === 'weekly' && $days) · <strong>{{ $days }}</strong>@endif
                                </div>
                            </div>
                            <div class="sp-duty-row__status">
                                <span class="sp-doc-badge sp-doc-badge--{{ $rowColor }}">
                                    {{ DutyAssignment::STATUSES[$d->status] ?? $d->status }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="sp-duty-history__empty">No prior duties recorded.</div>
        @endforelse
    </div>
</div>
