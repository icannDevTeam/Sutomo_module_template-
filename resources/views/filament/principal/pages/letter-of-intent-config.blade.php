<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-2xl border border-amber-200/70 bg-gradient-to-r from-amber-50 via-yellow-50 to-orange-50 dark:from-amber-900/15 dark:via-yellow-900/10 dark:to-orange-900/10 dark:border-amber-700/40 p-5">
            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-500/15 text-amber-700 dark:text-amber-300">
                    <x-heroicon-o-cog-6-tooth class="h-6 w-6"/>
                </div>
                <div class="flex-1">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Letter of Intent — Templates &amp; Schedules
                    </h2>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                        Draft reusable letter bodies, then set up automatic sends — yearly for annual re-commitment,
                        monthly for rolling reminders, or one-off for ad-hoc batches. Saved schedules will compute
                        their next run time automatically.
                    </p>
                </div>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <div class="sticky bottom-0 z-10 -mx-4 sm:mx-0 border-t border-gray-200 dark:border-gray-700 bg-white/95 dark:bg-gray-900/95 backdrop-blur px-4 py-3 flex items-center justify-between rounded-b-xl">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Changes are saved when you click <span class="font-medium text-gray-700 dark:text-gray-200">Save configuration</span>.
                </p>
                <div class="flex items-center gap-2">
                    <x-filament::button color="gray" type="button" wire:click="mount" icon="heroicon-m-arrow-path">
                        Reset
                    </x-filament::button>
                    <x-filament::button type="submit" icon="heroicon-m-check" color="primary">
                        <span wire:loading.remove wire:target="save">Save configuration</span>
                        <span wire:loading wire:target="save">Saving…</span>
                    </x-filament::button>
                </div>
            </div>
        </form>
    </div>
</x-filament-panels::page>
