@props([
    'title',
    'description' => null,
    'icon' => 'heroicon-o-sparkles',
    'tone' => 'indigo',
])

@php
    $tones = [
        'indigo' => [
            'wrapper' => 'border-indigo-200/70 from-indigo-50 via-sky-50 to-cyan-50 dark:border-indigo-700/40 dark:from-indigo-900/15 dark:via-sky-900/10 dark:to-cyan-900/10',
            'icon' => 'bg-indigo-500/15 text-indigo-700 dark:text-indigo-300',
        ],
        'emerald' => [
            'wrapper' => 'border-emerald-200/70 from-emerald-50 via-teal-50 to-cyan-50 dark:border-emerald-700/40 dark:from-emerald-900/15 dark:via-teal-900/10 dark:to-cyan-900/10',
            'icon' => 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300',
        ],
        'amber' => [
            'wrapper' => 'border-amber-200/70 from-amber-50 via-yellow-50 to-orange-50 dark:border-amber-700/40 dark:from-amber-900/15 dark:via-yellow-900/10 dark:to-orange-900/10',
            'icon' => 'bg-amber-500/15 text-amber-700 dark:text-amber-300',
        ],
        'slate' => [
            'wrapper' => 'border-slate-200/70 from-slate-50 via-gray-50 to-zinc-50 dark:border-slate-700/40 dark:from-slate-900/15 dark:via-gray-900/10 dark:to-zinc-900/10',
            'icon' => 'bg-slate-500/15 text-slate-700 dark:text-slate-300',
        ],
    ];

    $cfg = $tones[$tone] ?? $tones['indigo'];
@endphp

<div @class([
    'rounded-2xl border bg-gradient-to-r p-5',
    $cfg['wrapper'],
])>
    <div class="flex flex-wrap items-start gap-4">
        <div @class([
            'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl',
            $cfg['icon'],
        ])>
            <x-filament::icon :icon="$icon" class="h-6 w-6" />
        </div>

        <div class="min-w-[16rem] flex-1">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h2>
            @if ($description)
                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $description }}</p>
            @endif
            @if (trim((string) $slot) !== '')
                <div class="mt-3">
                    {{ $slot }}
                </div>
            @endif
        </div>

        @if (isset($actions))
            <div class="flex flex-wrap items-center gap-2">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
