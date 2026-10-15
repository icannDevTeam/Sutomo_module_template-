{{--
    Compact queue row. Click opens the detail (href or wire:click).
    Only the single primary action for this viewer is surfaced on the row.
--}}
@props([
    'request',
    'user',
    'href' => null,            {{-- link target --}}
    'open' => null,            {{-- wire:click expression alternative --}}
    'selected' => false,
    'showSubmitter' => false,
    'primaryTarget' => null,   {{-- wire:target prefix for the primary action, e.g. "openSheet" --}}
    'primaryHref' => null,     {{-- link base for the primary action; "?sheet=key" is appended --}}
])
@php
    $p   = \App\Support\ContractRequestPresenter::for($request);
    $rev = $request->latestRevision;
    $mine = $p->isWaitingOn($user);
    $urgency = $p->urgency();
    $primary = $p->primaryActionFor($user);
    $classes = 'ap-row'
        . ($selected ? ' is-selected' : '')
        . ($mine ? ' is-mine' : '')
        . ($urgency === 'overdue' ? ' is-overdue' : ($urgency === 'attention' ? ' is-attention' : ''));
    $rowId = 'ap-row-' . $request->id;
@endphp
<article
    id="{{ $rowId }}"
    {{ $attributes->merge(['class' => $classes]) }}
    role="link"
    tabindex="0"
    aria-current="{{ $selected ? 'true' : 'false' }}"
    aria-label="Open {{ $p->typeLabel() }} for {{ $request->teacher?->name ?? 'teacher' }}, {{ $p->stageLabel() }}"
    @if($href)
        onclick="if(!event.target.closest('button,a')) window.location='{{ $href }}'"
        onkeydown="if((event.key==='Enter'||event.key===' ')&&!event.target.closest('button,a')){event.preventDefault();window.location='{{ $href }}'}"
    @elseif($open)
        wire:click="{{ $open }}"
        wire:keydown.enter="{{ $open }}"
        wire:loading.class="is-busy" wire:target="{{ $open }}"
    @endif
>
    <div class="ap-row__main">
        <div class="ap-row__title">
            <x-approval.type-chip :type="$request->type" />
            <span class="ap-row__name">{{ $request->teacher?->name ?? 'Unknown teacher' }}</span>
            <x-approval.tier-transition :from="$rev?->current_type" :to="$rev?->proposed_type" />
            @if($rev?->academic_year)<span class="ap-muted" style="font-size:.76rem">AY {{ $rev->academic_year }}</span>@endif
        </div>
        <div class="ap-row__meta">
            <x-approval.stage-badge :presenter="$p" />
            <x-approval.owner-chip :presenter="$p" :user="$user" />
            @if($showSubmitter && $request->submitter)
                <span><x-filament::icon icon="heroicon-m-paper-airplane" class="ap-icon-14" aria-hidden="true" />{{ $request->submitter->name }}@if($request->campus) · {{ strtoupper($request->campus) }}@endif</span>
            @endif
        </div>
    </div>
    <div class="ap-row__side">
        <x-approval.age :presenter="$p" />
        @if($primary && $primaryTarget)
            <x-approval.btn
                :variant="$primary['variant'] === 'primary' ? 'primary' : 'secondary'"
                size="sm"
                :icon="$primary['icon']"
                :target="$primaryTarget . '(' . $request->id . ', \'' . $primary['key'] . '\')'"
                wire:click.stop="{{ $primaryTarget }}({{ $request->id }}, '{{ $primary['key'] }}')"
            >{{ $primary['label'] }}</x-approval.btn>
        @elseif($primary && $primaryHref)
            <a class="ap-btn ap-btn--sm {{ $primary['variant'] === 'primary' ? 'ap-btn--primary' : 'ap-btn--secondary' }}"
               href="{{ $primaryHref }}?sheet={{ $primary['key'] }}" onclick="event.stopPropagation()">
                <span class="ap-btn__icon" aria-hidden="true"><x-filament::icon :icon="$primary['icon']" /></span>
                <span>{{ $primary['label'] }}</span>
            </a>
        @endif
        <span class="ap-row__chevron" aria-hidden="true"><x-filament::icon icon="heroicon-m-chevron-right" /></span>
    </div>
</article>
