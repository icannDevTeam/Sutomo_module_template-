@php
    /** @var \App\Models\TeacherCompensation|null $current */
    /** @var \Illuminate\Support\Collection $history */
    $current = $current ?? null;
    $history = $history ?? collect();

    $fmt = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');

    $contractType = null;
    if ($current && $current->teacher) {
        $statusKey = $current->teacher->status;
        $contractType = \App\Models\Teacher::STATUSES[$statusKey] ?? ucfirst((string) $statusKey);
    }

    // history is ordered desc by effective_from. Build chronological pairs for delta.
    $rows = $history->values();
    $deltas = [];
    foreach ($rows as $idx => $row) {
        $prev = $rows[$idx + 1] ?? null; // older row
        if (! $prev) {
            $deltas[$idx] = ['type' => 'new', 'pct' => null];
            continue;
        }
        $prevTotal = (int) $prev->total;
        $curTotal  = (int) $row->total;
        if ($prevTotal <= 0) {
            $deltas[$idx] = ['type' => 'neutral', 'pct' => null];
            continue;
        }
        $pct = (($curTotal - $prevTotal) / $prevTotal) * 100;
        $deltas[$idx] = [
            'type' => $pct > 0.05 ? 'up' : ($pct < -0.05 ? 'down' : 'neutral'),
            'pct'  => $pct,
        ];
    }
@endphp

<div class="space-y-6">
    {{-- Current package card --}}
    @if ($current)
        <div class="overflow-hidden rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
            <div class="bg-gradient-to-r from-indigo-500 to-violet-600 px-6 py-5 text-white">
                <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-indigo-100">
                    <x-heroicon-o-banknotes class="h-4 w-4" />
                    Current package
                </div>
                <div class="mt-2 flex flex-wrap items-baseline gap-2">
                    <span class="text-4xl font-bold tracking-tight">{{ $fmt($current->total) }}</span>
                    <span class="text-sm font-medium text-indigo-100">{{ $current->currency }} / month</span>
                </div>
                <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-indigo-100">
                    <span class="inline-flex items-center gap-1">
                        <x-heroicon-o-calendar-days class="h-4 w-4" />
                        Effective {{ $current->effective_from->format('d M Y') }}
                    </span>
                    @if ($contractType)
                        <span aria-hidden="true">·</span>
                        <span class="inline-flex items-center gap-1">
                            <x-heroicon-o-identification class="h-4 w-4" />
                            {{ $contractType }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="space-y-4 bg-white px-6 py-5 dark:bg-gray-900">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-300 dark:ring-indigo-400/30">
                        <x-heroicon-o-currency-dollar class="h-3.5 w-3.5" />
                        Base
                        <span class="font-bold">{{ $fmt($current->base_salary) }}</span>
                    </span>

                    @if (! empty($current->allowances))
                        @foreach ($current->allowances as $key => $value)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-200 dark:bg-white/5 dark:text-gray-300 dark:ring-white/10">
                                <span class="capitalize">{{ str_replace('_', ' ', (string) $key) }}</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $fmt($value) }}</span>
                            </span>
                        @endforeach
                    @endif
                </div>

                @if ($current->notes)
                    <p class="text-sm italic text-gray-500 dark:text-gray-400">{{ $current->notes }}</p>
                @endif
            </div>
        </div>
    @else
        <div class="rounded-xl bg-gray-50 px-6 py-8 text-center text-sm text-gray-500 ring-1 ring-gray-200 dark:bg-white/5 dark:text-gray-400 dark:ring-white/10">
            <x-heroicon-o-banknotes class="mx-auto mb-2 h-6 w-6 text-gray-400" />
            No compensation record on file.
        </div>
    @endif

    {{-- History timeline --}}
    @if ($history->isNotEmpty())
        <div>
            <h4 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                <x-heroicon-o-clock class="h-4 w-4" />
                Compensation history
            </h4>

            <ul class="relative space-y-3 border-s-2 border-gray-200 ps-5 dark:border-white/10">
                @foreach ($rows as $idx => $h)
                    @php
                        $d = $deltas[$idx];
                        $deltaColor = match ($d['type']) {
                            'up'    => 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-400/30',
                            'down'  => 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-400/30',
                            'new'   => 'bg-indigo-50 text-indigo-700 ring-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-300 dark:ring-indigo-400/30',
                            default => 'bg-gray-100 text-gray-600 ring-gray-200 dark:bg-white/5 dark:text-gray-300 dark:ring-white/10',
                        };
                        $dotColor = match ($d['type']) {
                            'up'    => 'bg-emerald-500',
                            'down'  => 'bg-rose-500',
                            'new'   => 'bg-indigo-500',
                            default => 'bg-gray-400',
                        };
                    @endphp
                    <li class="relative">
                        <span class="absolute -start-[27px] mt-1.5 h-3 w-3 rounded-full {{ $dotColor }} ring-2 ring-white dark:ring-gray-900"></span>
                        <div class="rounded-lg bg-white p-3 ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-white/10">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                        {{ $h->effective_from->format('d M Y') }}
                                    </span>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ $fmt($h->total) }}
                                    </span>
                                </div>
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $deltaColor }}">
                                    @if ($d['type'] === 'up')
                                        <x-heroicon-s-arrow-up class="h-3 w-3" />
                                        {{ number_format(abs($d['pct']), 1) }}%
                                    @elseif ($d['type'] === 'down')
                                        <x-heroicon-s-arrow-down class="h-3 w-3" />
                                        {{ number_format(abs($d['pct']), 1) }}%
                                    @elseif ($d['type'] === 'new')
                                        <x-heroicon-s-sparkles class="h-3 w-3" />
                                        NEW
                                    @else
                                        <x-heroicon-s-minus class="h-3 w-3" />
                                        0%
                                    @endif
                                </span>
                            </div>
                            <div class="mt-1 flex flex-wrap gap-x-3 gap-y-0.5 text-xs text-gray-500 dark:text-gray-400">
                                <span>Base {{ $fmt($h->base_salary) }}</span>
                                @if (! empty($h->allowances))
                                    @foreach ($h->allowances as $k => $v)
                                        <span><span class="capitalize">{{ str_replace('_', ' ', (string) $k) }}</span> {{ $fmt($v) }}</span>
                                    @endforeach
                                @endif
                            </div>
                            @if ($h->notes)
                                <p class="mt-1 text-xs italic text-gray-500 dark:text-gray-400">{{ $h->notes }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
