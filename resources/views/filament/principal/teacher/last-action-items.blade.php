@php
    $observer    = $observer ?? 'Unknown observer';
    $observedAt  = $observed_at ?? null;
    $actionItems = $action_items ?? '';
    $dateLabel   = $observedAt ? \Illuminate\Support\Carbon::parse($observedAt)->format('d M Y') : '—';
@endphp

<div class="rounded-lg border border-amber-200 dark:border-amber-800 ring-1 ring-amber-200 dark:ring-amber-800 bg-amber-50 dark:bg-amber-950/40 p-4">
    <div class="flex items-center gap-2 text-xs font-medium text-amber-800 dark:text-amber-200 uppercase tracking-wide">
        <x-heroicon-o-clipboard-document-check class="w-4 h-4" />
        <span>From {{ $observer }} on {{ $dateLabel }}</span>
    </div>
    <div class="mt-2 whitespace-pre-wrap text-sm text-amber-900 dark:text-amber-100">{{ $actionItems }}</div>
</div>
