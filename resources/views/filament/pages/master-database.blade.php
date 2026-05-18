<x-filament-panels::page>
    {{-- Intro / purpose --}}
    <div style="display:flex;align-items:flex-start;gap:.75rem;padding:.85rem 1rem;border-radius:.75rem;background:linear-gradient(135deg,#eff6ff,#ecfeff);border:1px solid #bfdbfe;color:#1e3a8a;">
        <x-filament::icon icon="heroicon-o-light-bulb" style="width:1.25rem;height:1.25rem;flex:0 0 auto;color:#1d4ed8;" />
        <div style="font-size:.85rem;line-height:1.4;">
            <b>Talent Pool</b> — every CV ever submitted to Sutomo (website, walk-in, referrals, fairs).
            Filter by subject, qualification, years of experience or past schools and
            <b>shortlist or assign candidates directly to a vacancy</b> instead of opening a new one.
        </div>
    </div>

    {{-- Stat strip --}}
    <div class="grid grid-cols-2 md:grid-cols-6 gap-3 mt-4">
        @foreach ([
            ['Total in pool',     $totals['pool'],       'heroicon-o-users',         'primary'],
            ['Shortlisted',       $totals['shortlist'],  'heroicon-o-star',          'warning'],
            ['Assigned to vacancy', $totals['assigned'], 'heroicon-o-briefcase',     'info'],
            ['Unassigned',        $totals['unassigned'], 'heroicon-o-archive-box',   'gray'],
            ['From website',      $totals['website'],    'heroicon-o-globe-alt',     'success'],
            ['Walk-in',           $totals['walkin'],     'heroicon-o-building-office-2', 'gray'],
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
            <div class="relative flex-1 min-w-[260px]">
                <x-filament::icon icon="heroicon-m-magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input wire:model.live.debounce.300ms="q" placeholder="Search name, email, school, education, notes…" class="w-full pl-9 rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm" />
            </div>

            <select wire:model.live="subject" class="rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm">
                <option value="all">All subjects</option>
                @foreach ($subjects as $s) <option value="{{ $s }}">{{ $s }}</option> @endforeach
            </select>

            <select wire:model.live="qualification" class="rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm">
                <option value="all">Any qualification</option>
                @foreach ($qualifications as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
            </select>

            <select wire:model.live="source" class="rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm">
                <option value="all">All sources</option>
                @foreach ($sources as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
            </select>

            <select wire:model.live="campus" class="rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm">
                <option value="all">Any campus</option>
                @foreach ($campuses as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
            </select>

            <select wire:model.live="city" class="rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm">
                <option value="all">Any city</option>
                @foreach ($cities as $c) <option value="{{ $c }}">{{ $c }}</option> @endforeach
            </select>

            <select wire:model.live="availability" class="rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm">
                <option value="all">Any availability</option>
                @foreach ($availabilities as $a) <option value="{{ $a }}">{{ $a }}</option> @endforeach
            </select>

            <select wire:model.live="shortlisted" class="rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm">
                <option value="all">All</option>
                <option value="yes">★ Shortlisted only</option>
                <option value="no">Not shortlisted</option>
            </select>

            <div class="flex items-center gap-1 text-xs text-gray-500">
                <span class="text-gray-400">Years:</span>
                <input type="number" min="0" max="40" wire:model.live.debounce.500ms="minYears" class="w-14 rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 text-xs py-1 text-center" />
                <span>–</span>
                <input type="number" min="0" max="40" wire:model.live.debounce.500ms="maxYears" class="w-14 rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 text-xs py-1 text-center" />
            </div>

            <select wire:model.live="sort" class="rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm">
                <option value="recent">Sort: Recently applied</option>
                <option value="years">Sort: Most experience</option>
                <option value="name">Sort: Name</option>
                <option value="salary">Sort: Desired salary</option>
                <option value="shortlist">Sort: Shortlist first</option>
            </select>

            <button wire:click="resetFilters" class="text-xs text-gray-500 hover:text-primary-600 px-2">Reset</button>
        </div>
        <div class="text-xs text-gray-400 mt-2">{{ count($list) }} of {{ $totals['pool'] }} candidates in pool</div>
    </div>

    {{-- Result cards --}}
    <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-3">
        @forelse ($list as $c)
            @php
                $initials = collect(explode(' ', $c->name))->map(fn($p)=>mb_substr($p,0,1))->take(2)->implode('');
                $sourceColor = ['website'=>'success','walk-in'=>'info','referral'=>'warning','agency'=>'primary','fair'=>'purple'][$c->source] ?? 'gray';
                $editUrl = \App\Filament\Resources\CandidateResource::getUrl('edit', ['record' => $c]);
            @endphp
            <div class="rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-4 flex flex-col gap-3 hover:ring-primary-300 transition">
                <div class="flex items-start gap-3">
                    <div class="h-12 w-12 rounded-full bg-gradient-to-br from-primary-500 to-sky-400 text-white flex items-center justify-center text-sm font-bold flex-shrink-0">
                        {{ $initials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ $editUrl }}" class="font-semibold text-gray-950 dark:text-white truncate hover:text-primary-600">{{ $c->name }}</a>
                            @if ($c->shortlisted)
                                <span title="Shortlisted" style="color:#f59e0b;">★</span>
                            @endif
                            <x-filament::badge :color="$sourceColor" size="xs">{{ $sources[$c->source] ?? $c->source }}</x-filament::badge>
                            @if ($c->vacancy)
                                <x-filament::badge color="info" size="xs">→ {{ $c->vacancy->code }}</x-filament::badge>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500 mt-0.5 font-mono">{{ $c->code }} · {{ $c->city ?: '—' }}{{ $c->age ? ' · ' . $c->age . ' yrs' : '' }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400 mt-1 truncate">{{ $c->education }}</div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <div class="text-[10px] uppercase text-gray-400 font-semibold">Experience</div>
                        <div class="text-lg font-bold text-primary-600">{{ $c->years }}<span class="text-xs text-gray-400">y</span></div>
                    </div>
                </div>

                {{-- Subjects --}}
                @if (is_array($c->subjects) && count($c->subjects))
                    <div class="flex flex-wrap gap-1">
                        @foreach ($c->subjects as $s)
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-primary-50 dark:bg-primary-500/10 text-primary-700 dark:text-primary-300 font-medium">{{ $s }}</span>
                        @endforeach
                    </div>
                @endif

                {{-- Current + past schools --}}
                <div class="text-xs text-gray-600 dark:text-gray-400 space-y-0.5">
                    @if ($c->current_school)
                        <div class="flex items-center gap-1.5">
                            <x-filament::icon icon="heroicon-m-building-library" class="h-3.5 w-3.5 text-gray-400" />
                            <b>Now:</b> {{ $c->current_school }}
                        </div>
                    @endif
                    @if (is_array($c->past_schools) && count($c->past_schools))
                        <div class="flex items-start gap-1.5">
                            <x-filament::icon icon="heroicon-m-clock" class="h-3.5 w-3.5 text-gray-400 mt-0.5" />
                            <span><b>Past:</b> {{ implode(' · ', $c->past_schools) }}</span>
                        </div>
                    @endif
                </div>

                {{-- Meta row --}}
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-gray-500 pt-1 border-t border-gray-100 dark:border-white/5">
                    @if ($c->qualification)
                        <span>📜 {{ $qualifications[$c->qualification] ?? $c->qualification }}</span>
                    @endif
                    @if ($c->availability)
                        <span>📅 Available: <b class="text-gray-700 dark:text-gray-300">{{ $c->availability }}</b></span>
                    @endif
                    @if ($c->desired_salary)
                        <span>💰 Rp {{ number_format($c->desired_salary, 0, ',', '.') }}</span>
                    @endif
                    @if ($c->preferred_campus)
                        <span>🏫 {{ $campuses[$c->preferred_campus] ?? strtoupper($c->preferred_campus) }}</span>
                    @endif
                    @if ($c->cv_url)
                        <a href="{{ $c->cv_url }}" target="_blank" class="text-primary-600 hover:underline">📄 CV</a>
                    @endif
                </div>

                @if ($c->notes)
                    <div class="text-xs text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-500/10 rounded-md px-2 py-1.5 italic">
                        “{{ $c->notes }}”
                    </div>
                @endif

                {{-- Actions --}}
                <div class="flex flex-wrap items-center gap-2 pt-1">
                    <button wire:click="toggleShortlist({{ $c->id }})"
                            class="text-xs font-semibold px-3 py-1.5 rounded-lg transition"
                            style="background:{{ $c->shortlisted ? '#f59e0b' : '#fef3c7' }};color:{{ $c->shortlisted ? '#fff' : '#92400e' }};">
                        {{ $c->shortlisted ? '★ Shortlisted' : '☆ Add to shortlist' }}
                    </button>

                    @if (! $c->vacancy_id && count($vacancies))
                        <form wire:submit.prevent="" class="flex items-center gap-1">
                            <select onchange="if(this.value)@this.call('assignToVacancy', {{ $c->id }}, parseInt(this.value)); this.value='';"
                                    class="text-xs rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 py-1">
                                <option value="">Assign to vacancy…</option>
                                @foreach ($vacancies as $v)
                                    <option value="{{ $v->id }}">{{ $v->code }} — {{ $v->title }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif

                    <a href="{{ $editUrl }}" class="text-xs text-gray-500 hover:text-primary-600 ml-auto inline-flex items-center gap-1">
                        Open profile <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" class="h-3 w-3" />
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center text-gray-400 py-12 rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10">
                <div class="text-5xl mb-3">🔍</div>
                <div class="font-semibold">No candidates match your filters.</div>
                <div class="text-xs mt-1">Try widening the experience range or clearing the subject filter.</div>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
