<x-filament-panels::page>
    @php
        $tabs = ['opl' => 'OPL sessions', 'probation' => 'Probation', 'contracts' => 'Contracts'];
    @endphp

    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
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
            @if ($activeTab === 'opl')
                <table class="w-full text-sm">
                    <thead class="text-xs uppercase text-gray-400"><tr><th class="text-left py-2">Candidate</th><th class="text-left">Vacancy</th><th class="text-left">OPL date</th><th class="text-center">Status</th></tr></thead>
                    <tbody>
                        @forelse ($opl as $c)
                            <tr class="border-t border-gray-100 dark:border-white/5">
                                <td class="py-2 font-semibold">{{ $c->name }}</td>
                                <td class="text-gray-500">{{ $c->vacancy?->title }}</td>
                                <td>{{ $c->meta['opl']['date'] ?? '—' }}</td>
                                <td class="text-center"><x-filament::badge color="warning">In OPL</x-filament::badge></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-gray-400 py-6">Nobody currently in OPL.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif ($activeTab === 'probation')
                <table class="w-full text-sm">
                    <thead class="text-xs uppercase text-gray-400"><tr><th class="text-left py-2">Teacher</th><th class="text-left">Subject</th><th class="text-left">Joined</th><th class="text-left">Probation ends</th><th class="text-center">Status</th></tr></thead>
                    <tbody>
                        @forelse ($probation as $t)
                            <tr class="border-t border-gray-100 dark:border-white/5">
                                <td class="py-2 font-semibold">{{ $t->name }}</td>
                                <td class="text-gray-500">{{ $t->subject }}</td>
                                <td>{{ $t->joined_at?->format('d M Y') }}</td>
                                <td>{{ $t->contract_end?->format('d M Y') }}</td>
                                <td class="text-center"><x-filament::badge color="warning">Probation</x-filament::badge></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-gray-400 py-6">No teachers on probation.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <table class="w-full text-sm">
                    <thead class="text-xs uppercase text-gray-400"><tr><th class="text-left py-2">Teacher</th><th class="text-left">Subject</th><th class="text-left">Joined</th><th class="text-left">Contract ends</th><th class="text-center">Status</th></tr></thead>
                    <tbody>
                        @forelse ($contracts as $t)
                            <tr class="border-t border-gray-100 dark:border-white/5">
                                <td class="py-2 font-semibold">{{ $t->name }}</td>
                                <td class="text-gray-500">{{ $t->subject }}</td>
                                <td>{{ $t->joined_at?->format('d M Y') }}</td>
                                <td>{{ $t->contract_end?->format('d M Y') }}</td>
                                <td class="text-center"><x-filament::badge color="info">Contract</x-filament::badge></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-gray-400 py-6">No contract teachers.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-filament-panels::page>
