<x-filament-panels::page>
    @php
        $letter = $this->letter;
        $teacher = $letter?->teacher;
        $isSigned = $letter && $letter->status === 'signed';
        $isDeclined = $letter && $letter->status === 'declined';
        $isLocked = $isSigned || $isDeclined;
        $isAgreement = $this->isAgreementMode();
        $agreementSigned = $letter && ! is_null($letter->agreement_signed_at);
        $contractReady = $letter && ! is_null($letter->yayasan_contract_uploaded_at);
    @endphp

    @if ($isAgreement)
        {{-- =====================================================
             AGREEMENT MODE — post-Yayasan e-sign (= contract handover)
             ===================================================== --}}
        <div class="mx-auto w-full max-w-2xl">
            <div class="rounded-xl bg-white shadow ring-1 ring-amber-200 dark:bg-gray-900 dark:ring-amber-800">
                <div class="border-b border-amber-200 bg-amber-50 px-6 py-4 dark:border-amber-800 dark:bg-amber-950">
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-finger-print class="h-6 w-6 text-amber-600" />
                        <div>
                            <h2 class="text-base font-semibold text-amber-900 dark:text-amber-100">
                                Agreement Letter E-Sign · {{ $letter?->academic_year }}
                            </h2>
                            <p class="text-xs text-amber-700 dark:text-amber-200">
                                Signing here = official contract handover. Buku Induk is recorded after this.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-5">
                    @if ($teacher)
                        <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-gray-50 px-3 py-1 text-sm text-gray-700 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700">
                            <x-heroicon-o-user-circle class="h-4 w-4" />
                            <span class="font-medium">{{ $teacher->name }}</span>
                            @if ($teacher->employee_no)
                                <span class="text-gray-400">·</span>
                                <span class="text-xs text-gray-500">{{ $teacher->employee_no }}</span>
                            @endif
                        </div>
                    @endif

                    @if (! $contractReady)
                        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                            The Yayasan contract has not been uploaded yet. The principal must record the contract before the agreement can be signed.
                        </div>
                    @elseif ($agreementSigned)
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            <div class="flex items-center gap-2 font-medium">
                                <x-heroicon-o-check-badge class="h-5 w-5" />
                                Agreement e-signed by {{ $letter->agreement_signature_text }}
                            </div>
                            <div class="mt-1 text-xs">on {{ $letter->agreement_signed_at?->format('d M Y H:i') }}</div>
                            <div class="mt-1 text-xs">Buku Induk logged at {{ $letter->buku_induk_recorded_at?->format('d M Y H:i') ?? '—' }}</div>
                        </div>
                    @else
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-800">
                            <div class="font-semibold">Temporary Agreement Letter Template</div>
                            <div class="mt-2 whitespace-pre-line text-xs leading-5 text-slate-700">This temporary agreement confirms that the teacher has received and acknowledged the approved Yayasan contract for {{ $letter->academic_year }}.

Teacher: {{ $teacher?->name ?? '-' }}
Position: {{ $letter->position ?? 'Teacher' }}
Academic Year: {{ $letter->academic_year }}

By signing, the teacher acknowledges contract receipt and agrees that this handover is recorded in the school Buku Induk administration flow.</div>
                        </div>

                        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                            By signing below, you acknowledge receipt of your contract for {{ $letter->academic_year }} and confirm the official handover from Yayasan.
                        </div>

                        <label for="agreementSignatureText" class="mt-5 block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Type your full name to e-sign the agreement
                        </label>
                        <input
                            id="agreementSignatureText"
                            type="text"
                            wire:model="signatureText"
                            placeholder="{{ $teacher?->name }}"
                            class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm"
                        />

                        <div class="mt-4 flex justify-end">
                            <button
                                type="button"
                                wire:click="signAgreement"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-amber-700">
                                <x-heroicon-o-finger-print class="h-4 w-4" />
                                I Confirm Receipt — E-Sign Agreement
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
    <div class="mx-auto w-full max-w-2xl">
        <div class="rounded-xl bg-white shadow ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-700">
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <x-heroicon-o-document-check class="h-6 w-6 text-primary-600" />
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            Letter of Intent · {{ $letter?->academic_year }}
                        </h2>
                        @if ($letter?->position)
                            <p class="text-xs text-gray-500">{{ $letter->position }}</p>
                        @endif
                    </div>
                </div>
                <span @class([
                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                    'bg-gray-100 text-gray-700' => $letter?->status === 'draft',
                    'bg-sky-100 text-sky-700' => $letter?->status === 'sent',
                    'bg-emerald-100 text-emerald-700' => $isSigned,
                    'bg-rose-100 text-rose-700' => $isDeclined,
                ])>
                    {{ \App\Models\LetterOfIntent::STATUSES[$letter?->status] ?? '—' }}
                </span>
            </div>

            {{-- Teacher pill --}}
            @if ($teacher)
                <div class="px-6 pt-4">
                    <div class="inline-flex items-center gap-2 rounded-full bg-gray-50 px-3 py-1 text-sm text-gray-700 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700">
                        <x-heroicon-o-user-circle class="h-4 w-4" />
                        <span class="font-medium">{{ $teacher->name }}</span>
                        @if ($teacher->employee_no)
                            <span class="text-gray-400">·</span>
                            <span class="text-xs text-gray-500">{{ $teacher->employee_no }}</span>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Body --}}
            <div class="px-6 py-5">
                <div class="whitespace-pre-wrap rounded-lg bg-gray-50 p-4 text-sm leading-relaxed text-gray-800 dark:bg-gray-800 dark:text-gray-100">{{ $letter?->body }}</div>

                @if ($letter?->deadline_at)
                    <div class="mt-3 flex items-center gap-2 text-xs text-gray-500">
                        <x-heroicon-o-clock class="h-4 w-4" />
                        Deadline: {{ $letter->deadline_at->format('d M Y H:i') }}
                    </div>
                @endif
            </div>

            {{-- Signed acknowledgement --}}
            @if ($isSigned)
                <div class="mx-6 mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
                    <div class="flex items-center gap-2 font-medium">
                        <x-heroicon-o-check-badge class="h-5 w-5" />
                        Signed by {{ $letter->signature_text }}
                    </div>
                    <div class="mt-1 text-xs">on {{ $letter->signed_at?->format('d M Y H:i') }}</div>
                </div>
            @elseif ($isDeclined)
                <div class="mx-6 mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-800 dark:bg-rose-950 dark:text-rose-200">
                    <div class="flex items-center gap-2 font-medium">
                        <x-heroicon-o-x-circle class="h-5 w-5" />
                        Letter declined
                    </div>
                    @if ($letter->decline_reason)
                        <div class="mt-1 text-xs">Reason: {{ $letter->decline_reason }}</div>
                    @endif
                </div>
            @else
                {{-- Sign form --}}
                <div class="border-t border-gray-200 px-6 py-5 dark:border-gray-700">
                    <label for="signatureText" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Type your full name to sign
                    </label>
                    <input
                        id="signatureText"
                        type="text"
                        wire:model="signatureText"
                        placeholder="{{ $teacher?->name }}"
                        class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 sm:text-sm"
                    />

                    <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                        <button
                            type="button"
                            wire:click="decline"
                            wire:confirm="Decline this letter of intent?"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            <x-heroicon-o-x-mark class="h-4 w-4" />
                            Decline
                        </button>
                        <button
                            type="button"
                            wire:click="sign"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                        >
                            <x-heroicon-o-check-circle class="h-4 w-4" />
                            I Confirm — Sign Letter
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endif
</x-filament-panels::page>
