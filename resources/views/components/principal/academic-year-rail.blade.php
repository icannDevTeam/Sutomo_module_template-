@props([
    'years' => [],
    'selectedAy' => null,
    'currentAy' => null,
    'selectAction' => 'selectAcademicYear',
    'showAll' => true,
    'allKey' => 'all',
    'allLabel' => 'All years',
    'archiveAction' => null,
    'unarchiveAction' => null,
    'focusPrefix' => 'focused on',
    'showStatusLegend' => true,
])

@php
    $rows = collect($years)->map(function ($row) {
        $total = (int) ($row['total'] ?? 0);
        $active = (int) ($row['active'] ?? $total);
        $pending = (int) ($row['pending'] ?? 0);

        return [
            'year' => (string) ($row['year'] ?? ''),
            'total' => $total,
            'active' => $active,
            'pending' => $pending,
        ];
    })->filter(fn ($row) => $row['year'] !== '')->values();

    $totalAll = $rows->sum('total');
    $totalActive = $rows->sum('active');
    $totalArchived = max(0, $totalAll - $totalActive);
    $selectedAy = (string) ($selectedAy ?? '');
    $currentAy = (string) ($currentAy ?? '');
@endphp

<div class="mt-5">
    <div class="mb-2.5 flex items-center justify-between gap-3 flex-wrap">
        <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500 dark:text-slate-400">
            <x-heroicon-o-calendar-days class="h-3.5 w-3.5"/>
            Academic year
            <span class="text-slate-300 dark:text-slate-600">.</span>
            <span class="normal-case tracking-normal font-medium text-slate-500 dark:text-slate-400">
                {{ $selectedAy === $allKey ? 'showing all years' : $focusPrefix.' '.$selectedAy }}
            </span>
        </div>
        @if ($showStatusLegend)
            <div class="flex items-center gap-3 text-[10px] font-medium text-slate-500 dark:text-slate-400">
                <span class="inline-flex items-center gap-1">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span> active
                </span>
                <span class="inline-flex items-center gap-1">
                    <span class="h-2 w-2 rounded-full bg-amber-400"></span> pending
                </span>
                <span class="inline-flex items-center gap-1">
                    <span class="h-2 w-2 rounded-full bg-slate-300"></span> archived
                </span>
            </div>
        @endif
    </div>

    <div class="sp-ay-rail">
        @if ($showAll)
            @php $isAll = $selectedAy === $allKey; @endphp
            <div class="sp-ay-card is-all tone-indigo {{ $isAll ? 'is-active' : '' }}">
                <button type="button" wire:click="{{ $selectAction }}('{{ $allKey }}')" class="sp-ay-btn">
                    <span class="sp-ay-head">
                        <x-heroicon-o-rectangle-stack/>
                        {{ $allLabel }}
                    </span>
                    <span class="sp-ay-year">{{ $totalAll }} <span class="text-[11px] font-medium text-slate-500">items</span></span>
                    <span class="sp-ay-meta">
                        <span class="sp-ay-count">{{ $rows->count() }}</span> years
                        @if ($totalArchived > 0)
                            <span class="sp-ay-pip" title="Archived">
                                <x-heroicon-m-archive-box class="h-3 w-3"/>{{ $totalArchived }}
                            </span>
                        @endif
                    </span>
                </button>
            </div>
        @endif

        @foreach ($rows as $row)
            @php
                $year = $row['year'];
                $isCurr = $year === $currentAy;
                $isSelected = $selectedAy === $year;
                $archivedCnt = max(0, $row['total'] - $row['active']);
                $allArchived = $row['active'] === 0 && $row['total'] > 0;
                $activePct = $row['total'] > 0 ? (int) round(($row['active'] / $row['total']) * 100) : 0;

                $tone = $isCurr ? 'tone-emerald' : ($allArchived ? 'tone-stone' : 'tone-slate');
                $cls = ['sp-ay-card', $tone];
                if ($isSelected) {
                    $cls[] = 'is-active';
                }
                if ($allArchived) {
                    $cls[] = 'is-archived';
                }
            @endphp

            <div class="{{ implode(' ', $cls) }}">
                <button type="button" wire:click="{{ $selectAction }}(@js($year))" class="sp-ay-btn">
                    <span class="sp-ay-head">
                        @if ($isCurr)
                            <x-heroicon-m-star class="h-3.5 w-3.5"/> Current
                        @elseif ($allArchived)
                            <x-heroicon-m-archive-box class="h-3.5 w-3.5"/> Archived
                        @else
                            <x-heroicon-m-calendar class="h-3.5 w-3.5"/> Past
                        @endif
                    </span>
                    <span class="sp-ay-year">{{ $year }}</span>
                    <span class="sp-ay-meta">
                        <span class="sp-ay-count" title="Total">{{ $row['total'] }}</span>
                        @if ($row['pending'] > 0 && ! $allArchived)
                            <span class="sp-ay-pip" title="Pending">
                                <x-heroicon-m-clock class="h-3 w-3"/>{{ $row['pending'] }}
                            </span>
                        @endif
                        @if ($archivedCnt > 0 && ! $allArchived)
                            <span class="sp-ay-pip" style="background:rgba(100,116,139,.18); color:#475569;" title="Archived">
                                <x-heroicon-m-archive-box class="h-3 w-3"/>{{ $archivedCnt }}
                            </span>
                        @endif
                    </span>
                    @if ($row['total'] > 0)
                        <span class="sp-ay-bar" title="{{ $activePct }}% active">
                            <i style="width: {{ $activePct }}%"></i>
                        </span>
                    @endif
                </button>

                @if ($archiveAction && $unarchiveAction && $row['total'] > 0 && ! $isCurr)
                    @if ($allArchived)
                        <button type="button"
                            wire:click="{{ $unarchiveAction }}(@js($year))"
                            wire:confirm="Restore {{ $row['total'] }} items from {{ $year }}?"
                            title="Restore {{ $year }}"
                            class="sp-ay-corner">
                            <x-heroicon-m-arrow-uturn-left/>
                        </button>
                    @else
                        <button type="button"
                            wire:click="{{ $archiveAction }}(@js($year))"
                            wire:confirm="Archive all {{ $row['active'] }} active items for {{ $year }}?"
                            title="Archive {{ $year }}"
                            class="sp-ay-corner">
                            <x-heroicon-m-archive-box/>
                        </button>
                    @endif
                @endif
            </div>
        @endforeach
    </div>
</div>
