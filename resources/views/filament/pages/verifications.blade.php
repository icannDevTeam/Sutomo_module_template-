<x-filament-panels::page>
    @php
        $tabs = ['deposits' => 'Deposits', 'psycho' => 'Psychological', 'medical' => 'Medical', 'yayasan' => 'Yayasan'];
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
            @if ($activeTab === 'deposits')
                <table class="w-full text-sm">
                    <thead class="text-xs uppercase text-gray-400">
                        <tr><th class="text-left py-2">Candidate</th><th class="text-left">Bank</th><th class="text-right">Amount</th><th class="text-left">Paid</th><th class="text-center">Status</th><th class="text-center">Refund</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($deposits as $d)
                            <tr class="border-t border-gray-100 dark:border-white/5">
                                <td class="py-2 font-semibold">{{ $d->candidate?->name }}</td>
                                <td>{{ $d->bank }}</td>
                                <td class="text-right font-mono">Rp {{ number_format($d->amount, 0, ',', '.') }}</td>
                                <td>{{ $d->paid_at?->format('d M Y') }}</td>
                                <td class="text-center"><x-filament::badge :color="$d->status === 'verified' ? 'success' : 'warning'">{{ $d->status }}</x-filament::badge></td>
                                <td class="text-center">{{ $d->refund_eligible ? '✓' : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-gray-400 py-6">No deposits recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                @php
                    $records = $$activeTab;
                    $key = $activeTab;
                @endphp
                <div class="mb-4 rounded-lg bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 px-4 py-3 text-sm text-amber-800 dark:text-amber-300">
                    🔒 Restricted records — visible to authorized roles only. All views are audit-logged.
                </div>
                <table class="w-full text-sm">
                    <thead class="text-xs uppercase text-gray-400">
                        <tr><th class="text-left py-2">Candidate</th><th class="text-left">Vacancy</th><th class="text-left">Date</th><th class="text-left">Provider</th><th class="text-center">Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $c)
                            @php $d = $c->meta[$key] ?? []; @endphp
                            <tr class="border-t border-gray-100 dark:border-white/5">
                                <td class="py-2 font-semibold">{{ $c->name }}</td>
                                <td class="text-gray-500">{{ $c->vacancy?->title }}</td>
                                <td>{{ $d['date'] ?? '—' }}</td>
                                <td>{{ $d['clinic'] ?? $d['counselor'] ?? $d['committee'] ?? '—' }}</td>
                                <td class="text-center"><x-filament::badge :color="in_array($d['status'] ?? '', ['passed','approved']) ? 'success' : 'warning'">{{ $d['status'] ?? '—' }}</x-filament::badge></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-gray-400 py-6">No records.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-filament-panels::page>
