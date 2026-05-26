<x-filament-panels::page>
    @php
        $c = $candidate;
        $initials = $c ? collect(explode(' ', $c->name))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('') : '?';
        $stageLabels = \App\Models\Candidate::STAGES;
        $stage = $c?->stage;
        $doneCount = collect($progress)->where('done', true)->count();
        $totalSteps = count($progress);
    @endphp

    {{-- HEADER --}}
    <x-filament::section>
        <div class="flex items-center gap-4 flex-wrap">
            <div class="w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-500/20 text-primary-700 dark:text-primary-300 flex items-center justify-center text-xl font-bold">
                {{ strtoupper($initials) }}
            </div>
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-bold text-gray-950 dark:text-white">{{ $c?->name }}</h1>
                <div class="text-sm text-gray-500 mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                    <code class="text-xs">{{ $c?->code }}</code>
                    @if ($c?->applied_at)
                        <span class="inline-flex items-center gap-1">
                            <x-filament::icon icon="heroicon-m-calendar" class="w-3.5 h-3.5" />
                            Applied {{ $c->applied_at?->format('d M Y') }}
                        </span>
                    @endif
                    @if ($stage)
                        <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 px-2 py-0.5 text-xs font-semibold">
                            Stage: {{ $stageLabels[$stage] ?? $stage }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="text-right">
                <div class="text-xs uppercase font-bold text-gray-400 tracking-wider">Progress</div>
                <div class="text-2xl font-bold text-gray-950 dark:text-white">{{ $doneCount }}/{{ $totalSteps }}</div>
            </div>
        </div>
    </x-filament::section>

    {{-- PROGRESS CHECKLIST --}}
    <x-filament::section class="mt-4">
        <h2 class="text-base font-semibold text-gray-950 dark:text-white mb-3">OPL Progress</h2>
        <ul class="space-y-2">
            @foreach ($progress as $step)
                <li class="flex items-center gap-3 p-2 rounded-md {{ $step['done'] ? 'bg-emerald-50 dark:bg-emerald-500/5' : 'bg-gray-50 dark:bg-white/5' }}">
                    @if ($step['done'])
                        <x-filament::icon icon="heroicon-o-check-circle" class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                    @else
                        <x-filament::icon icon="heroicon-o-check-circle" class="w-6 h-6 text-gray-300 dark:text-white/20" />
                    @endif
                    <div class="flex-1 text-sm font-medium {{ $step['done'] ? 'text-gray-900 dark:text-white' : 'text-gray-500' }}">{{ $step['label'] }}</div>
                    @if ($step['done'])
                        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide">Done</span>
                    @else
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Pending</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </x-filament::section>

    {{-- OBSERVATIONS --}}
    <x-filament::section class="mt-4">
        <h2 class="text-base font-semibold text-gray-950 dark:text-white mb-3">Observations</h2>
        @if ($observations->isEmpty())
            <div class="text-sm text-gray-400 py-4 text-center">No observations recorded yet.</div>
        @else
            <table class="w-full text-sm">
                <thead class="text-xs uppercase text-gray-500">
                    <tr>
                        <th class="text-left py-2">Date</th>
                        <th class="text-left">Subject</th>
                        <th class="text-left">Score</th>
                        <th class="text-left">Observer</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($observations as $o)
                        @php
                            $score = $o->average_score;
                            $color = $score === null ? 'gray' : ($score >= 4 ? 'success' : ($score >= 3 ? 'warning' : 'danger'));
                        @endphp
                        <tr class="border-t border-gray-100 dark:border-white/5">
                            <td class="py-2">{{ $o->observed_at?->format('d M Y H:i') }}</td>
                            <td class="text-gray-600 dark:text-gray-400">{{ $o->lesson_subject ?? '—' }}</td>
                            <td>
                                @if ($score !== null)
                                    <x-filament::badge :color="$color">{{ number_format($score, 2) }}</x-filament::badge>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="text-gray-600 dark:text-gray-400">{{ $o->observer?->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-filament::section>

    {{-- MENTOR NOTES --}}
    <x-filament::section class="mt-4">
        <h2 class="text-base font-semibold text-gray-950 dark:text-white mb-3">Mentor Notes</h2>
        @if (empty($mentor_notes))
            <div class="text-sm text-gray-400 py-4 text-center">No mentor notes recorded yet.</div>
        @else
            <div class="space-y-2">
                @foreach ($mentor_notes as $note)
                    <div class="rounded-lg bg-gray-50 dark:bg-white/5 p-3 text-sm">
                        @if (is_array($note))
                            <div class="flex items-center justify-between mb-1 text-xs text-gray-500">
                                <span class="font-semibold">{{ $note['who'] ?? 'Mentor' }}</span>
                                <span>{{ $note['when'] ?? '' }}</span>
                            </div>
                            <div class="text-gray-700 dark:text-gray-300">{{ $note['text'] ?? '' }}</div>
                        @else
                            <div class="text-gray-700 dark:text-gray-300">{{ $note }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>

    {{-- PROMOTE BUTTON --}}
    <div class="mt-6 flex justify-center">
        <x-filament::button
            wire:click="promoteToProbation"
            wire:confirm="Promote this candidate to Probation?"
            icon="heroicon-m-arrow-up-circle"
            color="success"
            size="lg"
        >Promote to Probation</x-filament::button>
    </div>
</x-filament-panels::page>
