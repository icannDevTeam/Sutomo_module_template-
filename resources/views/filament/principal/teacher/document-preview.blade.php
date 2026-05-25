@php
    use Illuminate\Support\Facades\Storage;
    $path = $record->file_path;
    $url  = $path ? Storage::disk('public')->url($path) : null;
    $ext  = $path ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : null;
    $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp']);
    $isPdf   = $ext === 'pdf';
    $typeLabel = \App\Models\TeacherDocument::TYPES[$record->type] ?? $record->type;
    $statusLabel = \App\Models\TeacherDocument::STATUSES[$record->status] ?? $record->status;
    $statusColor = \App\Models\TeacherDocument::STATUS_COLORS[$record->status] ?? 'gray';
@endphp

<div class="sp-doc-preview">
    <div class="sp-doc-preview__meta">
        <div>
            <div class="sp-doc-preview__label">Teacher</div>
            <div class="sp-doc-preview__value">{{ $record->teacher?->name ?? '—' }}</div>
        </div>
        <div>
            <div class="sp-doc-preview__label">Type</div>
            <div class="sp-doc-preview__value">{{ $typeLabel }}</div>
        </div>
        <div>
            <div class="sp-doc-preview__label">Label</div>
            <div class="sp-doc-preview__value">{{ $record->label ?: '—' }}</div>
        </div>
        <div>
            <div class="sp-doc-preview__label">Expires</div>
            <div class="sp-doc-preview__value">{{ optional($record->expires_at)->format('d M Y') ?: '—' }}</div>
        </div>
        <div>
            <div class="sp-doc-preview__label">Status</div>
            <div class="sp-doc-preview__value">
                <span class="sp-doc-badge sp-doc-badge--{{ $statusColor }}">{{ $statusLabel }}</span>
            </div>
        </div>
        @if($record->verified_by)
        <div>
            <div class="sp-doc-preview__label">Decided By</div>
            <div class="sp-doc-preview__value">{{ $record->verified_by }} · {{ optional($record->verified_at)->format('d M Y H:i') }}</div>
        </div>
        @endif
    </div>

    @if($record->note)
        <div class="sp-doc-preview__note">
            <strong>Note:</strong> {{ $record->note }}
        </div>
    @endif

    <div class="sp-doc-preview__viewer">
        @if(! $url)
            <div class="sp-doc-preview__empty">No file uploaded.</div>
        @elseif($isImage)
            <a href="{{ $url }}" target="_blank" rel="noopener">
                <img src="{{ $url }}" alt="Document preview" />
            </a>
        @elseif($isPdf)
            <iframe src="{{ $url }}" title="Document"></iframe>
        @else
            <div class="sp-doc-preview__file">
                <div>
                    <div class="sp-doc-preview__label">File</div>
                    <div class="sp-doc-preview__value">{{ basename($path) }}</div>
                </div>
                <a class="sp-doc-preview__download" href="{{ $url }}" target="_blank" rel="noopener">
                    Open / Download
                </a>
            </div>
        @endif
    </div>

    @if($url)
        <div class="sp-doc-preview__actions">
            <a class="sp-doc-preview__download" href="{{ $url }}" target="_blank" rel="noopener">
                Open in new tab
            </a>
        </div>
    @endif
</div>
