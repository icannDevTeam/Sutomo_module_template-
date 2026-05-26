@php
    $samples = $samples ?? [];
@endphp

<div class="space-y-3">
    <div class="flex items-center gap-2 text-xs italic text-gray-500 dark:text-gray-400">
        <x-heroicon-o-information-circle class="w-4 h-4" />
        <span>Sample data — anonymous student feedback module pending.</span>
    </div>

    <div class="space-y-2">
        @foreach ($samples as $s)
            @php
                $rating = max(0, min(5, (int) ($s['rating'] ?? 0)));
            @endphp
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/40 p-3">
                <div class="flex items-center gap-1">
                    @for ($i = 1; $i <= 5; $i++)
                        @if ($i <= $rating)
                            <x-heroicon-s-star class="w-4 h-4 text-amber-400" />
                        @else
                            <x-heroicon-o-star class="w-4 h-4 text-gray-300 dark:text-gray-600" />
                        @endif
                    @endfor
                </div>
                <p class="mt-2 text-sm italic text-gray-700 dark:text-gray-300">
                    &ldquo;{{ $s['text'] ?? '' }}&rdquo;
                </p>
                <div class="mt-2 text-xs italic text-gray-500 dark:text-gray-400">
                    Class {{ $s['class'] ?? '—' }} · {{ $s['date'] ?? '' }}
                </div>
            </div>
        @endforeach
    </div>
</div>
