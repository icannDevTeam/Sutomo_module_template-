<x-filament-panels::page>
    <x-filament::section>
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-lg bg-primary-100 dark:bg-primary-500/20 text-primary-700 dark:text-primary-400 flex items-center justify-center text-xl">🏛</div>
            <div>
                <div class="font-semibold text-base">Yayasan board approval</div>
                <div class="text-xs text-gray-500">Final sign-off before OPL onboarding. {{ count($pending) }} awaiting review.</div>
            </div>
        </div>
    </x-filament::section>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-4">
        <x-filament::section>
            <div class="text-xs uppercase text-gray-400 font-semibold">Pending</div>
            <div class="text-3xl font-bold mt-1">{{ count($pending) }}</div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-xs uppercase text-gray-400 font-semibold">Approved</div>
            <div class="text-3xl font-bold mt-1 text-emerald-600">{{ count($approved) }}</div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-xs uppercase text-gray-400 font-semibold">Rejected</div>
            <div class="text-3xl font-bold mt-1 text-rose-600">{{ count($rejected) }}</div>
        </x-filament::section>
    </div>

    <div class="mt-4 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-200 dark:border-white/10 text-sm font-semibold">Pending approvals</div>
        <table class="w-full text-sm">
            <thead class="text-xs uppercase text-gray-400">
                <tr>
                    <th class="text-left px-5 py-2">Candidate</th>
                    <th class="text-left">Vacancy</th>
                    <th class="text-center">Written</th>
                    <th class="text-center">Interview</th>
                    <th class="text-center">Micro</th>
                    <th class="text-right px-5">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pending as $c)
                    <tr class="border-t border-gray-100 dark:border-white/5">
                        <td class="px-5 py-3">
                            <a href="{{ \App\Filament\Resources\CandidateResource::getUrl('view', ['record' => $c]) }}" class="font-semibold hover:text-primary-600">{{ $c->name }}</a>
                            <div class="text-xs text-gray-400 font-mono">{{ $c->code }}</div>
                        </td>
                        <td class="text-gray-600 dark:text-gray-400">{{ $c->vacancy?->title }}</td>
                        <td class="text-center font-semibold">{{ $c->score_written ?: '—' }}</td>
                        <td class="text-center font-semibold">{{ $c->score_interview ?: '—' }}</td>
                        <td class="text-center font-semibold">{{ $c->score_micro ?: '—' }}</td>
                        <td class="px-5 text-right">
                            <x-filament::button wire:click="approve({{ $c->id }})" wire:confirm="Approve {{ $c->name }} and move to OPL?" size="xs" color="success" icon="heroicon-m-check">Approve</x-filament::button>
                            <x-filament::button wire:click="reject({{ $c->id }})" wire:confirm="Reject {{ $c->name }}?" size="xs" color="danger" icon="heroicon-m-x-mark">Reject</x-filament::button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-gray-400 py-8">No candidates pending Yayasan approval.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if (count($approved) || count($rejected))
        <div class="grid md:grid-cols-2 gap-4 mt-4">
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-200 dark:border-white/10 text-sm font-semibold text-emerald-700 dark:text-emerald-400">Recently approved</div>
                <div class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach ($approved as $c)
                        <div class="px-5 py-2 text-sm flex justify-between"><span class="font-semibold">{{ $c->name }}</span><span class="text-gray-400 text-xs">{{ $c->meta['yayasan']['date'] ?? '' }}</span></div>
                    @endforeach
                </div>
            </div>
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-200 dark:border-white/10 text-sm font-semibold text-rose-700 dark:text-rose-400">Recently rejected</div>
                <div class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach ($rejected as $c)
                        <div class="px-5 py-2 text-sm flex justify-between"><span class="font-semibold">{{ $c->name }}</span><span class="text-gray-400 text-xs">{{ $c->meta['yayasan']['date'] ?? '' }}</span></div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
