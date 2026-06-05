<x-filament-panels::page>
    <div class="space-y-6">
        <x-principal.module-hero
            title="Letter of Intent - Templates and Schedules"
            description="Draft reusable LOI bodies, then schedule yearly, monthly, or one-off sends. Saved schedules automatically compute next run time."
            icon="heroicon-o-cog-6-tooth"
            tone="amber"
        />

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
