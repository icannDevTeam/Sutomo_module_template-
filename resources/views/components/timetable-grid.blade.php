@props([
    'lessons'    => [],
    'days'       => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
    'periods'    => [],
    'showLabels' => true,
    'compact'    => false,
])

@php
    use Carbon\Carbon;

    $palette = [
        ['bg' => 'bg-indigo-100',  'text' => 'text-indigo-700',  'border' => 'border-indigo-200'],
        ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200'],
        ['bg' => 'bg-amber-100',   'text' => 'text-amber-700',   'border' => 'border-amber-200'],
        ['bg' => 'bg-rose-100',    'text' => 'text-rose-700',    'border' => 'border-rose-200'],
        ['bg' => 'bg-sky-100',     'text' => 'text-sky-700',     'border' => 'border-sky-200'],
        ['bg' => 'bg-violet-100',  'text' => 'text-violet-700',  'border' => 'border-violet-200'],
    ];

    $colorFor = function (?string $subject) use ($palette) {
        if (! $subject) {
            return ['bg' => 'bg-gray-50', 'text' => 'text-gray-400', 'border' => 'border-gray-200'];
        }
        $idx = abs(crc32($subject)) % count($palette);
        return $palette[$idx];
    };

    // Map PHP weekday → English short name used in default $days.
    $dayMap = [
        'Monday'    => ['Mon', 'SENIN'],
        'Tuesday'   => ['Tue', 'SELASA'],
        'Wednesday' => ['Wed', 'RABU'],
        'Thursday'  => ['Thu', 'KAMIS'],
        'Friday'    => ['Fri', 'JUMAT'],
        'Saturday'  => ['Sat', 'SABTU'],
    ];
    $todayCandidates = $dayMap[now()->format('l')] ?? [];

    $currentPeriodNo = null;
    $nowTime = now();
    foreach ($periods as $p) {
        $time = is_array($p) ? ($p['time'] ?? null) : null;
        if (! $time) {
            continue;
        }
        // Accept "HH.MM – HH.MM" or "HH:MM - HH:MM"
        $normalized = str_replace(['.', '–', '—'], [':', '-', '-'], $time);
        if (preg_match('/(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})/', $normalized, $m)) {
            try {
                $start = Carbon::createFromFormat('H:i', $m[1]);
                $end   = Carbon::createFromFormat('H:i', $m[2]);
                $nowHM = Carbon::createFromFormat('H:i', $nowTime->format('H:i'));
                if ($nowHM->between($start, $end)) {
                    $currentPeriodNo = is_array($p) ? ($p['no'] ?? null) : $p;
                    break;
                }
            } catch (\Throwable $e) {
                // ignore parse failures
            }
        }
    }

    $cellFor = function ($day, $periodNo) use ($lessons) {
        if ($lessons instanceof \Illuminate\Support\Collection) {
            $row = $lessons->get($day);
        } elseif (is_array($lessons)) {
            $row = $lessons[$day] ?? null;
        } else {
            $row = null;
        }
        if ($row instanceof \Illuminate\Support\Collection) {
            return $row->get($periodNo);
        }
        if (is_array($row)) {
            return $row[$periodNo] ?? null;
        }
        return null;
    };
@endphp

<div class="overflow-x-auto">
    <table class="min-w-full border-separate border-spacing-1 text-xs">
        @if ($showLabels)
            <thead>
                <tr>
                    <th class="w-20 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-500">Period</th>
                    @foreach ($days as $day)
                        <th class="text-center text-[11px] font-semibold uppercase tracking-wider text-gray-600">
                            {{ $day }}
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody>
            @foreach ($periods as $period)
                @php
                    $periodNo = is_array($period) ? ($period['no'] ?? null) : $period;
                    $periodTime = is_array($period) ? ($period['time'] ?? null) : null;

                    if ($compact) {
                        $rowHasAny = false;
                        foreach ($days as $d) {
                            if ($cellFor($d, $periodNo)) { $rowHasAny = true; break; }
                        }
                        if (! $rowHasAny) { continue; }
                    }
                @endphp
                <tr>
                    @if ($showLabels)
                        <th class="text-left align-top text-[11px] font-semibold text-gray-600">
                            <div>{{ $periodNo }}</div>
                            @if ($periodTime)
                                <div class="text-[10px] font-normal text-gray-400">{{ $periodTime }}</div>
                            @endif
                        </th>
                    @endif
                    @foreach ($days as $day)
                        @php
                            $lesson = $cellFor($day, $periodNo);
                            $subject = $lesson->subject ?? null;
                            $c = $colorFor($subject);
                            $isCurrent = $currentPeriodNo === $periodNo
                                && in_array($day, $todayCandidates, true);
                        @endphp
                        <td class="align-top">
                            <div class="rounded-md border p-2 min-h-[58px] {{ $c['bg'] }} {{ $c['border'] }} {{ $isCurrent ? 'ring-2 ring-primary-500' : '' }}">
                                @if ($lesson)
                                    <div class="font-semibold {{ $c['text'] }} leading-tight">
                                        {{ $lesson->subject }}
                                    </div>
                                    <div class="text-[10px] {{ $c['text'] }} opacity-80">
                                        {{ $lesson->class_code }}
                                    </div>
                                    @if (! empty($lesson->room))
                                        <div class="text-[10px] text-gray-500 mt-0.5">
                                            R. {{ $lesson->room }}
                                        </div>
                                    @endif
                                @else
                                    <div class="text-[10px] text-gray-300 italic">—</div>
                                @endif
                            </div>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
