<x-filament-panels::page>
    @php
        $letter  = $this->letter;
        $teacher = $letter?->teacher;
        $outcome = $this->data['follow_up_outcome'] ?? $letter?->follow_up_outcome;
        $hasResignationFile = filled($this->data['resignation_letter_path'] ?? $letter?->resignation_letter_path);
        $needsResignationFile = $outcome === 'resigning' && ! $hasResignationFile;
    @endphp

    <div class="space-y-5">
        @if ($needsResignationFile)
            <div role="alert"
                 class="sticky top-2 z-10 rounded-xl border border-amber-300 bg-amber-50 dark:border-amber-700/60 dark:bg-amber-950/40 p-4 shadow-sm ring-1 ring-amber-200/60 dark:ring-amber-800/40">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 rounded-lg bg-amber-100 dark:bg-amber-900/60 p-1.5">
                        @svg('heroicon-o-exclamation-triangle', 'w-5 h-5 text-amber-700 dark:text-amber-300')
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">
                            Resignation letter file missing
                        </p>
                        <p class="mt-0.5 text-xs text-amber-800 dark:text-amber-200/90 leading-relaxed">
                            This teacher is resigning. Upload the signed resignation letter (PDF or scan) before marking the case Ready for HR or Closed.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="rounded-2xl border border-amber-200/70 bg-gradient-to-r from-amber-50 via-rose-50 to-orange-50 dark:from-amber-900/15 dark:via-rose-900/10 dark:to-orange-900/10 dark:border-amber-700/40 p-5">
            <div class="flex flex-wrap items-start gap-4">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-500/15 text-amber-700 dark:text-amber-300">
                    <x-heroicon-o-clipboard-document-list class="h-6 w-6"/>
                </div>
                <div class="flex-1 min-w-[16rem]">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Follow-up · {{ $teacher?->name ?? '—' }}
                        </h2>
                        @if ($letter?->updated_at)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-white/80 dark:bg-gray-900/60 text-gray-600 dark:text-gray-300 ring-1 ring-gray-200 dark:ring-gray-700"
                                  title="Last updated {{ $letter->updated_at->format('Y-m-d H:i') }}">
                                <x-heroicon-o-clock class="w-3.5 h-3.5"/>
                                Updated {{ $letter->updated_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                        Academic Year <span class="font-medium">{{ $letter?->academic_year ?? '—' }}</span>
                        · Status
                        <span class="inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-xs font-medium text-rose-700 dark:bg-rose-900/40 dark:text-rose-200">
                            {{ \App\Models\LetterOfIntent::STATUSES[$letter?->status] ?? '—' }}
                        </span>
                        @if ($letter?->follow_up_status)
                            · Follow-up
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">
                                {{ \App\Models\LetterOfIntent::FOLLOW_UP_STATUSES[$letter->follow_up_status] ?? $letter->follow_up_status }}
                            </span>
                        @endif
                    </p>
                </div>
            </div>

            @if ($letter?->decline_reason)
                <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50/70 p-3 text-sm text-rose-900 dark:border-rose-800 dark:bg-rose-950/50 dark:text-rose-100">
                    <div class="flex items-center gap-2 font-medium">
                        <x-heroicon-o-megaphone class="h-4 w-4"/>
                        Teacher's decline reason
                    </div>
                    <p class="mt-1 whitespace-pre-wrap">{{ $letter->decline_reason }}</p>
                </div>
            @endif

            <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50/70 p-3 text-xs text-amber-900 dark:border-amber-800 dark:bg-amber-950/50 dark:text-amber-100">
                <div class="flex items-center gap-2 font-medium">
                    <x-heroicon-o-exclamation-triangle class="h-4 w-4"/>
                    Mandatory gate
                </div>
                <p class="mt-1">
                    For resignations: a scanned <strong>Resignation Letter</strong> must be uploaded before this case
                    can be marked <em>Ready for HR</em> or <em>Closed</em>.
                    HR is responsible for the final upload to the global Buku Induk.
                </p>
            </div>
        </div>

        <form wire:submit.prevent="saveNotes" class="space-y-5">
            {{ $this->form }}
        </form>
    </div>
</x-filament-panels::page>
