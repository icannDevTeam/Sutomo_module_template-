<x-filament-panels::page>
    @php
        $tabs = [
            'today'    => ['label' => 'Today',    'icon' => 'heroicon-o-sun'],
            'tomorrow' => ['label' => 'Tomorrow', 'icon' => 'heroicon-o-arrow-right'],
            'weekly'   => ['label' => 'Weekly',   'icon' => 'heroicon-o-calendar-days'],
            'monthly'  => ['label' => 'Monthly',  'icon' => 'heroicon-o-calendar'],
        ];

        $displayDays = $rosterDays;
        if ($view === 'monthly') {
            $activeDates = collect($rosterRows)
                ->flatMap(fn ($row) => array_keys($row['days'] ?? []))
                ->unique()
                ->all();

            $displayDays = array_values(array_filter(
                $rosterDays,
                fn ($day) => in_array($day['date'], $activeDates, true)
            ));

            if (empty($displayDays)) {
                $displayDays = $rosterDays;
            }
        }

        $statusChip = [
            'accepted'  => 'sp-pill-green',
            'pending'   => 'sp-pill-amber',
            'declined'  => 'sp-pill-red',
            'completed' => 'sp-pill-slate',
        ];

        $entryBorder = [
            'leave' => 'border-sky-200 bg-sky-50/80 dark:border-sky-500/30 dark:bg-sky-500/10',
            'duty'  => 'border-emerald-200 bg-emerald-50/80 dark:border-emerald-500/30 dark:bg-emerald-500/10',
        ];
    @endphp

    <x-principal.module-hero
        title="Substitution Roster"
        description="Teacher-first coverage matrix for approved leaves and assigned duties. Filter by date window to see all substitutions happening that day, week, or month."
        icon="heroicon-o-table-cells"
        tone="slate"
    >
        <div class="inline-flex items-center gap-2 rounded-full bg-white/80 px-3 py-1 text-xs text-slate-600 ring-1 ring-slate-200 dark:bg-slate-900/40 dark:text-slate-300 dark:ring-slate-700">
            <span class="font-semibold">{{ count($rosterRows) }}</span>
            <span>teachers with coverage</span>
        </div>
    </x-principal.module-hero>

    <div class="sp-cm-intro">
        This page is the operational coverage roster. It shows who is covering which lesson or duty, on which day, and where there are still unfilled gaps that need attention.
    </div>

    <div class="sp-kpis">
        @foreach ([
            ['Teachers covered', count($rosterRows), 'slate', 'heroicon-o-users'],
            ['Periods covered', $data['summary']['periods_covered'], 'emerald', 'heroicon-o-shield-check'],
            ['Standalone duties', $data['summary']['duties_count'], 'sky', 'heroicon-o-clipboard-document-list'],
            ['Open gaps', $data['summary']['gaps_count'], $data['summary']['gaps_count'] > 0 ? 'rose' : 'amber', 'heroicon-o-exclamation-triangle'],
        ] as [$label, $value, $tone, $icon])
            <div class="sp-kpi sp-kpi--{{ $tone }}">
                <div class="sp-kpi-head">
                    <span class="sp-kpi-label">{{ $label }}</span>
                    <span class="sp-kpi-icon"><x-filament::icon :icon="$icon" style="width:16px;height:16px;" /></span>
                </div>
                <div class="sp-kpi-value">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <div class="sp-card">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="sp-card-h">Roster window</div>
                <div class="sp-card-sub">Switch between Today, Tomorrow, Weekly, and Monthly windows, then pick any date anchor.</div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" wire:click="prevWindow" class="sp-btn sp-btn-ghost !py-2 !px-3">← Prev</button>
                <button type="button" wire:click="currentWindow" class="sp-btn sp-btn-ghost !py-2 !px-3">Current</button>
                <button type="button" wire:click="nextWindow" class="sp-btn sp-btn-ghost !py-2 !px-3">Next →</button>
                <span class="text-xs text-gray-500">
                    {{ \Carbon\Carbon::parse($weekStart)->format('d M Y') }} – {{ \Carbon\Carbon::parse($weekEnd)->format('d M Y') }}
                </span>
            </div>
        </div>

        <div class="sp-tabs mt-4">
            <div class="sp-tabs__group">
                @foreach ($tabs as $key => $tab)
                    <button type="button" wire:click="setView('{{ $key }}')" @class(['sp-tab', 'is-active' => $view === $key])>
                        <x-dynamic-component :component="$tab['icon']" class="h-4 w-4" />
                        {{ $tab['label'] }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <div class="relative">
                <input type="date" wire:model.live="focusDate" class="sp-tabs__search !w-44 appearance-none pr-8" />
                <x-heroicon-m-calendar class="pointer-events-none absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            </div>

            <div class="relative">
                <select wire:model.live="campus" class="sp-tabs__search !w-44 appearance-none pr-8">
                    <option value="">All campuses</option>
                    @foreach ($campuses as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
                <x-heroicon-m-chevron-down class="pointer-events-none absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            </div>

            <div class="relative">
                <select wire:model.live="statusFilter" class="sp-tabs__search !w-44 appearance-none pr-8">
                    <option value="">All statuses</option>
                    <option value="pending">Pending</option>
                    <option value="accepted">Accepted</option>
                </select>
                <x-heroicon-m-chevron-down class="pointer-events-none absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            </div>

            <div class="text-xs text-gray-500">
                Leaves and duties are grouped by substitute teacher, so the roster shows what each teacher is actually covering.
            </div>
            @if ($view === 'monthly')
                <span class="sp-pill sp-pill-indigo">Monthly showing active days only</span>
            @endif
        </div>
    </div>

    <div class="sp-card">
        <div class="sp-card-h">How to read this roster</div>
        <div class="sp-card-sub">
            Each row is a teacher. Each day column lists the classes, periods, and duties they are covering on that date. Blue cards are leave coverages, green cards are standalone duties, and amber warnings flag coverage that still needs a duty handoff.
        </div>
    </div>

    <div class="sp-card overflow-x-auto p-0">
        <table class="min-w-full border-collapse text-xs">
            <thead class="bg-gray-50/80 text-gray-600 dark:bg-white/[0.03] dark:text-gray-300">
                <tr>
                    <th class="sticky left-0 z-10 w-72 border-b border-gray-200 bg-gray-50/95 px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.12em] text-gray-500 backdrop-blur dark:border-white/10 dark:bg-slate-950/95 dark:text-gray-400">Teacher</th>
                    @foreach ($displayDays as $day)
                        <th class="min-w-[220px] border-b border-gray-200 px-3 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.12em] text-gray-500 dark:border-white/10 dark:text-gray-400">
                            <div class="flex items-center justify-between gap-2">
                                <span>{{ $day['label'] }}</span>
                                <span class="rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-gray-500 ring-1 ring-gray-200 dark:bg-white/5 dark:text-gray-300 dark:ring-white/10">
                                    {{ \Carbon\Carbon::parse($day['date'])->format('d M') }}
                                </span>
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rosterRows as $row)
                    @php
                        $teacherInitial = \Illuminate\Support\Str::of($row['teacher_name'])->substr(0, 1);
                        $teacherDays = $row['days'] ?? [];
                    @endphp
                    <tr class="border-t border-gray-100/80 align-top {{ $row['gap_count'] > 0 ? 'bg-amber-50/20' : '' }}">
                        <th class="sticky left-0 z-10 border-b border-gray-100 bg-white px-4 py-4 text-left align-top dark:border-white/10 dark:bg-slate-950">
                            <div class="flex items-start gap-3">
                                <div class="sp-avatar">{{ $teacherInitial }}</div>
                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $row['teacher_name'] }}</div>
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-gray-500 dark:text-gray-400">
                                        @if ($row['teacher_subject'])
                                            <span class="rounded-full bg-gray-100 px-2 py-0.5 dark:bg-white/5">{{ $row['teacher_subject'] }}</span>
                                        @endif
                                        @if ($row['teacher_campus'])
                                            <span class="rounded-full bg-gray-100 px-2 py-0.5 dark:bg-white/5">{{ strtoupper($row['teacher_campus']) }}</span>
                                        @endif
                                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">{{ $row['covered_count'] }} items</span>
                                        @if ($row['gap_count'] > 0)
                                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-amber-700 dark:bg-amber-500/10 dark:text-amber-200">{{ $row['gap_count'] }} gaps</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </th>

                        @foreach ($displayDays as $day)
                            @php $entries = $teacherDays[$day['date']] ?? []; @endphp
                            <td class="border-b border-gray-100 px-2 py-3 align-top dark:border-white/10">
                                <div class="space-y-2">
                                    @forelse ($entries as $cell)
                                        @php
                                            $tone = $entryBorder[$cell['source']] ?? 'border-gray-200 bg-white dark:border-white/10 dark:bg-white/5';
                                            $status = $statusChip[$cell['status']] ?? 'sp-pill-gray';
                                        @endphp
                                        <div class="rounded-xl border p-3 shadow-sm {{ $tone }}">
                                            <div class="flex items-start justify-between gap-2">
                                                <span class="sp-pill {{ $cell['source'] === 'leave' ? 'sp-pill-blue' : 'sp-pill-green' }}">
                                                    {{ $cell['source'] === 'leave' ? 'Leave coverage' : 'Duty assignment' }}
                                                </span>
                                                <span class="sp-pill {{ $status }}">{{ strtoupper($cell['status']) }}</span>
                                            </div>

                                            <div class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                                                @if ($cell['source'] === 'leave')
                                                    {{ $cell['class_code'] ?? 'Class' }} · {{ $cell['subject'] }}
                                                @else
                                                    {{ $cell['subject'] }}
                                                @endif
                                            </div>

                                            <div class="mt-1 text-[11px] leading-5 text-gray-600 dark:text-gray-300">
                                                @if ($cell['source'] === 'leave')
                                                    <div>Assigned duty: <span class="font-medium text-gray-900 dark:text-white">Class substitution</span></div>
                                                    <div>Period: <span class="font-medium text-gray-900 dark:text-white">{{ $cell['period'] }}</span></div>
                                                    <div>Session: <span class="font-medium text-gray-900 dark:text-white">{{ $cell['session'] ?: 'Regular' }}</span></div>
                                                    <div>Original: <span class="font-medium text-gray-900 dark:text-white">{{ $cell['original'] }}</span></div>
                                                    <div>Covering: <span class="font-medium text-gray-900 dark:text-white">{{ $cell['substitute'] }}</span></div>
                                                    @if ($cell['room'])
                                                        <div>Room: <span class="font-medium text-gray-900 dark:text-white">{{ $cell['room'] }}</span></div>
                                                    @endif
                                                @else
                                                    <div>Assigned duty: <span class="font-medium text-gray-900 dark:text-white">Out-of-class assignment</span></div>
                                                    <div>Duty date: <span class="font-medium text-gray-900 dark:text-white">{{ $day['label'] }} · {{ \Carbon\Carbon::parse($cell['date'])->format('d M') }}</span></div>
                                                    @if ($cell['room'])
                                                        <div>Location: <span class="font-medium text-gray-900 dark:text-white">{{ $cell['room'] }}</span></div>
                                                    @endif
                                                    <div>Covered by: <span class="font-medium text-gray-900 dark:text-white">{{ $cell['substitute'] }}</span></div>
                                                @endif
                                            </div>

                                            @if ($cell['gap'])
                                                <div class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-2 py-1 text-[10px] font-medium text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
                                                    ⚠ {{ $cell['gap_reason'] }}
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        @if ($view === 'monthly')
                                            <div class="rounded-lg border border-dashed border-gray-200 px-2 py-2 text-center text-[10px] text-gray-300 dark:border-white/10 dark:text-gray-500">-</div>
                                        @else
                                            <div class="rounded-xl border border-dashed border-gray-200 px-3 py-4 text-center text-[11px] text-gray-300 dark:border-white/10 dark:text-gray-500">No coverage</div>
                                        @endif
                                    @endforelse
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($displayDays) + 1 }}" class="px-6 py-12 text-center text-sm text-gray-500">
                            No coverage rows match the current filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div id="eligibility-lookup"
         x-data="{}"
         @scroll-to-eligibility.window="$nextTick(() => document.getElementById('eligibility-lookup')?.scrollIntoView({ behavior: 'smooth', block: 'start' }))"
         class="sp-card mt-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Eligibility lookup</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Pick a leave to inspect substitute availability before assigning the coverage.</p>
            </div>
            @if($eligibilityLeaveId)
                <button type="button" wire:click="clearInspect" class="text-xs font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-300 dark:hover:text-indigo-200">
                    Clear
                </button>
            @endif
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-2">
            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Leave:</label>
            <div class="relative">
                <select wire:model.live="eligibilityLeaveId" class="min-w-[320px] appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 pr-8 text-xs dark:border-white/10 dark:bg-white/5 dark:text-white">
                    <option value="">— select a leave —</option>
                    @foreach($inspectableLeaves as $l)
                        <option value="{{ $l->id }}">
                            {{ $l->teacher?->name ?? '—' }} ·
                            {{ ucfirst($l->status) }} ·
                            {{ optional($l->starts_at)->format('d M') }}–{{ optional($l->ends_at)->format('d M') }}
                            ({{ \App\Models\LeaveType::labelFor($l->type) }})
                        </option>
                    @endforeach
                </select>
                <x-heroicon-m-chevron-down class="pointer-events-none absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            </div>
            @if($inspectableLeaves->isEmpty())
                <span class="text-xs text-gray-400 italic">No leaves in current window.</span>
            @endif
        </div>

        @if($eligibilityLeaveId && $eligibility)
            <div class="mt-4 border-t border-gray-100 pt-4 dark:border-white/10">
                @if($inspectedLeave)
                    <div class="mb-3 text-xs text-gray-700 dark:text-gray-300">
                        <strong>{{ $inspectedLeave->teacher?->name }}</strong>
                        · {{ \App\Models\LeaveType::labelFor($inspectedLeave->type) }}
                        · {{ optional($inspectedLeave->starts_at)->format('d M') }}–{{ optional($inspectedLeave->ends_at)->format('d M Y') }}
                        @if($inspectedLeave->substitute_teacher_id)
                            · <span class="text-emerald-700 dark:text-emerald-300">currently covered by {{ $inspectedLeave->substitute?->name }}</span>
                        @endif
                    </div>
                @endif

                @include('filament.principal.teacher.eligibility-grid', [
                    'eligibility' => $eligibility,
                    'showPick'    => false,
                ])
            </div>
        @elseif($eligibilityLeaveId && ! $eligibility)
            <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
                Could not build eligibility because the teacher is missing, there is no published timetable, or the leave window falls outside school days.
            </div>
        @endif
    </div>
</x-filament-panels::page>
