<x-filament-panels::page>
    {{-- Stat strip --}}
    <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
        @foreach ([
            ['Total staff', $total, 'heroicon-o-users', 'primary'],
            ['Permanent', $counts['permanent'] ?? 0, 'heroicon-o-check-circle', 'success'],
            ['Contract', $counts['contract'] ?? 0, 'heroicon-o-document-text', 'info'],
            ['OPL / Probation', ($counts['opl'] ?? 0) + ($counts['probation'] ?? 0), 'heroicon-o-academic-cap', 'warning'],
            ['On leave', $counts['leave'] ?? 0, 'heroicon-o-paper-airplane', 'gray'],
            ['Alumni', $counts['alumni'] ?? 0, 'heroicon-o-clock', 'gray'],
        ] as [$label, $value, $icon, $color])
            <div class="rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-[11px] uppercase text-gray-400 font-semibold tracking-wider">{{ $label }}</div>
                        <div class="text-2xl font-bold text-gray-950 dark:text-white mt-1">{{ $value }}</div>
                    </div>
                    <x-filament::icon :icon="$icon" class="h-7 w-7 text-{{ $color }}-500" />
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filter bar --}}
    <div class="mt-4 rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-3">
        <div class="flex flex-wrap items-center gap-2">
            <div class="relative flex-1 min-w-[220px]">
                <x-filament::icon icon="heroicon-m-magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input wire:model.live.debounce.300ms="q" placeholder="Search name, ID, email, subject…" class="w-full pl-9 rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm" />
            </div>
            <select wire:model.live="dept" class="rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm">
                <option value="all">All departments</option>
                @foreach ($depts as $d) <option value="{{ $d }}">{{ $d }}</option> @endforeach
            </select>
            <select wire:model.live="campus" class="rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm">
                <option value="all">All campuses</option>
                @foreach ($campuses as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
            </select>
            <select wire:model.live="status" class="rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm">
                <option value="all">All statuses</option>
                @foreach (\App\Filament\Pages\MasterDatabase::STATUSES as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
            </select>
            <select wire:model.live="employment" class="rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm">
                <option value="all">All employment</option>
                @foreach (\App\Filament\Pages\MasterDatabase::EMPLOYMENTS as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
            </select>
            <select wire:model.live="sort" class="rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm">
                <option value="name">Sort: Name</option>
                <option value="joined">Sort: Joined</option>
                <option value="rating">Sort: Rating</option>
                <option value="campus">Sort: Campus</option>
            </select>
            <button wire:click="resetFilters" class="text-xs text-gray-500 hover:text-primary-600 px-2">Reset</button>
        </div>
        <div class="text-xs text-gray-400 mt-2">{{ count($list) }} of {{ $total }} staff</div>
    </div>

    {{-- Results --}}
    <div class="mt-4 rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="text-xs uppercase text-gray-400 bg-gray-50 dark:bg-white/5">
                <tr>
                    <th class="text-left px-5 py-3">Staff</th>
                    <th class="text-left">Department</th>
                    <th class="text-left">Subject</th>
                    <th class="text-left">Campus</th>
                    <th class="text-left">Employment</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Rating</th>
                    <th class="text-right px-5">Joined</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($list as $t)
                    @php $initials = collect(explode(' ', $t->name))->map(fn($p)=>mb_substr($p,0,1))->take(2)->implode(''); @endphp
                    <tr class="border-t border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition cursor-pointer"
                        onclick="window.location='{{ \App\Filament\Resources\TeacherResource::getUrl('edit', ['record' => $t]) }}'">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 rounded-full bg-primary-500 text-white flex items-center justify-center text-xs font-bold">{{ $initials }}</div>
                                <div>
                                    <div class="font-semibold text-gray-950 dark:text-white">{{ $t->name }}</div>
                                    <div class="text-xs text-gray-400 font-mono">{{ $t->employee_no ?: $t->code }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-gray-600 dark:text-gray-400">{{ $t->dept ?: '—' }}</td>
                        <td class="text-gray-600 dark:text-gray-400">{{ $t->subject ?: '—' }}</td>
                        <td class="text-gray-500 uppercase text-xs font-mono">{{ $t->campus }}</td>
                        <td class="text-gray-600 dark:text-gray-400 text-xs">{{ \App\Filament\Pages\MasterDatabase::EMPLOYMENTS[$t->employment] ?? $t->employment ?: '—' }}</td>
                        <td class="text-center">
                            <x-filament::badge :color="\App\Filament\Pages\MasterDatabase::STATUS_COLOR[$t->status] ?? 'gray'">
                                {{ \App\Filament\Pages\MasterDatabase::STATUSES[$t->status] ?? $t->status }}
                            </x-filament::badge>
                        </td>
                        <td class="text-center font-semibold">{{ $t->rating ? '★ ' . number_format($t->rating, 1) : '—' }}</td>
                        <td class="text-right text-xs text-gray-500 px-5">{{ $t->joined_at?->format('M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-gray-400 py-10">
                        <div class="text-4xl mb-2">🔍</div>
                        No staff match the current filters.
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
