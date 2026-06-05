<x-filament-panels::page>
    {{-- ====================== SCOPED STYLES ====================== --}}
    <style>
        .eo-shell { display: flex; flex-direction: column; gap: 1.25rem; }

        /* Supervisor banner */
        .eo-banner {
            display: flex; align-items: center; gap: .75rem;
            padding: .85rem 1rem;
            background: linear-gradient(180deg, #f5f3ff 0%, #ede9fe 100%);
            border: 1px solid #ddd6fe;
            color: #4c1d95;
            border-radius: .75rem;
            font-size: .875rem;
        }
        .eo-banner svg { flex: 0 0 auto; width: 1.15rem; height: 1.15rem; color: #7c3aed; }
        .eo-banner strong { color: #6d28d9; font-weight: 600; }

        /* Hero card */
        .eo-hero {
            display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem;
            padding: 1.25rem 1.35rem;
            background: #fff;
            border: 1px solid rgb(229 231 235);
            border-radius: .85rem;
            box-shadow: 0 1px 2px rgba(0,0,0,.03);
        }
        .eo-hero h2 { font-size: 1.15rem; font-weight: 700; color: #111827; margin: 0; letter-spacing: -.01em; }
        .eo-hero p  { margin: .25rem 0 0; color: #6b7280; font-size: .875rem; }
        .eo-hero__actions { display: flex; gap: .55rem; align-items: center; }

        .eo-btn {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .45rem .8rem;
            background: #fff;
            color: #374151;
            border: 1px solid #e5e7eb;
            border-radius: .55rem;
            font-size: .8rem; font-weight: 500;
            cursor: pointer;
            transition: all .15s;
        }
        .eo-btn:hover { background: #f9fafb; border-color: #d1d5db; }
        .eo-btn svg { width: .9rem; height: .9rem; }

        .eo-day-menu {
            position: relative;
        }
        .eo-day-menu__btn {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .45rem .8rem;
            background: #fff;
            color: #111827;
            border: 1px solid #e5e7eb;
            border-radius: .55rem;
            font-size: .8rem; font-weight: 600;
            cursor: pointer;
        }
        .eo-day-menu__btn:hover { background: #f9fafb; }
        .eo-day-menu__list {
            position: absolute; top: calc(100% + 4px); right: 0;
            background: #fff; border: 1px solid #e5e7eb;
            border-radius: .55rem;
            box-shadow: 0 10px 25px rgba(0,0,0,.08);
            min-width: 160px;
            padding: .35rem;
            z-index: 30;
        }
        .eo-day-menu__item {
            display: flex; justify-content: space-between; align-items: center; gap: .5rem;
            width: 100%;
            padding: .45rem .65rem;
            background: transparent;
            border: 0;
            text-align: left;
            border-radius: .35rem;
            font-size: .8rem; color: #374151;
            cursor: pointer;
        }
        .eo-day-menu__item:hover { background: #f3f4f6; }
        .eo-day-menu__item.is-active { background: #ede9fe; color: #6d28d9; font-weight: 600; }
        .eo-day-menu__item small { color: #9ca3af; font-weight: 400; }

        /* Stat tiles */
        .eo-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }
        @media (max-width: 1100px) { .eo-stats { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px)  { .eo-stats { grid-template-columns: 1fr; } }

        .eo-tile {
            background: #fff;
            border: 1px solid rgb(229 231 235);
            border-radius: .85rem;
            padding: 1.05rem 1.15rem;
            box-shadow: 0 1px 2px rgba(0,0,0,.03);
        }
        .eo-tile__label { font-size: .8rem; color: #6b7280; font-weight: 500; }
        .eo-tile__value { font-size: 2rem; font-weight: 700; color: #111827; line-height: 1.1; margin-top: .35rem; letter-spacing: -.02em; }
        .eo-tile__value--green { color: #16a34a; }
        .eo-tile__sub { margin-top: .55rem; font-size: .8rem; color: #6b7280; }

        .eo-pill {
            display: inline-flex; align-items: center; gap: .25rem;
            padding: .15rem .55rem;
            font-size: .72rem; font-weight: 600;
            border-radius: 999px;
        }
        .eo-pill--amber   { background: #fef3c7; color: #92400e; }
        .eo-pill--green   { background: #dcfce7; color: #15803d; }
        .eo-pill--red     { background: #fee2e2; color: #b91c1c; }
        .eo-pill--blue    { background: #dbeafe; color: #1d4ed8; }
        .eo-pill--gray    { background: #f3f4f6; color: #4b5563; }

        /* Two-panel grid */
        .eo-panels {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.15rem;
        }
        @media (max-width: 1100px) { .eo-panels { grid-template-columns: 1fr; } }

        .eo-panel {
            background: #fff;
            border: 1px solid rgb(229 231 235);
            border-radius: .85rem;
            box-shadow: 0 1px 2px rgba(0,0,0,.03);
            overflow: hidden;
            display: flex; flex-direction: column;
        }
        .eo-panel__head {
            padding: 1rem 1.15rem .85rem;
            border-bottom: 1px solid #f3f4f6;
        }
        .eo-panel__title-row {
            display: flex; align-items: flex-start; justify-content: space-between; gap: .75rem;
        }
        .eo-panel__title { font-size: .95rem; font-weight: 700; color: #111827; margin: 0; }
        .eo-panel__sub   { margin: .2rem 0 0; font-size: .78rem; color: #6b7280; }
        .eo-panel__badges { display: flex; gap: .35rem; flex-wrap: wrap; flex-shrink: 0; }

        .eo-panel__filters {
            display: flex; gap: .5rem; margin-top: .85rem;
        }
        .eo-search {
            flex: 1;
            position: relative;
        }
        .eo-search svg {
            position: absolute; left: .65rem; top: 50%; transform: translateY(-50%);
            width: .95rem; height: .95rem; color: #9ca3af;
        }
        .eo-search input {
            width: 100%;
            padding: .5rem .75rem .5rem 2.1rem;
            border: 1px solid #e5e7eb;
            border-radius: .55rem;
            font-size: .82rem;
            color: #111827;
            background: #fff;
        }
        .eo-search input:focus { outline: none; border-color: #c4b5fd; box-shadow: 0 0 0 3px rgba(167,139,250,.15); }

        .eo-select {
            padding: .5rem 2rem .5rem .75rem;
            border: 1px solid #e5e7eb;
            border-radius: .55rem;
            font-size: .82rem;
            color: #111827;
            background: #fff;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%236b7280' stroke-width='2' viewBox='0 0 24 24'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right .65rem center;
            appearance: none;
            min-width: 7rem;
        }

        /* Table */
        .eo-table { width: 100%; border-collapse: collapse; }
        .eo-table thead th {
            text-align: left;
            font-size: .72rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .04em;
            padding: .65rem 1.15rem;
            background: #f9fafb;
            border-top: 1px solid #f3f4f6;
            border-bottom: 1px solid #f3f4f6;
        }
        .eo-table tbody td {
            padding: .85rem 1.15rem;
            border-bottom: 1px solid #f3f4f6;
            font-size: .85rem;
            color: #111827;
            vertical-align: middle;
        }
        .eo-table tbody tr:last-child td { border-bottom: 0; }
        .eo-table tbody tr:hover { background: #fafafa; }
        .eo-table td.num, .eo-table th.num { color: #9ca3af; width: 2.5rem; }

        .eo-applicant { display: flex; align-items: center; gap: .65rem; }
        .eo-avatar {
            width: 2rem; height: 2rem;
            border-radius: 999px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: .72rem; font-weight: 600;
            color: #4338ca;
            background: #eef2ff;
            border: 1px solid #e0e7ff;
            flex-shrink: 0;
        }
        .eo-applicant__name { font-weight: 500; color: #111827; }

        /* Present/Absent dual buttons */
        .eo-att { display: inline-flex; gap: .35rem; }
        .eo-att__btn {
            display: inline-flex; align-items: center; gap: .3rem;
            padding: .35rem .7rem;
            font-size: .75rem; font-weight: 600;
            border-radius: .45rem;
            border: 1px solid transparent;
            cursor: pointer;
        }
        .eo-att__present--on  { background: #16a34a; color: #fff; }
        .eo-att__present--off { background: #fff;     color: #6b7280; border-color: #e5e7eb; }
        .eo-att__absent--on   { background: #dc2626; color: #fff; }
        .eo-att__absent--off  { background: #fff;     color: #6b7280; border-color: #e5e7eb; }
        .eo-att__btn svg { width: .85rem; height: .85rem; }

        .eo-view-btn {
            display: inline-flex; align-items: center; gap: .35rem;
            padding: .35rem .7rem;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: .45rem;
            color: #374151;
            font-size: .75rem; font-weight: 500;
            cursor: pointer;
        }
        .eo-view-btn:hover { background: #f9fafb; }

        /* Footer / pagination */
        .eo-panel__foot {
            display: flex; justify-content: space-between; align-items: center;
            padding: .85rem 1.15rem;
            font-size: .78rem; color: #6b7280;
            border-top: 1px solid #f3f4f6;
            background: #fff;
        }
        .eo-pager { display: inline-flex; gap: .25rem; }
        .eo-pager button, .eo-pager span {
            min-width: 2rem;
            padding: .35rem .6rem;
            font-size: .78rem;
            border-radius: .4rem;
            border: 1px solid #e5e7eb;
            background: #fff;
            color: #374151;
            cursor: pointer;
        }
        .eo-pager button:hover { background: #f9fafb; }
        .eo-pager .is-current { background: #6d28d9; color: #fff; border-color: #6d28d9; }
        .eo-pager .ellipsis { border-color: transparent; cursor: default; padding: .35rem .25rem; }
        .eo-pager button[disabled] { opacity: .45; cursor: not-allowed; }

        .eo-empty {
            padding: 2.25rem 1rem;
            text-align: center;
            font-size: .85rem;
            color: #9ca3af;
        }
    </style>

    <div class="eo-shell">
        {{-- ====================== SUPERVISOR BANNER ====================== --}}
        <div class="eo-banner">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            <span>You are assigned as <strong>Enrollment Supervisor</strong> for the {{ $academicYear }} intake. This page is only visible to assigned supervisors.</span>
        </div>

        {{-- ====================== HERO HEADER ====================== --}}
        <x-principal.module-hero
            :title="'Enrollment - Academic Year '.$academicYear"
            :description="'Student Applicant Onboarding'.($periodWindow ? ' · '.$periodWindow : '').' · '.$room"
            icon="heroicon-o-rocket-launch"
            tone="indigo"
        >
            <x-slot:actions>
                <button type="button" class="eo-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Export
                </button>
                <div class="eo-day-menu" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" class="eo-day-menu__btn" @click="open = !open">
                        {{ $dayLabel }}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:.85rem;height:.85rem;">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                    <div class="eo-day-menu__list" x-show="open" x-cloak x-transition.opacity>
                        @foreach($dates as $i => $d)
                            <button type="button"
                                    class="eo-day-menu__item {{ $i === $dayIndex ? 'is-active' : '' }}"
                                    wire:click="setDay({{ $i }})"
                                    @click="open = false">
                                <span>Day {{ $i + 1 }}</span>
                                <small>{{ \Illuminate\Support\Carbon::parse($d)->format('M j') }}</small>
                            </button>
                        @endforeach
                        @if(empty($dates))
                            <div class="eo-day-menu__item">No observation window configured</div>
                        @endif
                    </div>
                </div>
            </x-slot:actions>
        </x-principal.module-hero>

        {{-- ====================== STAT TILES ====================== --}}
        <div class="eo-stats">
            <div class="eo-tile">
                <div class="eo-tile__label">Total Applicants</div>
                <div class="eo-tile__value">{{ $totalApplicants }}</div>
                <div class="eo-tile__sub">Registered for onboarding</div>
            </div>
            <div class="eo-tile">
                <div class="eo-tile__label">Present Today</div>
                <div class="eo-tile__value eo-tile__value--green">{{ $presentToday }}</div>
                <div class="eo-tile__sub">{{ $dayLabel }} of {{ count($dates) ?: 5 }}</div>
            </div>
            <div class="eo-tile">
                <div class="eo-tile__label">Scores Submitted</div>
                <div class="eo-tile__value">{{ $scoresSubmitted }}</div>
                <div class="eo-tile__sub">
                    @if($scoresPending > 0)
                        <span class="eo-pill eo-pill--amber">{{ $scoresPending }} pending</span>
                    @else
                        <span class="eo-pill eo-pill--green">All in</span>
                    @endif
                </div>
            </div>
            <div class="eo-tile">
                <div class="eo-tile__label">Sent to Principal</div>
                <div class="eo-tile__value">{{ $sentToPrincipal }}</div>
                <div class="eo-tile__sub">
                    <span class="eo-pill eo-pill--green">Synced</span>
                </div>
            </div>
        </div>

        {{-- ====================== TWO PANELS ====================== --}}
        <div class="eo-panels">
            {{-- ----- Attendance ----- --}}
            <div class="eo-panel">
                <div class="eo-panel__head">
                    <div class="eo-panel__title-row">
                        <div>
                            <h3 class="eo-panel__title">Attendance — {{ $dayLabel }}</h3>
                            <p class="eo-panel__sub">
                                {{ $dayHuman ?? '—' }} · {{ $dayWindow }}
                            </p>
                        </div>
                        <div class="eo-panel__badges">
                            <span class="eo-pill eo-pill--green">{{ $presentToday }} Present</span>
                            <span class="eo-pill eo-pill--red">{{ $absentToday }} Absent</span>
                        </div>
                    </div>
                    <div class="eo-panel__filters">
                        <div class="eo-search">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                            <input type="text" placeholder="Search" wire:model.live.debounce.300ms="attendanceSearch">
                        </div>
                        <select class="eo-select" wire:model.live="attendanceFilter">
                            <option value="all">All</option>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>

                <table class="eo-table">
                    <thead>
                        <tr>
                            <th class="num">#</th>
                            <th>Applicant</th>
                            <th style="text-align:right;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attRows as $i => $r)
                            <tr>
                                <td class="num">{{ $attFrom + $i }}</td>
                                <td>
                                    <div class="eo-applicant">
                                        <span class="eo-avatar">{{ $r->initials }}</span>
                                        <span class="eo-applicant__name">{{ $r->name }}</span>
                                    </div>
                                </td>
                                <td style="text-align:right;">
                                    <div class="eo-att">
                                        <span class="eo-att__btn {{ $r->status === 'present' ? 'eo-att__present--on' : 'eo-att__present--off' }}">
                                            @if($r->status === 'present')
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="20 6 9 17 4 12"/>
                                                </svg>
                                            @endif
                                            Present
                                        </span>
                                        <span class="eo-att__btn {{ $r->status === 'absent' ? 'eo-att__absent--on' : 'eo-att__absent--off' }}">
                                            Absent
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="eo-empty">No applicants match your filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="eo-panel__foot">
                    <div>
                        @if($attTotal > 0)
                            Showing <strong>{{ $attFrom }}-{{ $attTo }}</strong> of {{ $attTotal }} students
                        @else
                            No results
                        @endif
                    </div>
                    <div class="eo-pager">
                        <button type="button" wire:click="setAttendancePage({{ max(1, $attPage - 1) }})" @disabled($attPage <= 1)>
                            ‹ Previous
                        </button>
                        @foreach($this->pageWindow($attPage, $attLastPage) as $p)
                            @if($p === '…')
                                <span class="ellipsis">…</span>
                            @else
                                <button type="button"
                                        class="{{ $p == $attPage ? 'is-current' : '' }}"
                                        wire:click="setAttendancePage({{ $p }})">{{ $p }}</button>
                            @endif
                        @endforeach
                        <button type="button" wire:click="setAttendancePage({{ min($attLastPage, $attPage + 1) }})" @disabled($attPage >= $attLastPage)>
                            Next ›
                        </button>
                    </div>
                </div>
            </div>

            {{-- ----- Placement Exam ----- --}}
            <div class="eo-panel">
                <div class="eo-panel__head">
                    <div class="eo-panel__title-row">
                        <div>
                            <h3 class="eo-panel__title">Placement Exam Scores — {{ $dayLabel }}</h3>
                            <p class="eo-panel__sub">Submitted scores are permanently locked · Only Principal can override</p>
                        </div>
                        <div class="eo-panel__badges">
                            <span class="eo-pill eo-pill--blue">{{ $scoresSubmitted }} Submitted</span>
                        </div>
                    </div>
                    <div class="eo-panel__filters">
                        <div class="eo-search">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                            <input type="text" placeholder="Search" wire:model.live.debounce.300ms="scoreSearch">
                        </div>
                        <select class="eo-select" wire:model.live="scoreFilter">
                            <option value="all">All</option>
                            <option value="submitted">Submitted</option>
                            <option value="pending">Pending</option>
                            <option value="absent">Absent</option>
                        </select>
                    </div>
                </div>

                <table class="eo-table">
                    <thead>
                        <tr>
                            <th class="num">#</th>
                            <th>Applicant</th>
                            <th>Avg</th>
                            <th>Status</th>
                            <th style="text-align:right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($scoreRows as $i => $r)
                            <tr>
                                <td class="num">{{ $scoreFrom + $i }}</td>
                                <td>
                                    <div class="eo-applicant">
                                        <span class="eo-avatar">{{ $r->initials }}</span>
                                        <span class="eo-applicant__name">{{ \Illuminate\Support\Str::limit($r->name, 22) }}</span>
                                    </div>
                                </td>
                                <td>{{ $r->score ?? '—' }}</td>
                                <td>
                                    @if($r->status === 'submitted')
                                        <span class="eo-pill eo-pill--blue">Submitted</span>
                                    @elseif($r->status === 'pending')
                                        <span class="eo-pill eo-pill--amber">Pending</span>
                                    @else
                                        <span class="eo-pill eo-pill--gray">Absent</span>
                                    @endif
                                </td>
                                <td style="text-align:right;">
                                    <a href="{{ \App\Filament\Principal\Resources\ApplicationResource::getUrl('view', ['record' => $r->id]) }}"
                                       class="eo-view-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="eo-empty">No applicants match your filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="eo-panel__foot">
                    <div>
                        @if($scoreTotal > 0)
                            Showing <strong>{{ $scoreFrom }}-{{ $scoreTo }}</strong> of {{ $scoreTotal }} students
                        @else
                            No results
                        @endif
                    </div>
                    <div class="eo-pager">
                        <button type="button" wire:click="setScoresPage({{ max(1, $scorePage - 1) }})" @disabled($scorePage <= 1)>
                            ‹ Previous
                        </button>
                        @foreach($this->pageWindow($scorePage, $scoreLastPage) as $p)
                            @if($p === '…')
                                <span class="ellipsis">…</span>
                            @else
                                <button type="button"
                                        class="{{ $p == $scorePage ? 'is-current' : '' }}"
                                        wire:click="setScoresPage({{ $p }})">{{ $p }}</button>
                            @endif
                        @endforeach
                        <button type="button" wire:click="setScoresPage({{ min($scoreLastPage, $scorePage + 1) }})" @disabled($scorePage >= $scoreLastPage)>
                            Next ›
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-filament-panels::page>
