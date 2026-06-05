@php
    /** @var string $heading */
    /** @var string $subheading */
    /** @var array $actions */
    /** @var array $years */
    /** @var string $currentAy */
    /** @var string $selectedAy */

@endphp

<div class="rounded-2xl border border-indigo-200/70 bg-gradient-to-r from-indigo-50 via-sky-50 to-cyan-50 dark:from-indigo-900/15 dark:via-sky-900/10 dark:to-cyan-900/10 dark:border-indigo-700/40 p-5">
    <div class="flex flex-wrap items-start gap-4">
        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 shrink-0">
            <x-heroicon-o-inbox-stack class="h-6 w-6"/>
        </div>
        <div class="flex-1 min-w-[16rem]">
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $heading }}</h1>
            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $subheading }}</p>
        </div>
        @if (! empty($actions))
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($actions as $action)
                    {{ $action }}
                @endforeach
            </div>
        @endif
    </div>

    <x-principal.academic-year-rail
        :years="$years"
        :selected-ay="$selectedAy"
        :current-ay="$currentAy"
        select-action="selectAcademicYear"
        archive-action="archiveAcademicYear"
        unarchive-action="unarchiveAcademicYear"
    />
</div>
