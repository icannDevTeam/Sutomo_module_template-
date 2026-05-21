<x-filament-panels::page>
@include('filament.principal.partials.planning-module-tabs', ['module' => 'timetable'])

<div class="sp-tt2" wire:key="tt-{{ $viewMode }}-{{ $current['name'] }}-{{ $selectedClassCode }}-{{ $selectedRoom }}">

    {{-- ============ View switcher ============ --}}
    <nav class="sp-tt2__viewbar" aria-label="Timetable views">
        @php
            $modes = [
                'teacher' => ['lbl' => 'Teacher',     'icon' => 'heroicon-m-user',            'hint' => 'Edit one teacher\'s sheet'],
                'class'   => ['lbl' => 'Class',       'icon' => 'heroicon-m-academic-cap',    'hint' => 'See a class\'s schedule'],
                'room'    => ['lbl' => 'Room',        'icon' => 'heroicon-m-building-office', 'hint' => 'Room utilisation'],
                'master'  => ['lbl' => 'Master',      'icon' => 'heroicon-m-squares-2x2',     'hint' => 'Principal\'s overview'],
            ];
        @endphp
        @foreach ($modes as $mKey => $m)
            <button type="button" wire:click="setViewMode('{{ $mKey }}')"
                @class(['sp-tt2__viewbtn', 'is-active' => $viewMode === $mKey])
                title="{{ $m['hint'] }}">
                <x-filament::icon icon="{{ $m['icon'] }}" class="w-4 h-4" />
                {{ $m['lbl'] }}
            </button>
        @endforeach
        <div class="sp-tt2__viewbar-sep"></div>
        <span class="sp-tt2__viewbar-hint">{{ $modes[$viewMode]['hint'] }}</span>
    </nav>

    {{-- ============ Top toolbar: teacher picker + collaborators + actions ============ --}}
    <header class="sp-tt2__tools">
        <div class="sp-tt2__tools-left">
            <div class="sp-tt2__tools-label">Teacher roster</div>
            <div class="sp-tt2__tabs">
                @foreach ($teachers as $id => $t)
                    <button type="button"
                            wire:click="selectTeacher('{{ $id }}')"
                            class="sp-tt2__tab {{ $id === $current['name'] ? '' : '' }} {{ $id === request()->input('teacher', $loop->parent ?? null) ? '' : '' }}"
                            @class([
                                'sp-tt2__tab',
                                'is-active' => $t['name'] === $current['name'],
                            ])
                            style="--c: {{ $t['color'] }};">
                        <span class="sp-tt2__tab-dot" style="background: {{ $t['color'] }};"></span>
                        <span class="sp-tt2__tab-body">
                            <strong>{{ $t['name'] }}</strong>
                            <em>{{ $t['subject'] }} · {{ $t['unit'] }}</em>
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="sp-tt2__tools-right">
            @if (($conflictCount ?? 0) > 0)
                <a href="#sp-tt2-first-conflict" class="sp-tt2__conflict-badge" title="Click to jump to first conflict">
                    <x-filament::icon icon="heroicon-m-exclamation-triangle" class="w-4 h-4" />
                    {{ $conflictCount }} conflict{{ $conflictCount === 1 ? '' : 's' }}
                </a>
            @else
                <span class="sp-tt2__conflict-badge sp-tt2__conflict-badge--ok">
                    <x-filament::icon icon="heroicon-m-check-circle" class="w-4 h-4" />
                    No conflicts
                </span>
            @endif
            <div class="sp-tt2__live">
                <div class="sp-tt2__avatars">
                    @foreach ($collaborators as $c)
                        <span class="sp-tt2__avatar" style="background: {{ $c['color'] }};" title="{{ $c['name'] }} · {{ $c['where'] }}">
                            {{ $c['initials'] }}
                            @if ($c['online'])
                                <span class="sp-tt2__avatar-dot"></span>
                            @endif
                        </span>
                    @endforeach
                </div>
                <span class="sp-tt2__livetxt">
                    <span class="sp-tt2__live-dot"></span>
                    {{ $collaborators->where('online', true)->count() }} editing now
                </span>
            </div>
            <button type="button" class="sp-tt2__btn sp-tt2__btn--ghost" onclick="window.print()">
                <x-filament::icon icon="heroicon-m-printer" class="w-4 h-4" /> Print
            </button>
            <button type="button"
                wire:click="exportAllTeachers"
                class="sp-tt2__btn sp-tt2__btn--ghost">
                <x-filament::icon icon="heroicon-m-document-arrow-down" class="w-4 h-4" /> Export all
            </button>
            <button type="button"
                wire:click="resetDraft"
                wire:confirm="This wipes every unlocked lesson. Locked cells stay. Continue?"
                class="sp-tt2__btn sp-tt2__btn--ghost">
                <x-filament::icon icon="heroicon-m-arrow-uturn-left" class="w-4 h-4" /> Reset draft
            </button>
            <button type="button"
                wire:click="generateDraft"
                wire:loading.attr="disabled"
                wire:target="generateDraft"
                class="sp-tt2__btn sp-tt2__btn--primary">
                <span wire:loading.remove wire:target="generateDraft" class="sp-tt2__btn-content">
                    <x-filament::icon icon="heroicon-m-sparkles" class="w-4 h-4" /> Auto-fill draft
                </span>
                <span wire:loading wire:target="generateDraft" class="sp-tt2__btn-content">
                    <x-filament::loading-indicator class="w-4 h-4" /> Solving…
                </span>
            </button>
            <button type="button"
                wire:click="publishSnapshot"
                wire:confirm="Freeze current state as a published snapshot? This is recorded forever."
                class="sp-tt2__btn sp-tt2__btn--primary">
                <x-filament::icon icon="heroicon-m-rocket-launch" class="w-4 h-4" /> Publish
            </button>
        </div>
    </header>

    {{-- ============ TEACHER VIEW (editable Sutomo sheet) ============ --}}
    @if ($viewMode === 'teacher')
    <article class="sp-tt2__sheet">

        {{-- Header band --}}
        <header class="sp-tt2__sheet-head">
            <div>
                <h1 class="sp-tt2__sheet-school">{{ strtoupper($current['unit']) }}</h1>
                <div class="sp-tt2__sheet-roster">ROSTER TP 2025 / 2026</div>
            </div>
            <div class="sp-tt2__sheet-meta">
                <div><span>NAMA</span><strong>: {{ strtoupper($current['name']) }}</strong></div>
                <div><span>BIDANG STUDI</span><strong>: {{ strtoupper($current['subject']) }}</strong></div>
            </div>
        </header>

        {{-- Sessions --}}
        @foreach ($sessions as $sKey => $sLabel)
            @php $rows = $periods[$sKey] ?? []; @endphp
            @if (! empty($rows))
                <section class="sp-tt2__session">
                    <div class="sp-tt2__session-label">{{ $sLabel }}</div>
                    <table class="sp-tt2__grid">
                        <thead>
                            <tr>
                                <th class="sp-tt2__th-jam">JAM<br>PEL</th>
                                <th class="sp-tt2__th-waktu">WAKTU</th>
                                @foreach ($days as $d)
                                    <th>{{ $d }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                @if (! empty($row['break']))
                                    <tr class="sp-tt2__row-break">
                                        <td class="sp-tt2__cell-jam">{{ $row['no'] }}</td>
                                        <td class="sp-tt2__cell-waktu">{{ $row['time'] }}</td>
                                        <td colspan="{{ count($days) }}" class="sp-tt2__break">{{ $row['break'] }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <td class="sp-tt2__cell-jam">{{ $row['no'] }}</td>
                                        <td class="sp-tt2__cell-waktu">{{ $row['time'] }}</td>
                                        @foreach ($days as $d)
                                            @php
                                                $cellKey   = $row['no'] . '-' . $d;
                                                $cellData  = $currentGrid[$sKey][$cellKey] ?? null;
                                                $cellId    = $sKey . '-' . $row['no'] . '-' . $d;
                                                $isActive  = $activeCell === $cellId;
                                                $cellConflicts = $cellData['conflicts'] ?? [];
                                                $hasError = collect($cellConflicts)->contains(fn ($c) => ($c['severity'] ?? '') === 'error');
                                                $hasWarn  = ! $hasError && ! empty($cellConflicts);
                                                $tooltip  = collect($cellConflicts)->pluck('message')->implode("\n");
                                                $busyMark = $cellData && empty($cellConflicts) && (strlen($cellKey) % 7 === 0);
                                            @endphp
                                            <td @class([
                                                    'sp-tt2__cell',
                                                    'has-val'      => $cellData,
                                                    'is-editing'   => $isActive,
                                                    'has-conflict' => $hasError,
                                                    'has-warning'  => $hasWarn,
                                                ])
                                                @if ($hasError || $hasWarn) title="{{ $tooltip }}" @endif
                                                wire:click="editCell('{{ $sKey }}', '{{ $row['no'] }}', '{{ $d }}')">
                                                @if ($cellData)
                                                    <span class="sp-tt2__cell-subj">{{ $cellData['subject'] }}</span>
                                                    <span class="sp-tt2__cell-cls">{{ $cellData['class'] }}</span>
                                                    @if (! empty($cellData['locked']))
                                                        <span class="sp-tt2__cell-lock" title="Locked — protected from auto-fill">
                                                            <x-filament::icon icon="heroicon-m-lock-closed" class="w-3 h-3" />
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="sp-tt2__cell-empty">+</span>
                                                @endif

                                                @if ($hasError || $hasWarn)
                                                    <span class="sp-tt2__cell-flag {{ $hasError ? 'is-error' : 'is-warn' }}" aria-hidden="true">!</span>
                                                @elseif ($busyMark)
                                                    <span class="sp-tt2__cell-presence" style="background: {{ $current['color'] }};" title="Edited just now"></span>
                                                @endif

                                                @if ($isActive)
                                                    <div class="sp-tt2__popover" wire:click.stop>
                                                        <div class="sp-tt2__popover-head">
                                                            <span class="sp-tt2__popover-title">
                                                                Period {{ $row['no'] }} · {{ ucfirst(strtolower($d)) }}
                                                            </span>
                                                            <button type="button" wire:click="cancelCell" class="sp-tt2__popover-x">
                                                                <x-filament::icon icon="heroicon-m-x-mark" class="w-3.5 h-3.5" />
                                                            </button>
                                                        </div>
                                                        <label class="sp-tt2__popover-lbl">Subject</label>
                                                        <input type="text" wire:model.live="pickerSubject"
                                                               placeholder="e.g. BIOLOGI"
                                                               class="sp-tt2__popover-input" />
                                                        <label class="sp-tt2__popover-lbl">Class</label>
                                                        <select wire:model.live="pickerClass" class="sp-tt2__popover-input">
                                                            <option value="">— pick —</option>
                                                            @foreach ($current['classes'] as $c)
                                                                <option value="{{ $c }}">{{ $c }}</option>
                                                            @endforeach
                                                        </select>
                                                        <label class="sp-tt2__popover-lock">
                                                            <input type="checkbox" wire:model.live="pickerLocked" />
                                                            <x-filament::icon icon="heroicon-m-lock-closed" class="w-3.5 h-3.5" />
                                                            <span>Lock — protect from auto-fill</span>
                                                        </label>
                                                        @if (! empty($cellConflicts))
                                                            <div class="sp-tt2__popover-issues">
                                                                @foreach ($cellConflicts as $issue)
                                                                    <div class="sp-tt2__popover-issue is-{{ $issue['severity'] ?? 'warning' }}">
                                                                        <x-filament::icon icon="heroicon-m-exclamation-triangle" class="w-3 h-3" />
                                                                        {{ $issue['message'] }}
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                        <div class="sp-tt2__popover-actions">
                                                            <button type="button" wire:click="clearCell" class="sp-tt2__popover-btn sp-tt2__popover-btn--ghost">Clear</button>
                                                            <button type="button" wire:click="saveCell"  class="sp-tt2__popover-btn sp-tt2__popover-btn--primary">Save</button>
                                                        </div>
                                                    </div>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </section>
            @endif
        @endforeach

        {{-- Footer counts --}}
        <footer class="sp-tt2__sheet-foot">
            <div class="sp-tt2__counts">
                @forelse ($classCounts as $cls => $n)
                    <span class="sp-tt2__count-pill">
                        <strong>{{ $cls }}</strong>
                        <em>= {{ $n }}</em>
                    </span>
                @empty
                    <span class="sp-tt2__counts-empty">No slots assigned yet. Click a cell to add a lesson.</span>
                @endforelse
                @if (! empty($classCounts))
                    <span class="sp-tt2__counts-total">
                        TOTAL: {{ array_sum($classCounts) }} lessons / week
                    </span>
                @endif
            </div>

            <div class="sp-tt2__sign">
                <div>Medan, _____________________</div>
                <div class="sp-tt2__sign-role">Kepala {{ $current['unit'] }}</div>
                <div class="sp-tt2__sign-name">Dwigamar Hadi Purwanto, M.Kom.</div>
            </div>
        </footer>

    </article>
    @endif

    {{-- ============ CLASS VIEW (read-only sheet per class) ============ --}}
    @if ($viewMode === 'class')
    <article class="sp-tt2__sheet">
        <header class="sp-tt2__sheet-head">
            <div>
                <h1 class="sp-tt2__sheet-school">JADWAL KELAS</h1>
                <div class="sp-tt2__sheet-roster">ROSTER TP 2025 / 2026</div>
            </div>
            <div class="sp-tt2__sheet-meta">
                <label class="sp-tt2__pickerline">
                    <span>KELAS</span>
                    <select wire:change="selectClass($event.target.value)" class="sp-tt2__popover-input" style="max-width:180px;">
                        @foreach ($allClassCodes as $c)
                            <option value="{{ $c }}" @selected($selectedClassCode === $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </header>

        @if (! $selectedClassCode)
            <p class="sp-tt2__empty">No classes available yet.</p>
        @else
            @foreach ($sessions as $sKey => $sLabel)
                @php $rows = $classPeriods[$sKey] ?? []; @endphp
                @if (! empty($rows))
                    <section class="sp-tt2__session">
                        <div class="sp-tt2__session-label">{{ $sLabel }}</div>
                        <table class="sp-tt2__grid">
                            <thead>
                                <tr>
                                    <th class="sp-tt2__th-jam">JAM<br>PEL</th>
                                    <th class="sp-tt2__th-waktu">WAKTU</th>
                                    @foreach ($days as $d) <th>{{ $d }}</th> @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $row)
                                    @if (! empty($row['break']))
                                        <tr class="sp-tt2__row-break">
                                            <td class="sp-tt2__cell-jam">{{ $row['no'] }}</td>
                                            <td class="sp-tt2__cell-waktu">{{ $row['time'] }}</td>
                                            <td colspan="{{ count($days) }}" class="sp-tt2__break">{{ $row['break'] }}</td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td class="sp-tt2__cell-jam">{{ $row['no'] }}</td>
                                            <td class="sp-tt2__cell-waktu">{{ $row['time'] }}</td>
                                            @foreach ($days as $d)
                                                @php
                                                    $key = $row['no'] . '-' . $d;
                                                    $c   = $classGrid[$sKey][$key] ?? null;
                                                    $hasErr = $c && collect($c['conflicts'])->contains(fn ($i) => ($i['severity'] ?? '') === 'error');
                                                @endphp
                                                <td @class([
                                                        'sp-tt2__cell',
                                                        'sp-tt2__cell--readonly',
                                                        'has-val'      => $c,
                                                        'has-conflict' => $hasErr,
                                                    ])
                                                    @if ($c) wire:click="jumpToTeacher('{{ $c['teacher_ref'] }}')"
                                                            title="Click to edit in {{ $c['teacher'] }}'s sheet" @endif>
                                                    @if ($c)
                                                        <span class="sp-tt2__cell-subj">{{ $c['subject'] }}</span>
                                                        <span class="sp-tt2__cell-cls" style="color: {{ $c['color'] }};">
                                                            <span class="sp-tt2__cell-tdot" style="background: {{ $c['color'] }};"></span>
                                                            {{ $c['teacher'] }}
                                                        </span>
                                                    @else
                                                        <span class="sp-tt2__cell-empty">—</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </section>
                @endif
            @endforeach
        @endif
    </article>
    @endif

    {{-- ============ ROOM VIEW ============ --}}
    @if ($viewMode === 'room')
    <article class="sp-tt2__sheet">
        <header class="sp-tt2__sheet-head">
            <div>
                <h1 class="sp-tt2__sheet-school">RUANGAN / ROOM</h1>
                <div class="sp-tt2__sheet-roster">ROSTER TP 2025 / 2026</div>
            </div>
            <div class="sp-tt2__sheet-meta">
                @if (empty($allRooms))
                    <em style="color:#94a3b8;">No room assignments yet.</em>
                @else
                    <label class="sp-tt2__pickerline">
                        <span>RUANGAN</span>
                        <select wire:change="selectRoom($event.target.value)" class="sp-tt2__popover-input" style="max-width:180px;">
                            @foreach ($allRooms as $r)
                                <option value="{{ $r }}" @selected($selectedRoom === $r)>{{ $r }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif
            </div>
        </header>

        @if (empty($allRooms))
            <p class="sp-tt2__empty">
                No room (<code>room</code>) values have been assigned to any lesson yet.<br>
                Tip: lock labs/halls to specific lessons via the teacher view to enable room clash detection.
            </p>
        @elseif ($selectedRoom)
            @foreach ($sessions as $sKey => $sLabel)
                @php $rows = $periods[$sKey] ?? []; @endphp
                @if (! empty($rows))
                    <section class="sp-tt2__session">
                        <div class="sp-tt2__session-label">{{ $sLabel }}</div>
                        <table class="sp-tt2__grid">
                            <thead>
                                <tr>
                                    <th class="sp-tt2__th-jam">JAM<br>PEL</th>
                                    <th class="sp-tt2__th-waktu">WAKTU</th>
                                    @foreach ($days as $d) <th>{{ $d }}</th> @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $row)
                                    @if (! empty($row['break']))
                                        <tr class="sp-tt2__row-break">
                                            <td class="sp-tt2__cell-jam">{{ $row['no'] }}</td>
                                            <td class="sp-tt2__cell-waktu">{{ $row['time'] }}</td>
                                            <td colspan="{{ count($days) }}" class="sp-tt2__break">{{ $row['break'] }}</td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td class="sp-tt2__cell-jam">{{ $row['no'] }}</td>
                                            <td class="sp-tt2__cell-waktu">{{ $row['time'] }}</td>
                                            @foreach ($days as $d)
                                                @php
                                                    $key = $row['no'] . '-' . $d;
                                                    $c   = $roomGrid[$sKey][$key] ?? null;
                                                    $hasErr = $c && collect($c['conflicts'])->contains(fn ($i) => ($i['severity'] ?? '') === 'error');
                                                @endphp
                                                <td @class([
                                                        'sp-tt2__cell',
                                                        'sp-tt2__cell--readonly',
                                                        'has-val'      => $c,
                                                        'has-conflict' => $hasErr,
                                                    ])
                                                    @if ($c) wire:click="jumpToTeacher('{{ $c['teacher_ref'] }}')" @endif>
                                                    @if ($c)
                                                        <span class="sp-tt2__cell-subj">{{ $c['subject'] }}</span>
                                                        <span class="sp-tt2__cell-cls">{{ $c['class'] }}</span>
                                                        <span class="sp-tt2__cell-cls" style="color: {{ $c['color'] }};">{{ $c['teacher'] }}</span>
                                                    @else
                                                        <span class="sp-tt2__cell-empty">—</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </section>
                @endif
            @endforeach
        @endif
    </article>
    @endif

    {{-- ============ MASTER VIEW (principal bird's-eye) ============ --}}
    @if ($viewMode === 'master')
    <article class="sp-tt2__sheet">
        <header class="sp-tt2__sheet-head">
            <div>
                <h1 class="sp-tt2__sheet-school">MASTER OVERVIEW · ALL TEACHERS</h1>
                <div class="sp-tt2__sheet-roster">ROSTER TP 2025 / 2026 · {{ count($masterCards) }} teachers</div>
            </div>
            <div class="sp-tt2__sheet-meta">
                <div><span>Total lessons</span><strong>: {{ collect($masterCards)->sum('total') }}</strong></div>
                <div><span>Total conflicts</span><strong style="color:#dc2626;">: {{ collect($masterCards)->sum('conflicts') }}</strong></div>
            </div>
        </header>

        @php
            $byUnit = collect($masterCards)->groupBy('unit');
        @endphp

        @foreach ($byUnit as $unitName => $cards)
            <section class="sp-tt2__master-unit">
                <div class="sp-tt2__session-label">{{ strtoupper($unitName) }}</div>
                <div class="sp-tt2__master-grid">
                    @foreach ($cards as $card)
                        <button type="button" wire:click="jumpToTeacher('{{ $card['id'] }}')"
                                class="sp-tt2__master-card"
                                style="--c: {{ $card['color'] }};"
                                title="Open {{ $card['name'] }}'s sheet">
                            <div class="sp-tt2__master-head">
                                <span class="sp-tt2__master-dot" style="background: {{ $card['color'] }};"></span>
                                <div class="sp-tt2__master-id">
                                    <strong>{{ $card['name'] }}</strong>
                                    <em>{{ $card['subject'] }}</em>
                                </div>
                                @if ($card['conflicts'] > 0)
                                    <span class="sp-tt2__master-badge is-bad">{{ $card['conflicts'] }} ⚠</span>
                                @endif
                            </div>

                            <div class="sp-tt2__master-stats">
                                <span class="sp-tt2__master-stat">
                                    <strong>{{ $card['total'] }}</strong>
                                    <em>jam / minggu</em>
                                </span>
                                @if ($card['cap'])
                                    @php $pct = min(100, round(($card['total'] / max(1,$card['cap'])) * 100)); @endphp
                                    <span class="sp-tt2__master-stat">
                                        <strong>{{ $card['cap'] }}</strong>
                                        <em>cap</em>
                                    </span>
                                    <div class="sp-tt2__master-bar" title="{{ $pct }}% of cap">
                                        <div class="sp-tt2__master-bar-fill" style="width: {{ $pct }}%; background: {{ $pct > 100 ? '#dc2626' : ($pct > 90 ? '#f59e0b' : $card['color']) }};"></div>
                                    </div>
                                @endif
                            </div>

                            <div class="sp-tt2__master-heat">
                                @foreach ($days as $d)
                                    @php
                                        $n = $card['byDay'][$d] ?? 0;
                                        $alpha = min(1, $n / 8);
                                    @endphp
                                    <div class="sp-tt2__master-heat-day" title="{{ $d }}: {{ $n }} jam">
                                        <div class="sp-tt2__master-heat-fill"
                                             style="background: {{ $card['color'] }}; opacity: {{ max(0.08, $alpha) }};"></div>
                                        <span>{{ substr($d, 0, 3) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </button>
                    @endforeach
                </div>
            </section>
        @endforeach
    </article>
    @endif

    {{-- ============ PRINT-ALL: stacked per-teacher sheets ============ --}}
    @if ($printAll)
    <section class="sp-tt2__printall" id="sp-tt2-printall">
        @foreach ($printSheets as $sheet)
            <article class="sp-tt2__sheet sp-tt2__printall-sheet">
                <header class="sp-tt2__sheet-head">
                    <div>
                        <h1 class="sp-tt2__sheet-school">{{ strtoupper($sheet['teacher']['unit']) }}</h1>
                        <div class="sp-tt2__sheet-roster">ROSTER TP 2025 / 2026 · {{ $sheet['teacher']['name'] }}</div>
                    </div>
                    <div class="sp-tt2__sheet-meta">
                        <div><strong>Guru:</strong> {{ $sheet['teacher']['name'] }}</div>
                        <div><strong>Mata Pelajaran:</strong> {{ $sheet['teacher']['subject'] }}</div>
                        <div><strong>Jumlah Jam:</strong> {{ array_sum($sheet['counts']) }} JP</div>
                    </div>
                </header>
                @foreach (['pagi' => 'PAGI', 'sore' => 'SORE'] as $sk => $sLabel)
                    @php $rows = $sheet['periods'][$sk] ?? []; @endphp
                    @if (! empty($rows))
                    <table class="sp-tt2__grid">
                        <thead>
                            <tr>
                                <th class="sp-tt2__th-sess">{{ $sLabel }}</th>
                                <th>JAM</th>
                                <th>WAKTU</th>
                                @foreach ($days as $d)<th>{{ $d }}</th>@endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                @if (! empty($row['break']))
                                    <tr class="sp-tt2__row-break">
                                        <td colspan="{{ 3 + count($days) }}">ISTIRAHAT</td>
                                    </tr>
                                @else
                                    <tr>
                                        <td></td>
                                        <td class="sp-tt2__td-no">{{ $row['no'] }}</td>
                                        <td class="sp-tt2__td-time">{{ $row['time'] }}</td>
                                        @foreach ($days as $d)
                                            @php $c = $sheet['grid'][$sk][$row['no'] . '-' . $d] ?? null; @endphp
                                            <td class="sp-tt2__td-cell">
                                                @if ($c)
                                                    <strong>{{ $c['subject'] }}</strong>
                                                    <small>{{ $c['class'] }}</small>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                @endforeach
            </article>
        @endforeach
    </section>
    <script>
        window.addEventListener('timetable-print-ready', () => {
            setTimeout(() => {
                window.print();
                // Reset flag after print so the UI returns to normal.
                window.Livewire?.dispatch('reset-print-all');
            }, 250);
        });
    </script>
    @endif

</div>
</x-filament-panels::page>
