<x-filament-panels::page>
    <p class="text-sm text-gray-600 dark:text-gray-300">
        Contract continuation is now part of the Contract Workflow hub.
    </p>

    <x-filament::button
        tag="a"
        :href="\App\Filament\Principal\Pages\MySubmissions::getUrl(['type' => 'annual_continuation'], panel: 'principal')"
    >
        Open Contract Workflow
    </x-filament::button>
</x-filament-panels::page>
