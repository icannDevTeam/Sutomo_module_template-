<x-filament-panels::page>
    @php
        $states = \App\Support\SubstitutionTimetable::STATES;
        $views  = ['day' => 'Day', 'week' => 'Week', 'month' => 'Month'];
        $fmt    = fn ($d, $f = 'd M') => \Carbon\Carbon::parse($d)->format($f);
        $s      = $summary;
        $active = $s['confirmed'] + $s['awaiting'];
        $pct    = $s['lessons_total'] > 0 ? (int) round($active / $s['lessons_total'] * 100) : null;
        $attnTone = [
            'uncovered'  => 'red',
            'declined'   => 'red',
            'no_handoff' => 'amber',
            'pending'    => 'slate',
        ];
        $hasFilters = $campus || $unit || $statusFilter || filled($search);
    @endphp

    <div class="sp-st">
        {{-- ───────────── Toolbar ───────────── --}}
        <div class="sp-st-bar">
            <div class="sp-st-bar__row">
                <div class="sp-st-seg" role="tablist" aria-label="Window">
                    @foreach ($views as $key => $label)
                        <button type="button" role="tab" wire:click="setView('{{ $key }}')"
                                aria-selected="{{ $view === $key ? 'true' : 'false' }}"
                                @class(['sp-st-seg__btn', 'is-active' => $view === $key])>{{ $label }}</button>
                    @endforeach
                </div>

                <div class="sp-st-nav">
                    <button type="button" wire:click="prevWindow" class="sp-st-icon-btn" aria-label="Previous">
                        <x-heroicon-m-chevron-left />
                    </button>
                    <div class="sp-st-nav__label">{{ $windowLabel }}</div>
                    <button type="button" wire:click="nextWindow" class="sp-st-icon-btn" aria-label="Next">
                        <x-heroicon-m-chevron-right />
                    </button>
                </div>

                <div class="sp-st-bar__quick">
                    <button type="button" wire:click="goToday"
                            @class(['sp-st-chipbtn', 'is-active' => $view === 'day' && $focusDate === $today])>Today</button>
                    <button type="button" wire:click="goTomorrow" class="sp-st-chipbtn">Tomorrow</button>
                    <label class="sp-st-field sp-st-field--date">
                        <span class="sr-only">Jump to date</span>
                        <input type="date" wire:model.live="focusDate" />
                    </label>
                </div>
            </div>

            <div class="sp-st-bar__row sp-st-bar__row--filters">
                <label class="sp-st-field sp-st-field--search">
                    <x-heroicon-m-magnifying-glass class="sp-st-field__icon" />
                    <span class="sr-only">Search teacher</span>
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search teacher…" autocomplete="off" />
                </label>

                <label class="sp-st-field">
                    <span class="sp-st-field__label">Campus</span>
                    <select wire:model.live="campus">
                        <option value="">All</option>
                        @foreach ($campuses as $c)
                            <option value="{{ $c }}">{{ $c }}</option>
                        @endforeach
                    </select>
                    <x-heroicon-m-chevron-down class="sp-st-field__caret" />
                </label>

                <label class="sp-st-field">
                    <span class="sp-st-field__label">Unit</span>
                    <select wire:model.live="unit">
                        <option value="">All</option>
                        @foreach ($units as $code => $label)
                            <option value="{{ $code }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-heroicon-m-chevron-down class="sp-st-field__caret" />
                </label>

                <label class="sp-st-field">
                    <span class="sp-st-field__label">Show</span>
                    <select wire:model.live="statusFilter">
                        <option value="">Everything</option>
                        @foreach ($filters as $k => $label)
                            <option value="{{ $k }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-heroicon-m-chevron-down class="sp-st-field__caret" />
                </label>

                @if ($hasFilters)
                    <button type="button" wire:click="resetFilters" class="sp-st-link">Clear filters</button>
                @endif

                @if ($view !== 'month')
                    <div class="sp-st-seg sp-st-seg--sm sp-st-bar__end" aria-label="Layout">
                        <button type="button" wire:click="setLayout('roster')" @class(['sp-st-seg__btn', 'is-active' => $layout === 'roster'])>
                            <x-heroicon-m-users /> By teacher
                        </button>
                        <button type="button" wire:click="setLayout('grid')" @class(['sp-st-seg__btn', 'is-active' => $layout === 'grid'])>
                            <x-heroicon-m-table-cells /> By period
                        </button>
                    </div>
                @endif

                <div wire:loading.delay class="sp-st-loading">Updating…</div>
            </div>
        </div>

        {{-- ───────────── KPIs ───────────── --}}
        <div class="sp-st-kpis">
            <div class="sp-st-kpi">
                <div class="sp-st-kpi__label">Teachers on leave</div>
                <div class="sp-st-kpi__value">{{ $s['teachers_on_leave'] }}</div>
                <div class="sp-st-kpi__hint">{{ $s['lessons_total'] }} lessons to cover</div>
            </div>
            <div class="sp-st-kpi sp-st-kpi--green">
                <div class="sp-st-kpi__label">Covered</div>
                <div class="sp-st-kpi__value">{{ $active }}@if ($pct !== null)<small>{{ $pct }}%</small>@endif</div>
                <div class="sp-st-meter" aria-hidden="true">
                    @if ($s['lessons_total'] > 0)
                        <i class="is-confirmed" style="width: {{ $s['confirmed'] / $s['lessons_total'] * 100 }}%"></i>
                        <i class="is-awaiting" style="width: {{ $s['awaiting'] / $s['lessons_total'] * 100 }}%"></i>
                        <i class="is-gap" style="width: {{ $s['gaps_count'] / $s['lessons_total'] * 100 }}%"></i>
                    @endif
                </div>
                <div class="sp-st-kpi__hint">{{ $s['confirmed'] }} confirmed · {{ $s['awaiting'] }} awaiting reply</div>
            </div>
            <div @class(['sp-st-kpi', 'sp-st-kpi--red' => $s['gaps_count'] > 0])>
                <div class="sp-st-kpi__label">Needs action</div>
                <div class="sp-st-kpi__value">{{ $s['gaps_count'] }}</div>
                <div class="sp-st-kpi__hint">{{ $s['uncovered'] }} uncovered · {{ $s['no_handoff'] }} without hand-off</div>
            </div>
            <div class="sp-st-kpi">
                <div class="sp-st-kpi__label">Standalone duties</div>
                <div class="sp-st-kpi__value">{{ $s['duties_count'] }}</div>
                <div class="sp-st-kpi__hint">{{ $s['pending_leaves'] }} leave{{ $s['pending_leaves'] === 1 ? '' : 's' }} pending approval</div>
            </div>
        </div>

        {{-- ───────────── Attention list ───────────── --}}
        @if (count($attention))
            <div class="sp-st-card sp-st-attn">
                <div class="sp-st-card__head">
                    <div>
                        <div class="sp-st-card__title">Needs attention</div>
                        <div class="sp-st-card__sub">Leaves in this window without confirmed cover, worst first.</div>
                    </div>
                    <a href="{{ $leavesUrl }}" class="sp-st-link">Open Teacher Leaves →</a>
                </div>
                <ul class="sp-st-attn__list">
                    @foreach ($attention as $a)
                        <li class="sp-st-attn__row">
                            <span class="sp-st-dot sp-st-dot--{{ $attnTone[$a['state']] ?? 'slate' }}"></span>
                            <div class="sp-st-attn__main">
                                <div class="sp-st-attn__name">{{ $a['teacher'] }}
                                    <span class="sp-st-attn__type">{{ $a['type_label'] }}</span>
                                </div>
                                <div class="sp-st-attn__meta">
                                    {{ $fmt($a['starts_at']) }}@if ($a['starts_at'] !== $a['ends_at']) – {{ $fmt($a['ends_at']) }}@endif
                                    · {{ $a['lessons'] }} lesson{{ $a['lessons'] === 1 ? '' : 's' }} in window
                                    @if ($a['substitute']) · sub: {{ $a['substitute'] }} @endif
                                </div>
                            </div>
                            <span class="sp-pill sp-pill-{{ $attnTone[$a['state']] ?? 'slate' }}">{{ $a['label'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ───────────── Main view ───────────── --}}
        @if ($view === 'month')
            <div class="sp-st-card sp-st-card--flush">
                <div class="sp-st-cal">
                    <div class="sp-st-cal__head">
                        @foreach (['Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
                            <div>{{ $d }}</div>
                        @endforeach
                    </div>
                    @foreach ($calendar as $week)
                        <div class="sp-st-cal__week">
                            @foreach ($week as $day)
                                <button type="button" wire:click="openDay('{{ $day['date'] }}')"
                                        title="Open {{ $fmt($day['date'], 'l d M') }}"
                                        @class([
                                            'sp-st-cal__day',
                                            'is-out'   => ! $day['in_month'],
                                            'is-today' => $day['is_today'],
                                            'has-gap'  => $day['gaps'] > 0,
                                            'is-empty' => ! $day['lessons'] && ! $day['duties'],
                                        ])>
                                    <span class="sp-st-cal__num">{{ $day['num'] }}</span>
                                    @if ($day['lessons'] || $day['duties'])
                                        <span class="sp-st-cal__stats">
                                            @if ($day['lessons'])
                                                <span class="sp-st-tag sp-st-tag--blue">{{ $day['lessons'] }} lesson{{ $day['lessons'] === 1 ? '' : 's' }}</span>
                                            @endif
                                            @if ($day['gaps'])
                                                <span class="sp-st-tag sp-st-tag--red">{{ $day['gaps'] }} gap{{ $day['gaps'] === 1 ? '' : 's' }}</span>
                                            @endif
                                            @if ($day['duties'])
                                                <span class="sp-st-tag sp-st-tag--green">{{ $day['duties'] }} dut{{ $day['duties'] === 1 ? 'y' : 'ies' }}</span>
                                            @endif
                                        </span>
                                        @if (count($day['absent']))
                                            <span class="sp-st-cal__who">Away: {{ implode(', ', array_slice($day['absent'], 0, 2)) }}@if (count($day['absent']) > 2) +{{ count($day['absent']) - 2 }}@endif</span>
                                        @endif
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif ($isSunday)
            <div class="sp-st-card sp-st-empty">
                <x-heroicon-o-moon class="sp-st-empty__icon" />
                <div class="sp-st-empty__title">Sunday: no lessons scheduled</div>
                <div class="sp-st-empty__sub">Use the arrows to move to a school day.</div>
            </div>
        @elseif ($layout === 'grid')
            {{-- Period × day grid --}}
            <div class="sp-st-card sp-st-card--flush">
                <div class="sp-st-scroll">
                    <table class="sp-st-table sp-st-table--grid">
                        <thead>
                            <tr>
                                <th class="sp-st-sticky sp-st-col-period">Period</th>
                                @foreach ($rosterDays as $day)
                                    <th @class(['is-today' => $day['date'] === $today])>
                                        <button type="button" wire:click="openDay('{{ $day['date'] }}')" class="sp-st-dayhead">
                                            <span>{{ $day['label'] }}</span><b>{{ $fmt($day['date']) }}</b>
                                        </button>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($allDay))
                                <tr class="sp-st-row-allday">
                                    <th class="sp-st-sticky sp-st-col-period">Duties</th>
                                    @foreach ($rosterDays as $day)
                                        <td>
                                            @foreach ($allDay[$day['date']] ?? [] as $cell)
                                                @include('filament.principal.pages.partials.substitution-chip', ['cell' => $cell, 'perspective' => 'grid'])
                                            @endforeach
                                        </td>
                                    @endforeach
                                </tr>
                            @endif
                            @foreach ($periods as $p)
                                <tr>
                                    <th class="sp-st-sticky sp-st-col-period"><span class="sp-st-period">{{ $p }}</span></th>
                                    @foreach ($rosterDays as $day)
                                        <td @class(['is-today' => $day['date'] === $today])>
                                            @foreach ($grid[$day['date']][$p] ?? [] as $cell)
                                                @include('filament.principal.pages.partials.substitution-chip', ['cell' => $cell, 'perspective' => 'grid'])
                                            @endforeach
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            {{-- Teacher × day roster --}}
            <div class="sp-st-card sp-st-card--flush">
                @if (count($rosterRows))
                    <div class="sp-st-scroll">
                        <table class="sp-st-table">
                            <thead>
                                <tr>
                                    <th class="sp-st-sticky sp-st-col-teacher">Covering teacher</th>
                                    @foreach ($rosterDays as $day)
                                        <th @class(['is-today' => $day['date'] === $today])>
                                            @if ($view === 'day')
                                                <span class="sp-st-dayhead"><span>{{ $day['label'] }}</span><b>{{ $fmt($day['date']) }}</b></span>
                                            @else
                                                <button type="button" wire:click="openDay('{{ $day['date'] }}')" class="sp-st-dayhead">
                                                    <span>{{ $day['label'] }}</span><b>{{ $fmt($day['date']) }}</b>
                                                </button>
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rosterRows as $row)
                                    <tr @class(['is-unassigned' => $row['is_unassigned']])>
                                        <th class="sp-st-sticky sp-st-col-teacher">
                                            <div class="sp-st-who">
                                                <span @class(['sp-st-avatar', 'is-warn' => $row['is_unassigned']])>
                                                    @if ($row['is_unassigned'])
                                                        <x-heroicon-m-exclamation-triangle />
                                                    @else
                                                        {{ \Illuminate\Support\Str::of($row['teacher_name'])->substr(0, 1)->upper() }}
                                                    @endif
                                                </span>
                                                <span class="sp-st-who__text">
                                                    <span class="sp-st-who__name">{{ $row['is_unassigned'] ? 'No substitute' : $row['teacher_name'] }}</span>
                                                    <span class="sp-st-who__meta">
                                                        @if ($row['is_unassigned'])
                                                            Lessons with nobody assigned
                                                        @else
                                                            {{ collect([$row['teacher_subject'], $row['teacher_campus'] ? strtoupper($row['teacher_campus']) : null])->filter()->implode(' · ') }}
                                                        @endif
                                                    </span>
                                                    <span class="sp-st-who__tags">
                                                        @if ($row['lesson_count'])<span class="sp-st-tag sp-st-tag--blue">{{ $row['lesson_count'] }} lesson{{ $row['lesson_count'] === 1 ? '' : 's' }}</span>@endif
                                                        @if ($row['duty_count'])<span class="sp-st-tag sp-st-tag--green">{{ $row['duty_count'] }} dut{{ $row['duty_count'] === 1 ? 'y' : 'ies' }}</span>@endif
                                                        @if ($row['gap_count'] && ! $row['is_unassigned'])<span class="sp-st-tag sp-st-tag--red">{{ $row['gap_count'] }} need action</span>@endif
                                                    </span>
                                                </span>
                                            </div>
                                        </th>
                                        @foreach ($rosterDays as $day)
                                            <td @class(['is-today' => $day['date'] === $today])>
                                                @foreach ($row['days'][$day['date']] ?? [] as $cell)
                                                    @include('filament.principal.pages.partials.substitution-chip', ['cell' => $cell, 'perspective' => 'roster'])
                                                @endforeach
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="sp-st-empty">
                        <x-heroicon-o-calendar class="sp-st-empty__icon" />
                        <div class="sp-st-empty__title">No cover or duties {{ $hasFilters ? 'match these filters' : 'in this window' }}</div>
                        <div class="sp-st-empty__sub">
                            @if ($hasFilters)
                                <button type="button" wire:click="resetFilters" class="sp-st-link">Clear filters</button>
                            @else
                                Approved leaves with timetabled lessons and active duty assignments appear here.
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <div class="sp-st-legend">
            @foreach (['confirmed', 'awaiting', 'no_handoff', 'declined', 'uncovered', 'duty'] as $k)
                <span class="sp-st-legend__item"><i class="sp-st-swatch sp-st-swatch--{{ $k }}"></i>{{ $states[$k] }}</span>
            @endforeach
        </div>

    </div>
</x-filament-panels::page>
