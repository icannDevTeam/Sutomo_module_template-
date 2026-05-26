@php
    /** @var array<string, array{label:string, avg:?float}> $perCriterion */
    $perCriterion = $perCriterion ?? [];
    $hasAny = collect($perCriterion)->contains(fn ($c) => $c['avg'] !== null);

    $toneFor = function (?float $avg): array {
        if ($avg === null) {
            return ['bar' => 'bg-gray-300 dark:bg-gray-600', 'text' => 'text-gray-500 dark:text-gray-400'];
        }
        if ($avg >= 4) {
            return ['bar' => 'bg-emerald-500', 'text' => 'text-emerald-700 dark:text-emerald-300'];
        }
        if ($avg >= 3) {
            return ['bar' => 'bg-indigo-500', 'text' => 'text-indigo-700 dark:text-indigo-300'];
        }
        if ($avg >= 2) {
            return ['bar' => 'bg-amber-500', 'text' => 'text-amber-700 dark:text-amber-300'];
        }
        return ['bar' => 'bg-rose-500', 'text' => 'text-rose-700 dark:text-rose-300'];
    };
@endphp

@if (! $hasAny)
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <x-heroicon-o-chart-bar class="w-5 h-5" />
        <span>No approved observations yet.</span>
    </div>
@else
    <div class="space-y-2">
        @foreach ($perCriterion as $key => $row)
            @php
                $avg = $row['avg'];
                $pct = $avg !== null ? max(0, min(100, ($avg / 5) * 100)) : 0;
                $tone = $toneFor($avg);
            @endphp
            <div class="flex items-center gap-3">
                <div class="w-40 shrink-0 text-sm text-gray-700 dark:text-gray-300 truncate" title="{{ $row['label'] }}">
                    {{ $row['label'] }}
                </div>
                <div class="flex-1 h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                    @if ($avg !== null)
                        <div class="h-2 {{ $tone['bar'] }} rounded-full" style="width: {{ $pct }}%"></div>
                    @endif
                </div>
                <div class="w-12 shrink-0 text-right text-sm font-medium {{ $tone['text'] }}">
                    @if ($avg === null)
                        <span class="italic text-gray-400 dark:text-gray-500 text-xs">no data</span>
                    @else
                        {{ number_format($avg, 1) }}
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
