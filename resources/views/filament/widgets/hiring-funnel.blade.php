<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Hiring Funnel</x-slot>
        <x-slot name="description">Candidate distribution across the 8 active pipeline stages</x-slot>

        <div class="space-y-2.5">
            @foreach ($this->getFunnel() as $row)
                <div class="flex items-center gap-3">
                    <div class="w-32 shrink-0 text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ $row['label'] }}
                    </div>
                    <div class="flex-1 h-7 rounded-md bg-gray-100 dark:bg-white/5 overflow-hidden">
                        <div class="h-full rounded-md transition-all"
                             style="width: {{ max($row['pct'], 4) }}%; background: {{ $row['hex'] }}"></div>
                    </div>
                    <div class="w-10 text-right text-sm font-semibold tabular-nums text-gray-900 dark:text-white">
                        {{ $row['count'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
