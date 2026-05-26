<x-filament-panels::page>
    {{-- Tabs --}}
    <div class="flex items-center justify-between gap-3 flex-wrap">
        <div class="flex gap-1">
            <button type="button" wire:click="setTab('compare')" @class([
                'px-3 py-2 text-sm rounded-md border transition',
                'bg-primary-600 text-white border-primary-600' => $activeTab === 'compare',
                'bg-white text-gray-700 border-gray-200 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-300 dark:border-white/10 dark:hover:bg-white/5' => $activeTab !== 'compare',
            ])>3-up Compare</button>
            <button type="button" wire:click="setTab('leaderboard')" @class([
                'px-3 py-2 text-sm rounded-md border transition',
                'bg-primary-600 text-white border-primary-600' => $activeTab === 'leaderboard',
                'bg-white text-gray-700 border-gray-200 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-300 dark:border-white/10 dark:hover:bg-white/5' => $activeTab !== 'leaderboard',
            ])>Leaderboard</button>
        </div>

        @if ($activeTab === 'leaderboard')
            <x-filament::button
                wire:click="exportLeaderboard"
                icon="heroicon-m-arrow-down-tray"
                color="gray"
                size="sm"
            >Export CSV</x-filament::button>
        @endif
    </div>

    @if ($activeTab === 'compare')
        <form wire:submit.prevent>
            {{ $this->form }}
        </form>

        @if($cards->isEmpty())
            <div class="sp-compare-empty">Pick up to 3 teachers above to compare.</div>
        @else
            <div class="sp-compare-grid">
                @foreach($cards as $c)
                    @php $t = $c['teacher']; @endphp
                    <div class="sp-compare-card">
                        <div class="sp-compare-card__head">
                            @if($t->avatar_url)
                                <img class="sp-compare-card__avatar" src="{{ $t->avatar_url }}" alt="">
                            @else
                                <div class="sp-compare-card__avatar sp-compare-card__avatar--init">{{ strtoupper(substr($t->name, 0, 2)) }}</div>
                            @endif
                            <div>
                                <div class="sp-compare-card__name">{{ $t->name }}</div>
                                <div class="sp-compare-card__meta">{{ $t->title ? (\App\Models\Teacher::TITLES[$t->title] ?? $t->title) : '—' }} · {{ $t->dept ?? '—' }}</div>
                            </div>
                        </div>
                        <dl class="sp-compare-stats">
                            <div><dt>Subject</dt><dd>{{ $t->subject ?? '—' }}</dd></div>
                            <div><dt>Campus</dt><dd>{{ strtoupper($t->campus ?? '—') }}</dd></div>
                            <div><dt>Status</dt><dd>{{ $t->status ?? '—' }}</dd></div>
                            <div><dt>Homeroom</dt><dd>{{ $c['homeroom_count'] }}</dd></div>
                            <div><dt>Attendance (mo)</dt><dd>{{ $c['attendance'] !== null ? $c['attendance'].'%' : '—' }}</dd></div>
                            <div><dt>Certs</dt><dd>{{ $c['cert_count'] }}</dd></div>
                            <div><dt>Observations</dt><dd>{{ $c['obs_count'] }}</dd></div>
                            <div><dt>Mentor</dt><dd>{{ $t->mentor?->name ?? '—' }}</dd></div>
                        </dl>
                    </div>
                @endforeach
            </div>
        @endif
    @else
        {{-- LEADERBOARD --}}
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-4 space-y-4">
            {{-- Filters --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Campus</label>
                    <select wire:model.live="lbCampus" class="w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm">
                        <option value="">All</option>
                        @foreach ($campusOptions as $v => $l)
                            <option value="{{ $v }}">{{ strtoupper($l) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Department</label>
                    <select wire:model.live="lbDept" class="w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm">
                        <option value="">All</option>
                        @foreach ($deptOptions as $v => $l)
                            <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Status</label>
                    <select wire:model.live="lbStatus" class="w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm">
                        <option value="">All</option>
                        @foreach ($statusOptions as $v => $l)
                            <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Criteria checkboxes --}}
            <div>
                <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Criteria</div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($allCriteria as $key)
                        @php $on = in_array($key, $lbCriteria, true); @endphp
                        <button type="button" wire:click="toggleCriterion('{{ $key }}')"
                            @class([
                                'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border transition',
                                'bg-primary-50 text-primary-700 border-primary-200 dark:bg-primary-500/10 dark:text-primary-300 dark:border-primary-500/30' => $on,
                                'bg-gray-50 text-gray-500 border-gray-200 dark:bg-white/5 dark:text-gray-400 dark:border-white/10' => ! $on,
                            ])>
                            @if ($on)
                                <x-filament::icon icon="heroicon-m-check" class="w-3.5 h-3.5" />
                            @endif
                            {{ $criteriaLabels[$key] ?? $key }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="mt-4 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-xs uppercase text-gray-500 bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="text-left py-2 px-3 w-12">Rank</th>
                            <th class="text-left py-2 px-3">Teacher</th>
                            <th class="text-left py-2 px-3">Dept</th>
                            <th class="text-left py-2 px-3 w-20">Total</th>
                            <th class="text-left py-2 px-3">Scores</th>
                            <th class="text-left py-2 px-3 w-24">Badge</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leaderboard as $row)
                            @php
                                $t = $row['teacher'];
                                $initials = strtoupper(mb_substr($t->name ?? '?', 0, 2));
                            @endphp
                            <tr class="border-t border-gray-100 dark:border-white/5 align-middle">
                                <td class="py-2 px-3 font-bold text-gray-700 dark:text-gray-300">#{{ $row['rank'] }}</td>
                                <td class="py-2 px-3">
                                    <div class="flex items-center gap-2">
                                        @if (! empty($t->avatar_url))
                                            <img src="{{ $t->avatar_url }}" alt="" class="w-8 h-8 rounded-full object-cover">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-500/20 text-primary-700 dark:text-primary-300 flex items-center justify-center text-xs font-bold">{{ $initials }}</div>
                                        @endif
                                        <div>
                                            <div class="font-semibold text-gray-900 dark:text-white">{{ $t->name }}</div>
                                            <div class="text-xs text-gray-500">{{ strtoupper($t->campus ?? '—') }} · {{ $t->status ?? '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2 px-3 text-gray-600 dark:text-gray-400">{{ $t->dept ?? '—' }}</td>
                                <td class="py-2 px-3">
                                    <div class="text-2xl font-bold text-gray-950 dark:text-white leading-none">{{ $row['total'] }}</div>
                                    <div class="text-xs text-gray-400">/ 100</div>
                                </td>
                                <td class="py-2 px-3">
                                    <div class="space-y-1 min-w-[220px]">
                                        @foreach ($lbCriteria as $key)
                                            @php
                                                $s = (int) ($row['scores'][$key] ?? 0);
                                                $color = $s >= 85 ? 'bg-emerald-500'
                                                       : ($s >= 70 ? 'bg-indigo-500'
                                                       : ($s >= 50 ? 'bg-amber-500' : 'bg-rose-500'));
                                            @endphp
                                            <div class="flex items-center gap-2">
                                                <div class="w-24 text-xs text-gray-500 truncate">{{ $criteriaLabels[$key] ?? $key }}</div>
                                                <div class="flex-1 h-2 bg-gray-100 dark:bg-white/10 rounded overflow-hidden">
                                                    <div class="h-2 {{ $color }} rounded" style="width: {{ max(0, min(100, $s)) }}%"></div>
                                                </div>
                                                <div class="w-8 text-right text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $s }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-2 px-3">
                                    @if ($row['badge'] === 'top')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300 font-semibold">
                                            <x-filament::icon icon="heroicon-m-trophy" class="w-3.5 h-3.5" />
                                            Top
                                        </span>
                                    @elseif ($row['badge'] === 'support')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300 font-semibold">
                                            <x-filament::icon icon="heroicon-m-lifebuoy" class="w-3.5 h-3.5" />
                                            Support
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-gray-400 py-8">No teachers match the current filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-filament-panels::page>
