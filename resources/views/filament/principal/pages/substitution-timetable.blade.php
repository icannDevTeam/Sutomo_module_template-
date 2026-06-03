<x-filament-panels::page>
    @php
        $tabs = [
            'week'  => ['label' => 'Week',  'icon' => 'heroicon-o-calendar-days'],
            'today' => ['label' => 'Today', 'icon' => 'heroicon-o-sun'],
            'list'  => ['label' => 'List',  'icon' => 'heroicon-o-queue-list'],
        ];

        $statusColor = [
            'accepted'  => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'pending'   => 'bg-sky-100 text-sky-700 border-sky-200',
            'declined'  => 'bg-rose-100 text-rose-700 border-rose-200',
            'completed' => 'bg-gray-100 text-gray-600 border-gray-200',
        ];
        $gapClass = 'bg-amber-50 text-amber-800 border-amber-300';
    @endphp

    {{-- Top summary strip --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
        <div class="rounded-xl border border-gray-200 bg-white p-3">
            <div class="text-[11px] uppercase tracking-wider text-gray-500">Teachers on leave</div>
            <div class="text-2xl font-bold text-gray-900">{{ $data['summary']['teachers_on_leave'] }}</div>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3">
            <div class="text-[11px] uppercase tracking-wider text-emerald-700">Periods covered</div>
            <div class="text-2xl font-bold text-emerald-800">{{ $data['summary']['periods_covered'] }}</div>
        </div>
        <div class="rounded-xl border border-sky-200 bg-sky-50 p-3">
            <div class="text-[11px] uppercase tracking-wider text-sky-700">Standalone duties</div>
            <div class="text-2xl font-bold text-sky-800">{{ $data['summary']['duties_count'] }}</div>
        </div>
        <div class="rounded-xl border p-3 {{ $data['summary']['gaps_count'] > 0 ? 'border-amber-300 bg-amber-50' : 'border-gray-200 bg-white' }}">
            <div class="text-[11px] uppercase tracking-wider {{ $data['summary']['gaps_count'] > 0 ? 'text-amber-700' : 'text-gray-500' }}">Gaps</div>
            <div class="text-2xl font-bold {{ $data['summary']['gaps_count'] > 0 ? 'text-amber-800' : 'text-gray-900' }}">{{ $data['summary']['gaps_count'] }}</div>
        </div>
    </div>

    {{-- Toolbar: view tabs + filters --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="flex flex-wrap items-center gap-2">
            @foreach ($tabs as $key => $tab)
                <button
                    type="button"
                    wire:click="setView('{{ $key }}')"
                    class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium transition
                           {{ $view === $key
                               ? 'bg-primary-600 text-white shadow'
                               : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50' }}"
                >
                    <x-dynamic-component :component="$tab['icon']" class="h-4 w-4" />
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if ($view === 'week')
                <button type="button" wire:click="prevWeek"
                    class="rounded-lg bg-white px-2.5 py-1.5 text-sm ring-1 ring-gray-200 hover:bg-gray-50">
                    ← Prev
                </button>
                <button type="button" wire:click="thisWeek"
                    class="rounded-lg bg-white px-3 py-1.5 text-sm font-medium ring-1 ring-gray-200 hover:bg-gray-50">
                    This week
                </button>
                <button type="button" wire:click="nextWeek"
                    class="rounded-lg bg-white px-2.5 py-1.5 text-sm ring-1 ring-gray-200 hover:bg-gray-50">
                    Next →
                </button>
                <span class="text-xs text-gray-500 ml-2">
                    {{ \Carbon\Carbon::parse($weekStart)->format('d M Y') }} – {{ \Carbon\Carbon::parse($weekEnd)->format('d M Y') }}
                </span>
            @endif

            <select wire:model.live="campus"
                class="rounded-lg border-gray-200 bg-white text-sm">
                <option value="">All campuses</option>
                @foreach ($campuses as $c)
                    <option value="{{ $c }}">{{ $c }}</option>
                @endforeach
            </select>

            <select wire:model.live="statusFilter"
                class="rounded-lg border-gray-200 bg-white text-sm">
                <option value="">All statuses</option>
                <option value="pending">Pending</option>
                <option value="accepted">Accepted</option>
            </select>
        </div>
    </div>

    {{-- WEEK VIEW --}}
    @if ($view === 'week' || $view === 'today')
        @if (empty($data['cells']) && empty($offGrid))
            <div class="rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">
                No active substitutions for this window.
            </div>
        @else
            <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
                <table class="min-w-full text-xs">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="w-20 px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-500">Period</th>
                            @foreach ($data['days'] as $d)
                                <th class="px-3 py-2 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-600">
                                    <div>{{ $d['label'] }}</div>
                                    <div class="text-[10px] font-normal text-gray-400">{{ \Carbon\Carbon::parse($d['date'])->format('d M') }}</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data['periods'] as $period)
                            @php
                                $rowHasAny = false;
                                foreach ($data['days'] as $d) {
                                    if (! empty($grid[$d['date']][$period] ?? [])) { $rowHasAny = true; break; }
                                }
                            @endphp
                            @if (! $rowHasAny) @continue @endif
                            <tr class="border-t border-gray-100">
                                <th class="px-3 py-2 text-left align-top text-[11px] font-semibold text-gray-700">{{ $period }}</th>
                                @foreach ($data['days'] as $d)
                                    @php $cellList = $grid[$d['date']][$period] ?? []; @endphp
                                    <td class="px-2 py-2 align-top">
                                        @forelse ($cellList as $cell)
                                            @php
                                                $cls = $cell['gap'] ? $gapClass : ($statusColor[$cell['status']] ?? 'bg-gray-100 text-gray-700 border-gray-200');
                                            @endphp
                                            <div class="rounded-md border px-2 py-1.5 mb-1 {{ $cls }}">
                                                <div class="text-[10px] font-semibold uppercase tracking-wider opacity-70">
                                                    {{ $cell['class_code'] }} · {{ $cell['subject'] }}
                                                    @if ($cell['room']) <span class="opacity-60">· {{ $cell['room'] }}</span> @endif
                                                </div>
                                                <div class="mt-0.5 text-[11px]">
                                                    <span class="line-through opacity-60">{{ $cell['original'] }}</span>
                                                    →
                                                    <span class="font-semibold">{{ $cell['substitute'] }}</span>
                                                </div>
                                                @if ($cell['gap'])
                                                    <div class="mt-0.5 text-[10px] font-medium">⚠ {{ $cell['gap_reason'] }}</div>
                                                @endif
                                            </div>
                                        @empty
                                            <span class="text-gray-300">·</span>
                                        @endforelse
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if (! empty($offGrid))
                <div class="mt-4 rounded-xl border border-gray-200 bg-white p-3">
                    <div class="text-[11px] uppercase tracking-wider text-gray-500 mb-2">Standalone duties (no fixed period)</div>
                    <ul class="space-y-1.5">
                        @foreach ($offGrid as $cell)
                            @php $cls = $statusColor[$cell['status']] ?? 'bg-gray-100 text-gray-700 border-gray-200'; @endphp
                            <li class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="text-gray-500">{{ \Carbon\Carbon::parse($cell['date'])->format('d M') }} · {{ $cell['day_label'] }}</span>
                                <span class="rounded-md border px-2 py-0.5 {{ $cls }}">{{ $cell['status'] }}</span>
                                <span class="font-medium text-gray-900">{{ $cell['subject'] }}</span>
                                @if ($cell['room']) <span class="text-gray-500">@ {{ $cell['room'] }}</span> @endif
                                <span class="text-gray-700">— {{ $cell['substitute'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endif
    @endif

    {{-- LIST VIEW --}}
    @if ($view === 'list')
        @if (empty($data['cells']))
            <div class="rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">
                No active substitutions for this window.
            </div>
        @else
            <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
                <table class="min-w-full text-xs">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-500">Date</th>
                            <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-500">Period</th>
                            <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-500">Class</th>
                            <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-500">Subject</th>
                            <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-500">Original → Substitute</th>
                            <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-500">Source</th>
                            <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-500">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data['cells'] as $cell)
                            @php $cls = $cell['gap'] ? $gapClass : ($statusColor[$cell['status']] ?? 'bg-gray-100 text-gray-700 border-gray-200'); @endphp
                            <tr class="border-t border-gray-100 {{ $cell['gap'] ? 'bg-amber-50/40' : '' }}">
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($cell['date'])->format('d M') }}</div>
                                    <div class="text-[10px] text-gray-500">{{ $cell['day_label'] }}</div>
                                </td>
                                <td class="px-3 py-2">{{ $cell['period'] }}</td>
                                <td class="px-3 py-2">{{ $cell['class_code'] ?? '—' }}</td>
                                <td class="px-3 py-2">
                                    {{ $cell['subject'] }}
                                    @if ($cell['room']) <span class="text-gray-400">· {{ $cell['room'] }}</span> @endif
                                </td>
                                <td class="px-3 py-2">
                                    @if ($cell['original'])
                                        <span class="line-through text-gray-500">{{ $cell['original'] }}</span>
                                        →
                                    @endif
                                    <span class="font-semibold">{{ $cell['substitute'] }}</span>
                                </td>
                                <td class="px-3 py-2">
                                    @if ($cell['source'] === 'leave')
                                        @if ($cell['leave_id'])
                                            <div class="flex flex-col gap-1">
                                                <a class="text-primary-600 hover:underline"
                                                   href="{{ url('/principal/teacher-leaves/'.$cell['leave_id'].'/edit') }}">Leave #{{ $cell['leave_id'] }}</a>
                                                <button type="button"
                                                        wire:click="inspectLeave({{ $cell['leave_id'] }})"
                                                        class="text-[10px] font-medium text-indigo-600 hover:text-indigo-800 self-start">
                                                    Inspect →
                                                </button>
                                            </div>
                                        @else
                                            Leave
                                        @endif
                                    @else
                                        @if ($cell['duty_id'])
                                            <a class="text-primary-600 hover:underline"
                                               href="{{ url('/principal/duty-assignments/'.$cell['duty_id'].'/edit') }}">Duty #{{ $cell['duty_id'] }}</a>
                                        @else
                                            Duty
                                        @endif
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[10px] font-semibold {{ $cls }}">
                                        @if ($cell['gap']) GAP @else {{ strtoupper($cell['status']) }} @endif
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif

    {{-- ── Eligibility lookup ───────────────────────────────────────── --}}
    <div id="eligibility-lookup"
         x-data="{}"
         @scroll-to-eligibility.window="$nextTick(() => document.getElementById('eligibility-lookup')?.scrollIntoView({ behavior: 'smooth', block: 'start' }))"
         class="rounded-lg border border-gray-200 bg-white p-4 mt-4">

        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Eligibility lookup</h3>
                <p class="text-xs text-gray-500">Pick a leave to see which substitutes are actually free for each affected period.</p>
            </div>
            @if($eligibilityLeaveId)
                <button type="button" wire:click="clearInspect"
                        class="text-xs font-medium text-indigo-600 hover:text-indigo-800">
                    Clear
                </button>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-2 mb-3">
            <label class="text-xs font-medium text-gray-600">Leave:</label>
            <select wire:model.live="eligibilityLeaveId"
                    class="text-xs border border-gray-300 rounded-md px-2 py-1 min-w-[300px]">
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
            @if($inspectableLeaves->isEmpty())
                <span class="text-xs text-gray-400 italic">No leaves in current window.</span>
            @endif
        </div>

        @if($eligibilityLeaveId && $eligibility)
            <div class="border-t border-gray-100 pt-3">
                @if($inspectedLeave)
                    <div class="text-xs text-gray-700 mb-3">
                        <strong>{{ $inspectedLeave->teacher?->name }}</strong>
                        · {{ \App\Models\LeaveType::labelFor($inspectedLeave->type) }}
                        · {{ optional($inspectedLeave->starts_at)->format('d M') }}–{{ optional($inspectedLeave->ends_at)->format('d M Y') }}
                        @if($inspectedLeave->substitute_teacher_id)
                            · <span class="text-emerald-700">currently covered by {{ $inspectedLeave->substitute?->name }}</span>
                        @endif
                    </div>
                @endif

                @include('filament.principal.teacher.eligibility-grid', [
                    'eligibility' => $eligibility,
                    'showPick'    => false,
                ])
            </div>
        @elseif($eligibilityLeaveId && ! $eligibility)
            <div class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded p-2">
                Could not build eligibility (teacher missing, no published timetable, or window outside school days).
            </div>
        @endif
    </div>
</x-filament-panels::page>
