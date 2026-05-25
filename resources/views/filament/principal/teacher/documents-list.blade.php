@php
    /** @var \Illuminate\Support\Collection $docs */
    use App\Models\TeacherDocument;
@endphp
@if ($docs->isEmpty())
    <div class="sp-teacher-empty">No documents uploaded.</div>
@else
    <div class="sp-doc-list">
        @foreach ($docs as $d)
            <div class="sp-doc-row sp-doc-row--{{ $d->status }}">
                <div class="sp-doc-row__type">
                    {{ TeacherDocument::TYPES[$d->type] ?? $d->type }}
                </div>
                <div class="sp-doc-row__body">
                    <div class="sp-doc-row__title">{{ $d->label ?? (TeacherDocument::TYPES[$d->type] ?? $d->type) }}</div>
                    <div class="sp-doc-row__meta">
                        @if ($d->uploaded_at) Uploaded {{ $d->uploaded_at->format('d M Y') }} @endif
                        @if ($d->expires_at) · expires {{ $d->expires_at->format('d M Y') }} @endif
                        @if ($d->verified_by) · {{ $d->status }} by {{ $d->verified_by }} @endif
                    </div>
                    @if ($d->note)<div class="sp-doc-row__note">Note: {{ $d->note }}</div>@endif
                </div>
                <div class="sp-doc-row__right">
                    <span class="sp-leave-badge sp-leave-badge--{{ $d->status === 'verified' ? 'approved' : ($d->status === 'rejected' ? 'rejected' : 'pending') }}">
                        {{ TeacherDocument::STATUSES[$d->status] ?? $d->status }}
                    </span>
                    @if ($d->file_path)
                        <a class="sp-doc-row__link" href="{{ asset('storage/' . $d->file_path) }}" target="_blank" rel="noopener">View file ↗</a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
