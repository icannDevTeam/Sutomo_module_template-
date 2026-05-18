<x-filament-panels::page>
    @php
        $tabs = ['tests' => 'Written tests', 'interviews' => 'Interviews'];
    @endphp

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @foreach ([
            ['Tests scheduled', $scheduled, 'heroicon-o-calendar', 'primary'],
            ['Awaiting score', $awaiting, 'heroicon-o-clock', 'warning'],
            ['Pass rate', $passRate . '%', 'heroicon-o-check-badge', 'success'],
            ['Average score', $avg, 'heroicon-o-chart-bar', 'info'],
        ] as [$label, $value, $icon, $color])
            <x-filament::section>
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs uppercase text-gray-400 font-semibold">{{ $label }}</div>
                        <div class="text-2xl font-bold text-gray-950 dark:text-white mt-1">{{ $value }}</div>
                    </div>
                    <x-filament::icon :icon="$icon" class="h-8 w-8 text-{{ $color }}-500" />
                </div>
            </x-filament::section>
        @endforeach
    </div>

    {{-- Tabs --}}
    <div class="mt-4 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
        <div class="flex gap-1 px-3 border-b border-gray-200 dark:border-white/10">
            @foreach ($tabs as $id => $label)
                <button wire:click="setActiveTab('{{ $id }}')" @class([
                    'px-3 py-3 text-sm border-b-2 -mb-px',
                    'border-primary-600 text-primary-700 dark:text-primary-400 font-semibold' => $activeTab === $id,
                    'border-transparent text-gray-500 hover:text-gray-900 dark:hover:text-white' => $activeTab !== $id,
                ])>{{ $label }}</button>
            @endforeach
        </div>
        <div class="p-5">
            @if ($activeTab === 'tests')
                <table class="w-full text-sm">
                    <thead class="text-xs uppercase text-gray-400">
                        <tr><th class="text-left py-2">Candidate</th><th class="text-left">Vacancy</th><th class="text-center">Score</th><th class="text-center">Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($writtenCands as $c)
                            <tr class="border-t border-gray-100 dark:border-white/5">
                                <td class="py-2 font-semibold">{{ $c->name }} <span class="font-mono text-xs text-gray-400">{{ $c->code }}</span></td>
                                <td class="text-gray-600 dark:text-gray-400">{{ $c->vacancy?->title }}</td>
                                <td class="text-center font-bold {{ $c->score_written >= 70 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $c->score_written }}</td>
                                <td class="text-center">
                                    <x-filament::badge :color="$c->score_written >= 70 ? 'success' : 'danger'">{{ $c->score_written >= 70 ? 'Pass' : 'Fail' }}</x-filament::badge>
                                </td>
                                <td class="text-right">
                                    <a href="{{ \App\Filament\Resources\CandidateResource::getUrl('view', ['record' => $c]) }}" class="text-xs text-primary-600 hover:underline">Open →</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-gray-400 py-6">No written tests scored yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <table class="w-full text-sm">
                    <thead class="text-xs uppercase text-gray-400">
                        <tr><th class="text-left py-2">Date</th><th class="text-left">Candidate</th><th class="text-left">Type</th><th class="text-left">Room</th><th class="text-center">Status</th><th class="text-center">Recommendation</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($interviews as $iv)
                            <tr class="border-t border-gray-100 dark:border-white/5">
                                <td class="py-2">{{ $iv->scheduled_date?->format('d M Y') }} {{ $iv->scheduled_time }}</td>
                                <td class="font-semibold">{{ $iv->candidate?->name }}</td>
                                <td>{{ $iv->type }}</td>
                                <td class="text-gray-500">{{ $iv->room }}</td>
                                <td class="text-center"><x-filament::badge :color="$iv->status === 'completed' ? 'success' : 'warning'">{{ $iv->status }}</x-filament::badge></td>
                                <td class="text-center">{{ $iv->recommendation ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-gray-400 py-6">No interviews scheduled.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-filament-panels::page>
