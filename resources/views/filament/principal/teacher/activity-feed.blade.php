@php
    use Illuminate\Support\Carbon;

    $events = collect($events ?? []);

    // Bucket grouping
    $now = Carbon::now();
    $startOfToday = $now->copy()->startOfDay();
    $startOfYesterday = $now->copy()->subDay()->startOfDay();
    $startOfWeek = $now->copy()->startOfWeek();
    $startOfMonth = $now->copy()->startOfMonth();

    $buckets = [
        'Today'      => collect(),
        'Yesterday'  => collect(),
        'This week'  => collect(),
        'This month' => collect(),
        'Earlier'    => collect(),
    ];

    foreach ($events as $e) {
        $when = Carbon::parse($e['date']);
        if ($when->greaterThanOrEqualTo($startOfToday)) {
            $buckets['Today']->push($e);
        } elseif ($when->greaterThanOrEqualTo($startOfYesterday)) {
            $buckets['Yesterday']->push($e);
        } elseif ($when->greaterThanOrEqualTo($startOfWeek)) {
            $buckets['This week']->push($e);
        } elseif ($when->greaterThanOrEqualTo($startOfMonth)) {
            $buckets['This month']->push($e);
        } else {
            $buckets['Earlier']->push($e);
        }
    }

    $iconFor = function (string $category): array {
        // [heroicon component name, tailwind text color, ring/bg color]
        return match ($category) {
            'leave'       => ['heroicon-o-calendar',            'text-amber-600 bg-amber-50 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-400/30'],
            'observation' => ['heroicon-o-eye',                 'text-indigo-600 bg-indigo-50 ring-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-300 dark:ring-indigo-400/30'],
            'document'    => ['heroicon-o-document',            'text-slate-600 bg-slate-100 ring-slate-200 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-400/30'],
            'employment'  => ['heroicon-o-briefcase',           'text-emerald-600 bg-emerald-50 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-400/30'],
            'duty'        => ['heroicon-o-clipboard',           'text-violet-600 bg-violet-50 ring-violet-200 dark:bg-violet-500/10 dark:text-violet-300 dark:ring-violet-400/30'],
            default       => ['heroicon-o-information-circle',  'text-gray-500 bg-gray-100 ring-gray-200 dark:bg-white/5 dark:text-gray-300 dark:ring-white/10'],
        };
    };
@endphp

<div class="space-y-6">
    @if ($events->isEmpty())
        <div class="rounded-xl bg-gray-50 px-6 py-8 text-center text-sm text-gray-500 ring-1 ring-gray-200 dark:bg-white/5 dark:text-gray-400 dark:ring-white/10">
            <x-heroicon-o-bolt class="mx-auto mb-2 h-6 w-6 text-gray-400" />
            No activity yet
        </div>
    @else
        @foreach ($buckets as $label => $items)
            @continue($items->isEmpty())

            <section>
                <h4 class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-clock class="h-3.5 w-3.5" />
                    {{ $label }}
                    <span class="ms-1 rounded-full bg-gray-100 px-1.5 py-0.5 text-[10px] font-bold text-gray-600 dark:bg-white/10 dark:text-gray-300">
                        {{ $items->count() }}
                    </span>
                </h4>

                <ul class="space-y-2">
                    @foreach ($items as $e)
                        @php
                            [$iconName, $iconClass] = $iconFor($e['category'] ?? 'default');
                            $when = Carbon::parse($e['date']);
                        @endphp
                        <li class="flex gap-3 rounded-lg bg-white p-3 ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-white/10">
                            {{-- Icon --}}
                            <div class="flex-none">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full ring-1 ring-inset {{ $iconClass }}">
                                    <x-dynamic-component :component="$iconName" class="h-4 w-4" />
                                </span>
                            </div>

                            {{-- Middle: title + meta --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $e['verb'] ?? 'Activity' }}
                                    </span>
                                    @if (! empty($e['detail']))
                                        <span class="text-sm text-gray-600 dark:text-gray-300">— {{ $e['detail'] }}</span>
                                    @endif
                                </div>
                                <div class="mt-0.5 flex flex-wrap items-center gap-x-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span title="{{ $when->format('d M Y H:i') }}">{{ $when->diffForHumans() }}</span>
                                    @if (! empty($e['actor']) && $e['actor'] !== '—')
                                        <span aria-hidden="true">·</span>
                                        <span class="inline-flex items-center gap-1">
                                            <x-heroicon-o-user class="h-3 w-3" />
                                            {{ $e['actor'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Right: notes --}}
                            @if (! empty($e['notes']))
                                <div class="hidden max-w-[40%] flex-none border-s border-gray-200 ps-3 text-xs italic text-gray-500 dark:border-white/10 dark:text-gray-400 sm:block">
                                    {{ \Illuminate\Support\Str::limit((string) $e['notes'], 140) }}
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach
    @endif
</div>
