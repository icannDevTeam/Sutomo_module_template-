<x-filament-panels::page>
    @php
        $palette = [
            'success' => ['fg' => '#047857', 'bg' => '#ecfdf5', 'ring' => '#a7f3d0', 'bar' => '#10b981', 'icon' => 'heroicon-o-star'],
            'info'    => ['fg' => '#0369a1', 'bg' => '#f0f9ff', 'ring' => '#bae6fd', 'bar' => '#0ea5e9', 'icon' => 'heroicon-o-hand-thumb-up'],
            'gray'    => ['fg' => '#374151', 'bg' => '#f9fafb', 'ring' => '#e5e7eb', 'bar' => '#9ca3af', 'icon' => 'heroicon-o-user'],
            'warning' => ['fg' => '#b45309', 'bg' => '#fffbeb', 'ring' => '#fde68a', 'bar' => '#f59e0b', 'icon' => 'heroicon-o-exclamation-triangle'],
            'danger'  => ['fg' => '#b91c1c', 'bg' => '#fef2f2', 'ring' => '#fecaca', 'bar' => '#ef4444', 'icon' => 'heroicon-o-no-symbol'],
        ];
        $total = max(1, array_sum(array_column($summary, 'count')));
    @endphp

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ($summary as $row)
            @php
                $p   = $palette[$row['color']] ?? $palette['gray'];
                $pct = (int) round(($row['count'] / $total) * 100);
            @endphp

            <div class="group relative overflow-hidden rounded-xl border bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:bg-gray-900"
                 style="border-color: {{ $p['ring'] }};">

                <div class="h-1 w-full" style="background: linear-gradient(90deg, {{ $p['bar'] }} 0%, {{ $p['ring'] }} 100%);"></div>

                <div class="p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg"
                                 style="background: {{ $p['bg'] }}; color: {{ $p['fg'] }};">
                                <x-filament::icon :icon="$p['icon']" class="h-5 w-5" />
                            </div>
                            <div class="text-[11px] font-semibold uppercase tracking-wider"
                                 style="color: {{ $p['fg'] }};">
                                {{ $row['label'] }}
                            </div>
                        </div>

                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                              style="background: {{ $p['bg'] }}; color: {{ $p['fg'] }};">
                            {{ $pct }}%
                        </span>
                    </div>

                    <div class="mt-3 flex items-baseline gap-1.5">
                        <div class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-50">
                            {{ $row['count'] }}
                        </div>
                        <div class="text-xs text-gray-400">
                            / {{ $total }} teachers
                        </div>
                    </div>

                    <p class="mt-2 text-xs leading-snug text-gray-500 dark:text-gray-400">
                        {{ $row['description'] }}
                    </p>

                    <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full" style="background: {{ $p['bg'] }};">
                        <div class="h-full rounded-full transition-all duration-500"
                             style="width: {{ $pct }}%; background: {{ $p['bar'] }};"></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
