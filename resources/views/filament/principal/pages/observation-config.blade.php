<x-filament-panels::page>
    <div class="space-y-6">
        <div class="sp-cm-intro">
            Teacher Observation criteria and Probation Watch thresholds are configured separately.
            Keep scoring dimensions here, and use the other tab for continuation rules.
        </div>

        <div class="sp-tabs sp-tabs--with-search">
            <div class="sp-tabs__group">
                <button type="button" wire:click="setTab('teacher-observation')" @class(['sp-tab', 'is-active' => $activeTab === 'teacher-observation'])>
                    <x-heroicon-o-clipboard-document-check class="h-4 w-4" />
                    Teacher Observation
                    <span class="sp-tab__count">{{ count($criteria) }}</span>
                </button>
                <button type="button" wire:click="setTab('probation-watch')" @class(['sp-tab', 'is-active' => $activeTab === 'probation-watch'])>
                    <x-heroicon-o-shield-check class="h-4 w-4" />
                    Probation Watch
                    <span class="sp-tab__count">3</span>
                </button>
            </div>
        </div>

        @if ($activeTab === 'teacher-observation')
            <div class="sp-card">
                <div class="sp-card-h">Teacher Observation Criteria</div>
                <div class="sp-card-sub">Configure the scoring dimensions and keep inactive items in history for older observations.</div>
            </div>

            <div class="space-y-3">
                @forelse($criteria as $i => $row)
                    @php $isActive = (bool) ($row['active'] ?? true); @endphp
                    <div @class(['sp-card', 'is-success' => $isActive, 'is-warning opacity-85' => ! $isActive])>
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                            <div style="min-width:0;flex:1;">
                                <div class="sp-card-h" style="display:flex;align-items:center;gap:.5rem;margin-bottom:.35rem;">
                                    <span class="sp-pill sp-pill-slate">{{ $i + 1 }}</span>
                                    {{ $row['label'] ?: 'Untitled criterion' }}
                                </div>
                                <div class="sp-card-sub" style="display:flex;flex-wrap:wrap;gap:.4rem;align-items:center;">
                                    <span class="sp-pill sp-pill-gray">{{ $row['key'] }}</span>
                                    <span class="sp-pill sp-pill-indigo">Weight {{ $row['weight'] ?? 5 }}</span>
                                    <span class="sp-pill {{ $isActive ? 'sp-pill-green' : 'sp-pill-slate' }}">{{ $isActive ? 'Active' : 'Inactive' }}</span>
                                </div>
                            </div>

                            <div style="display:flex;align-items:center;gap:.35rem;flex-wrap:wrap;">
                                <label class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-xs font-medium text-slate-700 dark:bg-white/5 dark:text-slate-200">
                                    <input type="checkbox" wire:model.live="criteria.{{ $i }}.active" class="size-3.5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:border-white/10 dark:bg-white/5" />
                                    <span>Enabled</span>
                                </label>
                                <button type="button" wire:click="moveUp({{ $i }})" @class(['sp-btn sp-btn-ghost !py-2 !px-3', 'opacity-30 cursor-not-allowed' => $i === 0]) @disabled($i === 0) title="Move up">
                                    <x-heroicon-m-chevron-up class="size-4" />
                                </button>
                                <button type="button" wire:click="moveDown({{ $i }})" @class(['sp-btn sp-btn-ghost !py-2 !px-3', 'opacity-30 cursor-not-allowed' => $i === count($criteria) - 1]) @disabled($i === count($criteria) - 1) title="Move down">
                                    <x-heroicon-m-chevron-down class="size-4" />
                                </button>
                                <button type="button" wire:click="removeRow({{ $i }})" wire:confirm="Remove this criterion? If it's referenced by existing observations it will be deactivated instead." class="sp-btn sp-btn-ghost !py-2 !px-3 text-rose-600 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700 dark:hover:bg-rose-500/10" title="Remove">
                                    <x-heroicon-m-trash class="size-4" />
                                </button>
                            </div>
                        </div>

                        <div class="sp-divider"></div>

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
                            <div class="md:col-span-3">
                                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Key</label>
                                <input type="text" wire:model.live.debounce.500ms="criteria.{{ $i }}.key" placeholder="engagement" class="block w-full rounded-lg border-gray-300 bg-white font-mono text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                                <p class="mt-1 text-[11px] text-gray-400">Slug (a–z, _).</p>
                            </div>
                            <div class="md:col-span-5">
                                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Label</label>
                                <input type="text" wire:model.live.debounce.500ms="criteria.{{ $i }}.label" placeholder="Engagement" class="block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                                <p class="mt-1 text-[11px] text-gray-400">Shown in observation form.</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Weight</label>
                                <input type="number" min="1" max="100" wire:model.defer="criteria.{{ $i }}.weight" class="block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                                <p class="mt-1 text-[11px] text-gray-400">For weighted avg.</p>
                            </div>
                            <div class="md:col-span-12">
                                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Description <span class="text-gray-400">(optional)</span></label>
                                <input type="text" wire:model.defer="criteria.{{ $i }}.description" placeholder="What this criterion measures (e.g. 'Student attention and active participation')" class="block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="sp-card sp-empty">
                        <x-heroicon-o-clipboard-document-list class="mx-auto size-10 text-gray-400" />
                        <h3 class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">No criteria yet</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add at least one dimension observers can score.</p>
                    </div>
                @endforelse

                <button type="button" wire:click="addRow" class="sp-btn sp-btn-ghost w-full justify-center border-dashed">
                    <x-heroicon-m-plus class="size-4" />
                    Add criterion
                </button>
            </div>
        @endif

        @if ($activeTab === 'probation-watch')
            <div class="sp-card">
                <div class="sp-card-h">Probation Watch Thresholds</div>
                <div class="sp-card-sub">These settings control how PKWT-I teachers are flagged for continuation decisions.</div>

                <div class="mt-4 flex items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50/50 px-4 py-3 dark:border-amber-500/30 dark:bg-amber-500/5">
                    <div>
                        <div class="text-sm font-semibold text-gray-900 dark:text-white">Probation Watch</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Enable or disable the entire workflow from here.</div>
                    </div>
                    <label class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-medium text-amber-700 ring-1 ring-amber-200 dark:bg-white/5 dark:text-amber-200 dark:ring-amber-500/20">
                        <input type="checkbox" wire:model.live="probationWatchEnabled" class="rounded border-amber-300 text-amber-600 focus:ring-amber-500" />
                        <span>{{ $probationWatchEnabled ? 'Enabled' : 'Disabled' }}</span>
                    </label>
                </div>

                <div class="sp-grid-3 mt-4">
                    <div class="sp-card" style="padding:1rem;">
                        <div class="sp-card-h">Min principal supervisions</div>
                        <div class="sp-card-sub">Required during the probation window.</div>
                        <input type="number" min="0" max="50" wire:model="probationMinSupervisions" @disabled(! $probationWatchEnabled) class="mt-2 block w-full rounded-lg border-gray-300 text-sm focus:border-amber-500 focus:ring-amber-500 disabled:opacity-50" />
                    </div>
                    <div class="sp-card" style="padding:1rem;">
                        <div class="sp-card-h">Min peer observations</div>
                        <div class="sp-card-sub">Counted using observation_type = peer_observation.</div>
                        <input type="number" min="0" max="50" wire:model="probationMinPeerObservations" @disabled(! $probationWatchEnabled) class="mt-2 block w-full rounded-lg border-gray-300 text-sm focus:border-amber-500 focus:ring-amber-500 disabled:opacity-50" />
                    </div>
                    <div class="sp-card" style="padding:1rem;">
                        <div class="sp-card-h">Decision-due window</div>
                        <div class="sp-card-sub">Flag a teacher as due when this many days remain.</div>
                        <input type="number" min="1" max="90" wire:model="probationDecisionDueDays" @disabled(! $probationWatchEnabled) class="mt-2 block w-full rounded-lg border-gray-300 text-sm focus:border-amber-500 focus:ring-amber-500 disabled:opacity-50" />
                    </div>
                </div>
            </div>
        @endif

        <div class="sticky bottom-0 left-0 right-0 -mx-6 -mb-6 border-t border-gray-200 bg-white/95 px-6 py-3 backdrop-blur dark:border-white/10 dark:bg-gray-900/95">
            <div class="flex items-center justify-between gap-3">
                <p class="hidden text-xs text-gray-500 sm:block dark:text-gray-400">Inactive criteria stay in history but won't appear on new observations.</p>
                <div class="ml-auto flex items-center gap-2">
                    <button type="button" wire:click="mount" wire:loading.attr="disabled" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5">Reset</button>
                    <button type="button" wire:click="save" wire:loading.attr="disabled" class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:opacity-60">
                        <svg wire:loading wire:target="save" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <x-heroicon-m-check wire:loading.remove wire:target="save" class="size-4" />
                        Save changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
