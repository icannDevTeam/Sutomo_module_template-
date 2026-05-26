@php
    $teaching = (int) ($teaching ?? 0);
    $duty     = (int) ($duty ?? 0);
    $total    = (int) ($total ?? ($teaching + $duty));
    $cap      = max(1, (int) ($cap ?? 30));
    $ratio    = $total / $cap;
    $pct      = (int) round(min(1, $ratio) * 100);
    $hot      = $ratio > 0.85;

    $barColor = $hot
        ? 'bg-rose-500'
        : ($ratio > 0.7 ? 'bg-amber-500' : 'bg-indigo-500');
@endphp

<div class="space-y-3">
    <div class="grid grid-cols-3 gap-3">
        <div class="rounded-lg border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-950/40 p-3">
            <div class="flex items-center gap-2 text-xs font-medium text-indigo-700 dark:text-indigo-300 uppercase tracking-wide">
                <x-heroicon-o-academic-cap class="w-4 h-4" />
                <span>Teaching</span>
            </div>
            <div class="mt-1 text-2xl font-semibold text-indigo-900 dark:text-indigo-100">
                {{ $teaching }}
            </div>
            <div class="text-xs text-indigo-700/70 dark:text-indigo-300/70">hours / week</div>
        </div>

        <div class="rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/40 p-3">
            <div class="flex items-center gap-2 text-xs font-medium text-amber-700 dark:text-amber-300 uppercase tracking-wide">
                <x-heroicon-o-shield-check class="w-4 h-4" />
                <span>Duty</span>
            </div>
            <div class="mt-1 text-2xl font-semibold text-amber-900 dark:text-amber-100">
                {{ $duty }}
            </div>
            <div class="text-xs text-amber-700/70 dark:text-amber-300/70">hours / week</div>
        </div>

        <div @class([
                'rounded-lg border p-3',
                'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40' => ! $hot,
                'border-rose-300 dark:border-rose-700 bg-rose-50 dark:bg-rose-950/40 ring-1 ring-rose-300 dark:ring-rose-700' => $hot,
            ])>
            <div @class([
                    'flex items-center gap-2 text-xs font-medium uppercase tracking-wide',
                    'text-gray-700 dark:text-gray-300' => ! $hot,
                    'text-rose-700 dark:text-rose-300' => $hot,
                ])>
                <x-heroicon-o-chart-bar-square class="w-4 h-4" />
                <span>Total / Cap</span>
            </div>
            <div @class([
                    'mt-1 text-2xl font-semibold',
                    'text-gray-900 dark:text-gray-100' => ! $hot,
                    'text-rose-900 dark:text-rose-100' => $hot,
                ])>
                {{ $total }} / {{ $cap }}
            </div>
            <div class="mt-2 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-2 {{ $barColor }} rounded-full" style="width: {{ $pct }}%"></div>
            </div>
        </div>
    </div>

    @if ($hot)
        <div class="flex items-center gap-2 text-xs text-rose-700 dark:text-rose-300">
            <x-heroicon-o-exclamation-triangle class="w-4 h-4" />
            <span>Workload above 85% of cap — consider rebalancing duties.</span>
        </div>
    @endif
</div>
