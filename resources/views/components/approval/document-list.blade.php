@props(['request', 'canDownload' => false])
@php
    $docs = $request->relationLoaded('documentVersions') ? $request->documentVersions : $request->documentVersions()->with(['uploader','acceptor','signer'])->get();
    $latestId = $docs->last()?->id;
    $size = function (?int $b) {
        if (! $b) return null;
        return $b >= 1048576 ? round($b / 1048576, 1) . ' MB' : round($b / 1024) . ' KB';
    };
@endphp
<div {{ $attributes->merge(['class' => 'ap-docs']) }}>
    @forelse($docs->sortByDesc('version_number') as $d)
        @php $isCurrent = $d->id === $latestId; @endphp
        <div class="ap-doc {{ $isCurrent ? 'is-current' : 'is-superseded' }}">
            <div class="ap-doc__icon" aria-hidden="true"><x-filament::icon icon="heroicon-o-document-text" /></div>
            <div style="min-width:0">
                <div class="ap-doc__name">v{{ $d->version_number }} · {{ $d->original_filename }}</div>
                <div class="ap-doc__meta">
                    <span>Uploaded {{ $d->uploaded_at?->format('d M Y H:i') }}</span>
                    @if($d->uploader)<span>by {{ $d->uploader->name }}</span>@endif
                    @if($size($d->file_size))<span>{{ $size($d->file_size) }}</span>@endif
                    @if($d->integrity_hash)<span class="ap-doc__hash" title="SHA-256 {{ $d->integrity_hash }}">sha256 {{ substr($d->integrity_hash, 0, 12) }}…</span>@endif
                </div>
                @if($d->upload_note)<div class="ap-prose" style="font-size:.78rem;margin-top:.3rem">{{ $d->upload_note }}</div>@endif
                <div class="ap-doc__marks">
                    @if(! $isCurrent)<span class="ap-badge ap-tone-neutral">Superseded</span>@endif
                    @if($d->correction_requested_at)
                        <span class="ap-badge ap-tone-waiting"><x-filament::icon icon="heroicon-m-arrow-uturn-left" aria-hidden="true" />Correction requested {{ $d->correction_requested_at->format('d M') }}</span>
                    @endif
                    @if($d->accepted_at)
                        <span class="ap-badge ap-tone-approved"><x-filament::icon icon="heroicon-m-document-check" aria-hidden="true" />Accepted {{ $d->accepted_at->format('d M') }}@if($d->acceptor) by {{ $d->acceptor->name }}@endif</span>
                    @endif
                    @if($d->signed_at)
                        <span class="ap-badge ap-tone-complete"><x-filament::icon icon="heroicon-m-pencil-square" aria-hidden="true" />Signed {{ $d->signed_at->format('d M') }}@if($d->signature_text) as “{{ $d->signature_text }}”@endif</span>
                    @endif
                    @if($isCurrent && ! $d->accepted_at && ! $d->correction_requested_at)
                        <span class="ap-badge ap-tone-review"><x-filament::icon icon="heroicon-m-eye" aria-hidden="true" />Awaiting principal review</span>
                    @endif
                </div>
                @if($d->correction_note)
                    <div class="ap-notice ap-notice--waiting" style="margin-top:.5rem">
                        <x-filament::icon icon="heroicon-m-chat-bubble-left-ellipsis" aria-hidden="true" />
                        <div><div class="ap-notice__title">Correction note</div>{{ $d->correction_note }}</div>
                    </div>
                @endif
            </div>
            <div>
                @if($canDownload)
                    <a class="ap-btn ap-btn--secondary ap-btn--sm" href="{{ route('approvals.document.download', $d) }}" target="_blank" rel="noopener">
                        <x-filament::icon icon="heroicon-m-arrow-down-tray" aria-hidden="true" /><span>Open</span>
                    </a>
                @endif
            </div>
        </div>
    @empty
        <p class="ap-muted" style="font-size:.84rem;margin:0">No document has been uploaded yet.</p>
    @endforelse
</div>
