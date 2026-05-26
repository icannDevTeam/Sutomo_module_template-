@php($loi = $loi ?? null)
@if ($loi)
    <div class="flex flex-col gap-3 rounded-lg border border-sky-200 bg-sky-50 p-4 dark:border-sky-800 dark:bg-sky-950 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <x-heroicon-o-document-check class="h-6 w-6 text-sky-600" />
            <div>
                <div class="text-sm font-semibold text-sky-900 dark:text-sky-100">
                    {{ $loi->academic_year }}
                    @if ($loi->position)
                        · {{ $loi->position }}
                    @endif
                </div>
                <div class="text-xs text-sky-700 dark:text-sky-300">
                    Status: {{ \App\Models\LetterOfIntent::STATUSES[$loi->status] ?? $loi->status }}
                    @if ($loi->sent_at)
                        · Sent {{ $loi->sent_at->format('d M Y') }}
                    @endif
                    @if ($loi->deadline_at)
                        · Deadline {{ $loi->deadline_at->format('d M Y') }}
                    @endif
                </div>
            </div>
        </div>
        <a
            href="{{ \App\Filament\Principal\Resources\LetterOfIntentResource::getUrl('view', ['record' => $loi->id], panel: 'principal') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-sky-700"
        >
            <x-heroicon-o-eye class="h-4 w-4" />
            View Letter
        </a>
    </div>
@endif
