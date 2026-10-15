{{--
    Tiered confirmation sheet.

    Tier 1  — heading + consequence + Confirm.
    Tier 2  — adds required reason/fields (validated server-side; error shown inline).
    Tier 3  — adds summary, acknowledgement checkbox, optional typed arm word,
              and a 1.5s arm delay before Confirm becomes clickable.

    The page owns open/close state and the submit method; this component owns
    focus trap, Esc, backdrop click, arming logic and the success/stale panels.
--}}
@props([
    'open' => false,
    'close' => 'closeSheet',
    'submit',
    'title',
    'description' => null,
    'tier' => 1,
    'tone' => 'primary',           {{-- primary | approved | danger | waiting --}}
    'icon' => 'heroicon-o-shield-check',
    'confirmLabel' => 'Confirm',
    'confirmVariant' => null,      {{-- override button variant --}}
    'acknowledge' => null,         {{-- checkbox text; required when set --}}
    'armWord' => null,             {{-- typed word required, e.g. REJECT --}}
    'armDelay' => null,            {{-- ms; defaults 1500 for tier 3 --}}
    'wide' => false,
    'error' => null,               {{-- server-side validation message --}}
    'stale' => null,               {{-- stale-state notice --}}
    'success' => null,             {{-- ['title','body','next_label','next_action'] --}}
])
@php
    $tier = (int) $tier;
    $armDelay = $armDelay ?? ($tier >= 3 ? 1500 : 0);
    $confirmVariant = $confirmVariant ?? match ($tone) {
        'danger'   => 'danger-solid',
        'approved' => 'approve-solid',
        default    => 'primary',
    };
    $tierLabel = match ($tier) {
        0 => 'Immediate action',
        1 => 'Quick confirmation',
        2 => 'Reason required',
        default => 'Committed action — cannot be undone',
    };
    $id = 'ap-sheet-' . \Illuminate\Support\Str::slug($title);
@endphp
@if($open)
<div
    class="ap-sheet-backdrop"
    x-data="{
        ack: false, word: '', armed: false, arming: false, timer: null,
        needsAck: {{ $acknowledge ? 'true' : 'false' }},
        armWord: @js($armWord),
        armDelay: {{ (int) $armDelay }},
        get ready() { return (!this.needsAck || this.ack) && (!this.armWord || this.word.trim() === this.armWord) },
        get canConfirm() { return this.ready && (this.armDelay === 0 || this.armed) },
        check() {
            if (this.ready && !this.armed && !this.arming && this.armDelay > 0) {
                this.arming = true;
                this.timer = setTimeout(() => { this.armed = true; this.arming = false }, this.armDelay);
            }
            if (!this.ready) { clearTimeout(this.timer); this.arming = false; this.armed = false }
        }
    }"
    x-init="$watch('ack', () => check()); $watch('word', () => check()); check()"
    x-on:click.self="$wire.{{ $close }}()"
    x-on:keydown.escape.window="$wire.{{ $close }}()"
>
    <div
        class="ap-sheet {{ $wide ? 'ap-sheet--wide' : '' }}"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $id }}-title"
        @if($description) aria-describedby="{{ $id }}-desc" @endif
        x-trap.inert.noscroll="true"
        tabindex="-1"
        wire:loading.class="is-busy" wire:target="{{ $submit }}"
    >
        <div class="ap-sheet__head">
            <div class="ap-sheet__icon ap-sheet__icon--{{ $tone }}" aria-hidden="true"><x-filament::icon :icon="$icon" /></div>
            <div style="min-width:0">
                <h2 class="ap-sheet__title" id="{{ $id }}-title">{{ $title }}</h2>
                <div class="ap-sheet__tier">
                    <x-filament::icon :icon="$tier >= 3 ? 'heroicon-m-lock-closed' : 'heroicon-m-information-circle'" aria-hidden="true" />
                    {{ $tierLabel }}
                </div>
            </div>
            <button type="button" class="ap-sheet__close" aria-label="Close" x-on:click="$wire.{{ $close }}()">
                <x-filament::icon icon="heroicon-m-x-mark" aria-hidden="true" />
            </button>
        </div>

        @if($success)
            <div class="ap-sheet__body">
                <div class="ap-success" role="status" aria-live="polite">
                    <div class="ap-success__icon"><x-filament::icon icon="heroicon-m-check" aria-hidden="true" /></div>
                    <div class="ap-success__title">{{ $success['title'] ?? 'Done' }}</div>
                    @if(!empty($success['body']))<div class="ap-success__body">{{ $success['body'] }}</div>@endif
                </div>
            </div>
            <div class="ap-sheet__foot">
                <span class="ap-actionbar__spacer"></span>
                <x-approval.btn variant="secondary" x-on:click="$wire.{{ $close }}()">Close</x-approval.btn>
                @if(!empty($success['next_action']))
                    <x-approval.btn variant="primary" :target="$success['next_action']" wire:click="{{ $success['next_action'] }}">{{ $success['next_label'] ?? 'Continue' }}</x-approval.btn>
                @endif
            </div>
        @else
            <div class="ap-sheet__body" @if($description) id="{{ $id }}-desc" @endif>
                @if($stale)
                    <div class="ap-stale" role="alert">
                        <x-filament::icon icon="heroicon-m-arrow-path" aria-hidden="true" />
                        <div><strong>This request changed while you were reviewing.</strong><br>{{ $stale }}</div>
                    </div>
                @endif

                @if($description)<p class="ap-prose" style="margin:0">{{ $description }}</p>@endif

                @isset($summary)
                    <div class="ap-summary" aria-label="What you are about to commit">{{ $summary }}</div>
                @endisset

                {{ $slot }}

                @if($acknowledge)
                    <label class="ap-check">
                        <input type="checkbox" x-model="ack" @if($stale) disabled @endif>
                        <span>{{ $acknowledge }}</span>
                    </label>
                @endif

                @if($armWord)
                    <div class="ap-field">
                        <label class="ap-label" for="{{ $id }}-arm">Type <strong>{{ $armWord }}</strong> to continue</label>
                        <input id="{{ $id }}-arm" class="ap-input ap-mono" type="text" autocomplete="off" spellcheck="false"
                               x-model="word" placeholder="{{ $armWord }}" @if($stale) disabled @endif>
                    </div>
                @endif

                @if($error)
                    <div class="ap-error" role="alert">{{ $error }}</div>
                @endif
            </div>

            <div class="ap-sheet__foot">
                <span class="ap-muted" style="font-size:.74rem" x-show="arming" x-cloak aria-live="polite">Hold on…</span>
                <span class="ap-actionbar__spacer"></span>
                <x-approval.btn variant="secondary" x-on:click="$wire.{{ $close }}()">Cancel</x-approval.btn>
                @if(! $stale)
                    <x-approval.btn
                        :variant="$confirmVariant"
                        :target="$submit"
                        wire:click="{{ $submit }}"
                        class="ap-arm"
                        x-bind:class="{ 'is-arming': arming }"
                        x-bind:disabled="!canConfirm"
                        x-bind:aria-disabled="!canConfirm"
                    >{{ $confirmLabel }}<span class="ap-arm__bar" aria-hidden="true"></span></x-approval.btn>
                @else
                    <x-approval.btn variant="primary" wire:click="$refresh">Reload</x-approval.btn>
                @endif
            </div>
        @endif
    </div>
</div>
@endif
