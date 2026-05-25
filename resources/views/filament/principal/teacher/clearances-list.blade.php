@php
    $colors = \App\Models\TeacherClearance::STATUS_COLORS;
    $palette = ['success'=>'#16a34a','warning'=>'#d97706','danger'=>'#dc2626','gray'=>'#6b7280'];
@endphp
<div class="sp-clear-list">
    @forelse($clearances ?? [] as $c)
        @php $s = $c->effective_status; $hex = $palette[$colors[$s] ?? 'gray']; @endphp
        <div class="sp-clear" style="border-left-color:{{ $hex }}">
            <div class="sp-clear__top">
                <span class="sp-clear__type">{{ \App\Models\TeacherClearance::TYPES[$c->type] ?? $c->type }}</span>
                <span class="sp-clear__status" style="background:{{ $hex }}">{{ \App\Models\TeacherClearance::STATUSES[$s] ?? $s }}</span>
            </div>
            <div class="sp-clear__meta">
                @if($c->issuer)<span>{{ $c->issuer }}</span>@endif
                @if($c->expires_at)<span>· expires {{ $c->expires_at->format('d M Y') }}</span>@endif
            </div>
            @if($c->file_path)
                <a class="sp-clear__file" target="_blank" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($c->file_path) }}">📄 View document</a>
            @endif
        </div>
    @empty
        <div class="sp-clear-empty">No clearances recorded.</div>
    @endforelse
</div>
