@php
    $year = now();
    $term = $year->month >= 7 ? 'Sem 1' : 'Sem 2';
    $ay   = $year->month >= 7
        ? $year->year . '/' . ($year->year + 1)
        : ($year->year - 1) . '/' . $year->year;
@endphp

<div class="fi-topbar-context hidden md:flex items-center gap-2 ms-3 text-xs">
    <span class="inline-flex items-center gap-1.5 rounded-md bg-primary-50 px-2 py-1 font-medium text-primary-700 ring-1 ring-inset ring-primary-200 dark:bg-primary-500/10 dark:text-primary-300 dark:ring-primary-400/30">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.75 2a.75.75 0 0 1 .75.75V4h7V2.75a.75.75 0 0 1 1.5 0V4h.25A2.75 2.75 0 0 1 18 6.75v8.5A2.75 2.75 0 0 1 15.25 18H4.75A2.75 2.75 0 0 1 2 15.25v-8.5A2.75 2.75 0 0 1 4.75 4H5V2.75A.75.75 0 0 1 5.75 2Zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75Z" clip-rule="evenodd" />
        </svg>
        AY {{ $ay }} · {{ $term }}
    </span>
    <span class="text-gray-400 dark:text-gray-500">·</span>
    <span class="text-gray-500 dark:text-gray-400">{{ $year->format('D, d M Y') }}</span>
</div>
