<x-filament-panels::page>
    <div class="space-y-4">
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="hidden md:grid grid-cols-12 gap-3 px-4 py-2 border-b border-gray-200 text-xs font-semibold uppercase text-gray-500 dark:border-white/10 dark:text-gray-400">
                <div class="col-span-1"></div>
                <div class="col-span-3">Key</div>
                <div class="col-span-3">Label</div>
                <div class="col-span-2">Weight</div>
                <div class="col-span-1 text-center">Active</div>
                <div class="col-span-2 text-right">Actions</div>
            </div>

            @forelse($criteria as $i => $row)
                <div class="grid grid-cols-12 gap-3 items-start px-4 py-3 border-b border-gray-100 dark:border-white/5">
                    <div class="col-span-1 flex items-center text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5" />
                        </svg>
                    </div>
                    <div class="col-span-12 md:col-span-3">
                        <label class="text-xs font-medium text-gray-500 md:hidden dark:text-gray-400">Key</label>
                        <input type="text"
                            wire:model.defer="criteria.{{ $i }}.key"
                            class="block w-full rounded-md border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                    </div>
                    <div class="col-span-12 md:col-span-3">
                        <label class="text-xs font-medium text-gray-500 md:hidden dark:text-gray-400">Label</label>
                        <input type="text"
                            wire:model.defer="criteria.{{ $i }}.label"
                            class="block w-full rounded-md border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                        <input type="text"
                            wire:model.defer="criteria.{{ $i }}.description"
                            placeholder="Optional description"
                            class="mt-1 block w-full rounded-md border-gray-200 bg-white text-xs text-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-gray-300" />
                    </div>
                    <div class="col-span-6 md:col-span-2">
                        <label class="text-xs font-medium text-gray-500 md:hidden dark:text-gray-400">Weight</label>
                        <input type="number" min="1" max="100"
                            wire:model.defer="criteria.{{ $i }}.weight"
                            class="block w-full rounded-md border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                    </div>
                    <div class="col-span-3 md:col-span-1 flex md:justify-center pt-2">
                        <input type="checkbox"
                            wire:model.defer="criteria.{{ $i }}.active"
                            class="size-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5" />
                    </div>
                    <div class="col-span-3 md:col-span-2 flex items-center justify-end gap-1">
                        <button type="button" wire:click="moveUp({{ $i }})"
                            class="p-1.5 rounded text-gray-500 hover:bg-gray-100 dark:hover:bg-white/10"
                            title="Move up">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                            </svg>
                        </button>
                        <button type="button" wire:click="moveDown({{ $i }})"
                            class="p-1.5 rounded text-gray-500 hover:bg-gray-100 dark:hover:bg-white/10"
                            title="Move down">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <button type="button" wire:click="removeRow({{ $i }})"
                            wire:confirm="Remove this criterion?"
                            class="p-1.5 rounded text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10"
                            title="Remove">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                    No criteria configured. Click "Add criterion" below to start.
                </div>
            @endforelse
        </div>

        <div>
            <button type="button" wire:click="addRow"
                class="inline-flex items-center gap-1.5 rounded-md border border-dashed border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add criterion
            </button>
        </div>
    </div>

    <div class="sticky bottom-0 left-0 right-0 mt-6 -mx-6 -mb-6 border-t border-gray-200 bg-white px-6 py-3 dark:border-white/10 dark:bg-gray-900">
        <div class="flex justify-end">
            <button type="button" wire:click="save"
                class="inline-flex items-center gap-1.5 rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                Save changes
            </button>
        </div>
    </div>
</x-filament-panels::page>
