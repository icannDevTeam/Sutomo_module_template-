<x-filament-panels::page>
@php
    $user   = auth()->user();
    $counts = $this->counts;
    $rows   = $this->rows->take($this->limit);
    $tabs   = \App\Filament\Principal\Pages\MySubmissions::TABS;
@endphp

<div class="ap-intro">
    One place to follow contract renewals and annual continuations from submission through Yayasan review, HR preparation, contract review, Agreement handover, and completion.
</div>

<div class="ap-toolbar">
    <div class="ap-tabs" role="tablist" aria-label="Submission status">
        @foreach($tabs as $key => $def)
            <x-approval.tab :active="$tab === $key" :count="$counts[$key] ?? 0" :alert="$key === 'action'"
                wire:click="setTab('{{ $key }}')" target="setTab" :id="'tab-' . $key" :aria-controls="'panel-' . $key">
                {{ $def['label'] }}
            </x-approval.tab>
        @endforeach
    </div>
    <div class="ap-segment" role="group" aria-label="Request type">
        @foreach(['all' => 'All', 'pkwt_renewal' => 'PKWT', 'annual_continuation' => 'Continuation'] as $k => $l)
            <button type="button" class="ap-segment__btn" aria-pressed="{{ $typeFilter === $k ? 'true' : 'false' }}"
                wire:click="setType('{{ $k }}')" wire:loading.attr="disabled" wire:target="setType">{{ $l }}</button>
        @endforeach
    </div>
</div>

<div id="panel-{{ $tab }}" role="tabpanel" aria-labelledby="tab-{{ $tab }}" aria-live="polite">
    {{-- Skeleton while switching tabs/filters; previous rows are hidden so no one acts on stale data. --}}
    <div wire:loading.delay.shorter wire:target="setTab, setType">
        <x-approval.skeleton-rows :count="4" />
    </div>

    <div wire:loading.remove wire:target="setTab, setType">
        @if($rows->isEmpty())
            @php
                [$t, $b] = match ($tab) {
                    'ready'    => ['No signed LOIs are ready to submit', 'Signed continuation letters for permanent teachers appear here before they are sent to Yayasan.'],
                    'action'   => ['Nothing needs your action', 'When HR uploads a document for review, or Yayasan asks for a correction, it will appear here.'],
                    'progress' => ['No submissions in progress', 'Submit a renewal from Contract Renewal, or a continuation from Letters of Intent, and track it here.'],
                    'done'     => ['No completed submissions yet', 'Requests appear here once HR has confirmed registration.'],
                    default    => ['No closed submissions', 'Rejected or withdrawn requests are kept here with their reasons.'],
                };
            @endphp
            <x-approval.empty-state :title="$t" :body="$b" icon="heroicon-o-clipboard-document-check" />
        @elseif($tab === 'ready')
            <div class="ap-list">
                @foreach($rows->take($this->limit) as $loi)
                    @php $preview = route('letter-of-intent.print', ['record' => $loi]); @endphp
                    <article class="ap-row" wire:key="ready-loi-{{ $loi->id }}">
                        <div class="ap-row__main">
                            <div class="ap-row__title">
                                <x-approval.type-chip type="annual_continuation" />
                                <span class="ap-row__name">{{ $loi->teacher?->name ?? 'Unknown teacher' }}</span>
                                @if($loi->academic_year)<span class="ap-muted" style="font-size:.76rem">AY {{ $loi->academic_year }}</span>@endif
                            </div>
                            <div class="ap-row__meta">
                                <span class="ap-badge ap-tone-approved">Teacher signed LOI</span>
                                @if($loi->teacher?->employee_no)<span>{{ $loi->teacher->employee_no }}</span>@endif
                                <span>Ready for Yayasan submission</span>
                            </div>
                        </div>
                        <div class="ap-row__side">
                            <a class="ap-btn ap-btn--secondary ap-btn--sm" href="{{ $preview }}" target="_blank" rel="noopener">
                                <x-filament::icon icon="heroicon-m-eye" aria-hidden="true" /> Preview LOI
                            </a>
                            <button type="button" class="ap-btn ap-btn--primary ap-btn--sm"
                                wire:click="submitContinuation({{ $loi->id }})"
                                wire:confirm="Submit {{ $loi->teacher?->name }}'s signed continuation to Yayasan? This starts the official approval and contract-preparation workflow."
                                wire:loading.attr="disabled" wire:target="submitContinuation">
                                <x-filament::icon icon="heroicon-m-paper-airplane" aria-hidden="true" />
                                <span wire:loading.remove wire:target="submitContinuation">Submit to Yayasan</span>
                                <span wire:loading wire:target="submitContinuation">Submitting…</span>
                            </button>
                        </div>
                        <details class="ap-row__preview" style="grid-column:1/-1">
                            <summary>Preview signed Letter of Intent</summary>
                            <iframe src="{{ $preview }}" loading="lazy" title="Signed Letter of Intent preview"
                                style="width:100%;height:32rem;margin-top:.75rem;border:1px solid #dbe2ea;border-radius:.75rem;background:white"></iframe>
                        </details>
                    </article>
                @endforeach
            </div>
            @if($this->hasMore())
                <div class="ap-loadmore">
                    <x-approval.btn variant="secondary" target="loadMore" wire:click="loadMore" icon="heroicon-m-arrow-down">Load more</x-approval.btn>
                </div>
                <div wire:loading.delay.shorter wire:target="loadMore" style="margin-top:.5rem"><x-approval.skeleton-rows :count="2" /></div>
            @endif
        @else
            <div class="ap-list">
                @foreach($rows as $req)
                    @php $href = \App\Filament\Principal\Pages\MySubmissionDetail::getUrl(['record' => $req->id], panel: 'principal'); @endphp
                    <x-approval.request-row :request="$req" :user="$user" :href="$href" :primary-href="$href" wire:key="row-{{ $req->id }}" />
                @endforeach
            </div>

            @if($this->hasMore())
                <div class="ap-loadmore">
                    <x-approval.btn variant="secondary" target="loadMore" wire:click="loadMore" icon="heroicon-m-arrow-down">Load more</x-approval.btn>
                </div>
                <div wire:loading.delay.shorter wire:target="loadMore" style="margin-top:.5rem"><x-approval.skeleton-rows :count="2" /></div>
            @endif
        @endif
    </div>
</div>
</x-filament-panels::page>
