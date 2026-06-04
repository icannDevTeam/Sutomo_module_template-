@php
    /** @var string $heading */
    /** @var string $subheading */
    /** @var array $actions */
    /** @var array $years */
    /** @var string $currentAy */
    /** @var string $selectedAy */
@endphp

<div class="rounded-2xl border border-indigo-200/70 bg-gradient-to-r from-indigo-50 via-sky-50 to-cyan-50 dark:from-indigo-900/15 dark:via-sky-900/10 dark:to-cyan-900/10 dark:border-indigo-700/40 p-5">
    <div class="flex flex-wrap items-start gap-4">
        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 shrink-0">
            <x-heroicon-o-inbox-stack class="h-6 w-6"/>
        </div>
        <div class="flex-1 min-w-[16rem]">
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $heading }}</h1>
            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $subheading }}</p>
        </div>
        @if (! empty($actions))
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($actions as $action)
                    {{ $action }}
                @endforeach
            </div>
        @endif
    </div>

    {{-- Academic-year CTA strip --}}
    <div class="mt-5">
        <div class="mb-2 flex items-baseline justify-between">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                Academic year · pick to focus the list
            </div>
            <div class="text-[11px] text-gray-500 dark:text-gray-400">
                <span class="inline-block h-2 w-2 rounded-full bg-emerald-500 align-middle"></span> current
                · <span class="inline-block h-2 w-2 rounded-full bg-slate-400 align-middle"></span> past (archivable)
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            {{-- "All years" CTA --}}
            @php $isAll = $selectedAy === 'all'; @endphp
            <button type="button" wire:click="selectAcademicYear('all')"
                @class([
                    'inline-flex items-center gap-2 rounded-xl border px-3.5 py-2 text-sm font-semibold transition',
                    'border-indigo-500 bg-indigo-600 text-white shadow-sm' => $isAll,
                    'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-200 dark:hover:bg-slate-800' => ! $isAll,
                ])>
                <x-heroicon-o-rectangle-stack class="h-4 w-4"/>
                All years
                <span @class([
                    'inline-flex items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-bold',
                    'bg-white/25 text-white' => $isAll,
                    'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200' => ! $isAll,
                ])>{{ collect($years)->sum('total') }}</span>
            </button>

            @foreach ($years as $row)
                @php
                    $year     = $row['year'];
                    $isCurr   = $year === $currentAy;
                    $isActive = $selectedAy === $year;
                    $allArchived = $row['active'] === 0 && $row['total'] > 0;

                    if ($isActive && $isCurr) {
                        $btnCls = 'border-emerald-500 bg-emerald-600 text-white shadow-sm';
                        $countCls = 'bg-white/25 text-white';
                    } elseif ($isActive) {
                        $btnCls = 'border-slate-700 bg-slate-800 text-white shadow-sm';
                        $countCls = 'bg-white/25 text-white';
                    } elseif ($isCurr) {
                        $btnCls = 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 dark:border-emerald-800/40 dark:bg-emerald-900/20 dark:text-emerald-200';
                        $countCls = 'bg-emerald-200/70 text-emerald-900 dark:bg-emerald-800/40 dark:text-emerald-100';
                    } elseif ($allArchived) {
                        $btnCls = 'border-slate-200 bg-slate-100 text-slate-500 hover:bg-slate-200 dark:border-slate-700 dark:bg-slate-800/40 dark:text-slate-400';
                        $countCls = 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300';
                    } else {
                        $btnCls = 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-200 dark:hover:bg-slate-800';
                        $countCls = 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200';
                    }
                @endphp

                <div class="inline-flex items-stretch">
                    <button type="button" wire:click="selectAcademicYear(@js($year))"
                        class="inline-flex items-center gap-2 rounded-l-xl border {{ $btnCls }} {{ $row['total'] > 0 && ! $isCurr ? 'border-r-0' : 'rounded-r-xl' }} px-3.5 py-2 text-sm font-semibold transition">
                        @if ($isCurr)
                            <x-heroicon-o-star class="h-4 w-4"/>
                        @elseif ($allArchived)
                            <x-heroicon-o-archive-box class="h-4 w-4"/>
                        @else
                            <x-heroicon-o-calendar class="h-4 w-4"/>
                        @endif
                        <span>{{ $year }}</span>
                        @if ($isCurr)
                            <span class="rounded-full bg-emerald-500/20 px-1.5 py-px text-[9px] font-bold uppercase tracking-wider text-emerald-700 dark:bg-emerald-400/15 dark:text-emerald-200">
                                Now
                            </span>
                        @endif
                        <span class="inline-flex items-center justify-center rounded-full {{ $countCls }} px-1.5 py-0.5 text-[10px] font-bold">
                            {{ $row['total'] }}
                        </span>
                        @if ($row['pending'] > 0 && ! $allArchived)
                            <span class="inline-flex items-center justify-center rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-500/20 dark:text-amber-200" title="Pending signatures">
                                {{ $row['pending'] }}↗
                            </span>
                        @endif
                    </button>

                    {{-- Archive / unarchive corner action (past years only) --}}
                    @if ($row['total'] > 0 && ! $isCurr)
                        @if ($allArchived)
                            <button type="button"
                                wire:click="unarchiveAcademicYear(@js($year))"
                                wire:confirm="Restore {{ $row['total'] }} letters from {{ $year }}?"
                                title="Restore archived letters from {{ $year }}"
                                class="inline-flex items-center justify-center rounded-r-xl border border-slate-300 bg-white px-2.5 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-400 dark:hover:bg-slate-800">
                                <x-heroicon-o-arrow-uturn-left class="h-4 w-4"/>
                            </button>
                        @else
                            <button type="button"
                                wire:click="archiveAcademicYear(@js($year))"
                                wire:confirm="Archive all {{ $row['active'] }} active letters for {{ $year }}? This hides them from default views."
                                title="Archive all letters for {{ $year }}"
                                class="inline-flex items-center justify-center rounded-r-xl border border-slate-200 bg-white px-2.5 text-slate-500 transition hover:bg-amber-50 hover:text-amber-700 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-400 dark:hover:bg-amber-900/20 dark:hover:text-amber-200">
                                <x-heroicon-o-archive-box class="h-4 w-4"/>
                            </button>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <p class="mt-4 hidden sm:block text-[11px] text-gray-500 dark:text-gray-400">
        <span class="font-medium text-gray-600 dark:text-gray-300">Continuation</span> = active LOIs awaiting signature
        ·
        <span class="font-medium text-gray-600 dark:text-gray-300">Declined / Resigned Pending</span> = needs follow-up
        ·
        <span class="font-medium text-gray-600 dark:text-gray-300">Signed</span> = teacher confirmed
        ·
        <span class="font-medium text-gray-600 dark:text-gray-300">Archived</span> = hidden / past years
    </p>
</div>
