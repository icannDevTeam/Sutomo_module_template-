<x-filament-panels::page>
    <x-filament::section>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Drag candidates between columns to advance them through the hiring funnel.
                    Every move writes to the audit log.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Filter by vacancy</label>
                <select
                    wire:model.live="vacancyFilter"
                    class="fi-select-input block w-64 rounded-lg border-gray-300 bg-white text-sm text-gray-950 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white"
                >
                    <option value="">All vacancies</option>
                    @foreach ($this->getVacancies() as $id => $title)
                        <option value="{{ $id }}">{{ $title }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-filament::section>

    <div
        x-data="{
            dragId: null,
            start(e, id) { this.dragId = id; e.dataTransfer.effectAllowed = 'move'; e.target.classList.add('opacity-50'); },
            end(e)       { e.target.classList.remove('opacity-50'); this.dragId = null; },
            over(e)      { e.preventDefault(); e.currentTarget.classList.add('ring-2','ring-primary-500','bg-primary-50/40'); },
            leave(e)     { e.currentTarget.classList.remove('ring-2','ring-primary-500','bg-primary-50/40'); },
            drop(e, stage) {
                e.preventDefault();
                e.currentTarget.classList.remove('ring-2','ring-primary-500','bg-primary-50/40');
                if (this.dragId) { $wire.moveCandidate(this.dragId, stage); }
            }
        }"
        class="mt-4 overflow-x-auto pb-4"
    >
        <div class="flex gap-4 min-w-max">
            @foreach ($this->getColumns() as $stage => $meta)
                @php $cards = $this->getCandidatesByStage()[$stage] ?? collect(); @endphp
                <div
                    @dragover="over($event)"
                    @dragleave="leave($event)"
                    @drop="drop($event, '{{ $stage }}')"
                    class="flex w-72 shrink-0 flex-col rounded-xl border border-gray-200 bg-gray-50/60 dark:border-white/10 dark:bg-white/5 transition"
                >
                    <div class="flex items-center justify-between px-3 py-2.5 border-b border-gray-200 dark:border-white/10">
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-2.5 h-2.5 rounded-full" style="background:{{ $meta['hex'] }}"></span>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $meta['label'] }}</h3>
                        </div>
                        <span class="inline-flex items-center justify-center min-w-[1.5rem] h-6 px-2 rounded-full text-xs font-semibold bg-white text-gray-700 ring-1 ring-gray-200 dark:bg-white/10 dark:text-white dark:ring-white/10">
                            {{ $cards->count() }}
                        </span>
                    </div>

                    <div class="flex-1 p-2 space-y-2 min-h-[200px]">
                        @forelse ($cards as $c)
                            <div
                                draggable="true"
                                @dragstart="start($event, {{ $c->id }})"
                                @dragend="end($event)"
                                @click.stop="window.location='{{ \App\Filament\Resources\CandidateResource::getUrl('view', ['record' => $c]) }}'"
                                class="group cursor-pointer rounded-lg bg-white p-3 shadow-sm ring-1 ring-gray-200 hover:shadow-md hover:ring-primary-300 transition dark:bg-gray-900 dark:ring-white/10"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold text-white"
                                             style="background:{{ $meta['hex'] }}">
                                            {{ collect(explode(' ', $c->name))->map(fn($p)=>mb_substr($p,0,1))->take(2)->implode('') }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $c->name }}</p>
                                            <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $c->code }}</p>
                                        </div>
                                    </div>
                                    @if ($c->priority === 'high')
                                        <span class="shrink-0 inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-semibold text-rose-700 dark:bg-rose-500/10 dark:text-rose-400">HIGH</span>
                                    @endif
                                </div>

                                @if ($c->vacancy)
                                    <p class="mt-2 text-xs text-gray-600 dark:text-gray-300 truncate">
                                        <x-filament::icon icon="heroicon-m-briefcase" class="inline h-3.5 w-3.5 text-gray-400" />
                                        {{ $c->vacancy->title }}
                                    </p>
                                @endif

                                <div class="mt-2 flex flex-wrap items-center gap-1">
                                    @foreach (array_slice($c->subjects ?? [], 0, 2) as $subj)
                                        <span class="inline-flex items-center rounded-full bg-primary-50 px-2 py-0.5 text-[10px] font-medium text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">{{ $subj }}</span>
                                    @endforeach
                                    @if ($c->deposit && $c->deposit->status === 'verified')
                                        <span class="inline-flex items-center gap-0.5 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">✓ Deposit</span>
                                    @endif
                                </div>

                                <div class="mt-2.5 flex items-center gap-1.5 text-[10px] font-medium">
                                    @if ($c->score_written)
                                        <span class="inline-flex items-center rounded bg-sky-50 px-1.5 py-0.5 text-sky-700 ring-1 ring-inset ring-sky-200 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-500/30">W {{ $c->score_written }}</span>
                                    @endif
                                    @if ($c->score_interview)
                                        <span class="inline-flex items-center rounded bg-amber-50 px-1.5 py-0.5 text-amber-700 ring-1 ring-inset ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30">I {{ $c->score_interview }}</span>
                                    @endif
                                    @if ($c->score_micro)
                                        <span class="inline-flex items-center rounded bg-emerald-50 px-1.5 py-0.5 text-emerald-700 ring-1 ring-inset ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/30">μ {{ $c->score_micro }}</span>
                                    @endif
                                    <span class="ml-auto text-gray-400 dark:text-gray-500">
                                        {{ $c->applied_at?->format('d M') }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="flex h-24 items-center justify-center text-xs text-gray-400 dark:text-gray-600">
                                Drop here
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
