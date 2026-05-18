<x-filament-panels::page>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <p class="text-sm text-gray-500">Master teacher records — {{ count($teachers) }} faculty</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <input wire:model.live.debounce.300ms="q" placeholder="Search teacher…" class="rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm" />
            <select wire:model.live="status" class="rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm">
                <option value="all">All statuses</option>
                @foreach (\App\Filament\Pages\MasterDatabase::STATUSES as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
            </select>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-3">
        @forelse ($teachers as $t)
            @php $initials = collect(explode(' ', $t->name))->map(fn($p)=>mb_substr($p,0,1))->take(2)->implode(''); @endphp
            <a href="{{ \App\Filament\Resources\TeacherResource::getUrl('edit', ['record' => $t]) }}"
               class="block rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 hover:ring-primary-300 hover:shadow-md transition p-5">
                <div class="flex items-start gap-3">
                    <div class="h-12 w-12 rounded-full bg-primary-500 text-white flex items-center justify-center font-bold">{{ $initials }}</div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <div class="font-bold text-gray-950 dark:text-white">{{ $t->name }}</div>
                            <x-filament::badge :color="\App\Filament\Pages\MasterDatabase::STATUS_COLOR[$t->status] ?? 'gray'">{{ \App\Filament\Pages\MasterDatabase::STATUSES[$t->status] ?? $t->status }}</x-filament::badge>
                        </div>
                        <div class="text-xs text-gray-500 mt-0.5">{{ $t->code }} · {{ $t->subject }} · {{ strtoupper($t->campus) }}</div>
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            <span class="text-[11px] rounded-full bg-gray-100 dark:bg-white/5 px-2 py-0.5">Joined {{ $t->joined_at?->format('Y') }}</span>
                            @if ($t->rating)
                                <span class="text-[11px] rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 px-2 py-0.5 font-semibold">★ {{ number_format($t->rating, 1) }}</span>
                            @else
                                <span class="text-[11px] rounded-full bg-gray-100 dark:bg-white/5 text-gray-500 px-2 py-0.5">Not yet rated</span>
                            @endif
                        </div>
                    </div>
                    <x-filament::icon icon="heroicon-m-chevron-right" class="h-4 w-4 text-gray-400" />
                </div>
            </a>
        @empty
            <div class="lg:col-span-2 text-center text-gray-400 py-10">No teachers match the search.</div>
        @endforelse
    </div>
</x-filament-panels::page>
