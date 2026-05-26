<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header callout --}}
        <div class="rounded-xl border border-primary-100 bg-primary-50/60 p-4 dark:border-primary-500/20 dark:bg-primary-500/5">
            <div class="flex items-start gap-3">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary-100 text-primary-600 dark:bg-primary-500/20 dark:text-primary-300">
                    <x-heroicon-o-clipboard-document-check class="size-5" />
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Observation Criteria</h3>
                    <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-400">
                        Configure the dimensions observers will score (1–5). Toggle a criterion off to retire it without losing history.
                    </p>
                </div>
                <div class="hidden sm:flex flex-col items-end text-xs text-gray-500 dark:text-gray-400">
                    <span class="text-lg font-bold text-gray-700 dark:text-gray-200">{{ collect($criteria)->where('active', true)->count() }}</span>
                    <span>active</span>
                </div>
            </div>
        </div>

        {{-- Criteria list --}}
        <div class="space-y-3">
            @forelse($criteria as $i => $row)
                @php $isActive = (bool) ($row['active'] ?? true); @endphp
                <div @class([
                    'group rounded-xl border bg-white shadow-sm transition hover:shadow-md dark:bg-gray-900',
                    'border-gray-200 dark:border-white/10' => $isActive,
                    'border-gray-200/60 bg-gray-50/50 dark:border-white/5 dark:bg-white/[0.02] opacity-75' => ! $isActive,
                ])>
                    {{-- Row header --}}
                    <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-2.5 dark:border-white/5">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex size-7 shrink-0 items-center justify-center rounded-md bg-gray-100 text-xs font-bold text-gray-600 dark:bg-white/10 dark:text-gray-300">
                                {{ $i + 1 }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $row['label'] ?: 'Untitled criterion' }}
                                </div>
                                <div class="truncate text-xs text-gray-500 dark:text-gray-400">
                                    <code class="rounded bg-gray-100 px-1 py-0.5 text-[10px] dark:bg-white/10">{{ $row['key'] }}</code>
                                    <span class="mx-1 text-gray-300">·</span>
                                    <span>weight {{ $row['weight'] ?? 5 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <label @class([
                                'inline-flex cursor-pointer items-center gap-2 rounded-md px-2 py-1 text-xs font-medium',
                                'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' => $isActive,
                                'bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400' => ! $isActive,
                            ])>
                                <input type="checkbox"
                                    wire:model.live="criteria.{{ $i }}.active"
                                    class="size-3.5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:border-white/10 dark:bg-white/5" />
                                <span>{{ $isActive ? 'Active' : 'Inactive' }}</span>
                            </label>
                            <div class="mx-1 h-5 w-px bg-gray-200 dark:bg-white/10"></div>
                            <button type="button" wire:click="moveUp({{ $i }})"
                                @class(['p-1.5 rounded text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-gray-200', 'opacity-30 cursor-not-allowed' => $i === 0])
                                @disabled($i === 0) title="Move up">
                                <x-heroicon-m-chevron-up class="size-4" />
                            </button>
                            <button type="button" wire:click="moveDown({{ $i }})"
                                @class(['p-1.5 rounded text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-gray-200', 'opacity-30 cursor-not-allowed' => $i === count($criteria) - 1])
                                @disabled($i === count($criteria) - 1) title="Move down">
                                <x-heroicon-m-chevron-down class="size-4" />
                            </button>
                            <button type="button" wire:click="removeRow({{ $i }})"
                                wire:confirm="Remove this criterion? If it's referenced by existing observations it will be deactivated instead."
                                class="rounded p-1.5 text-rose-500 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10"
                                title="Remove">
                                <x-heroicon-m-trash class="size-4" />
                            </button>
                        </div>
                    </div>

                    {{-- Edit fields --}}
                    <div class="grid grid-cols-1 gap-3 px-4 py-3 md:grid-cols-12">
                        <div class="md:col-span-3">
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Key</label>
                            <input type="text"
                                wire:model.live.debounce.500ms="criteria.{{ $i }}.key"
                                placeholder="engagement"
                                class="block w-full rounded-lg border-gray-300 bg-white font-mono text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                            <p class="mt-1 text-[11px] text-gray-400">Slug (a–z, _).</p>
                        </div>
                        <div class="md:col-span-5">
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Label</label>
                            <input type="text"
                                wire:model.live.debounce.500ms="criteria.{{ $i }}.label"
                                placeholder="Engagement"
                                class="block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                            <p class="mt-1 text-[11px] text-gray-400">Shown in observation form.</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Weight</label>
                            <input type="number" min="1" max="100"
                                wire:model.defer="criteria.{{ $i }}.weight"
                                class="block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                            <p class="mt-1 text-[11px] text-gray-400">For weighted avg.</p>
                        </div>
                        <div class="md:col-span-12">
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Description <span class="text-gray-400">(optional)</span></label>
                            <input type="text"
                                wire:model.defer="criteria.{{ $i }}.description"
                                placeholder="What this criterion measures (e.g. 'Student attention and active participation')"
                                class="block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border-2 border-dashed border-gray-300 bg-white p-10 text-center dark:border-white/10 dark:bg-gray-900">
                    <x-heroicon-o-clipboard-document-list class="mx-auto size-10 text-gray-400" />
                    <h3 class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">No criteria yet</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add at least one dimension observers can score.</p>
                </div>
            @endforelse
        </div>

        {{-- Add button --}}
        <button type="button" wire:click="addRow"
            class="flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-300 px-4 py-3 text-sm font-medium text-gray-600 transition hover:border-primary-400 hover:bg-primary-50/50 hover:text-primary-700 dark:border-white/10 dark:text-gray-300 dark:hover:border-primary-500/40 dark:hover:bg-primary-500/5 dark:hover:text-primary-300">
            <x-heroicon-m-plus class="size-4" />
            Add criterion
        </button>
    </div>

    {{-- Sticky save bar --}}
    <div class="sticky bottom-0 left-0 right-0 mt-6 -mx-6 -mb-6 border-t border-gray-200 bg-white/95 px-6 py-3 backdrop-blur dark:border-white/10 dark:bg-gray-900/95">
        <div class="flex items-center justify-between gap-3">
            <p class="hidden text-xs text-gray-500 sm:block dark:text-gray-400">
                Inactive criteria stay in history but won't appear on new observations.
            </p>
            <div class="ml-auto flex items-center gap-2">
                <button type="button" wire:click="mount" wire:loading.attr="disabled"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5">
                    Reset
                </button>
                <button type="button" wire:click="save" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:opacity-60">
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
</x-filament-panels::page>
