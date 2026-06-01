<x-filament-panels::page>
<style>
.tr-toolbar { display:flex; flex-direction:column; gap:.5rem; margin-bottom:1rem; }
.tr-toolbar__row { display:flex; flex-wrap:wrap; align-items:center; gap:.5rem; }
.tr-toolbar input[type="text"], .tr-toolbar select {
    height:34px; padding:0 .65rem; font-size:.85rem;
    border:1px solid #e5e7eb; border-radius:.5rem; background:#fff; color:#111827;
}
.dark .tr-toolbar input[type="text"], .dark .tr-toolbar select { background:#1f2937; border-color:rgba(255,255,255,.1); color:#f3f4f6; }
.tr-toolbar input[type="text"] { min-width:220px; }
.tr-toolbar select { appearance:none; -webkit-appearance:none; background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'><path fill='%236b7280' d='M0 0l5 6 5-6z'/></svg>"); background-repeat:no-repeat; background-position:right .55rem center; padding-right:1.75rem; }
.tr-chip {
    display:inline-flex; align-items:center; gap:.35rem;
    height:30px; padding:0 .65rem; font-size:.8rem; font-weight:500;
    border:1px solid #e5e7eb; border-radius:9999px; background:#fff; color:#374151;
    cursor:pointer; transition:background .15s, color .15s, border-color .15s;
}
.dark .tr-chip { background:#1f2937; border-color:rgba(255,255,255,.1); color:#d1d5db; }
.tr-chip:hover { background:#f3f4f6; }
.dark .tr-chip:hover { background:rgba(255,255,255,.06); }
.tr-chip--on { background:#2563eb; color:#fff; border-color:#2563eb; }
.tr-chip--on:hover { background:#1d4ed8; }
.tr-active { display:inline-flex; align-items:center; gap:.35rem; height:26px; padding:0 .55rem; font-size:.72rem; font-weight:500;
    background:#dbeafe; color:#1e3a8a; border:1px solid #bfdbfe; border-radius:9999px; }
.dark .tr-active { background:rgba(59,130,246,.15); color:#bfdbfe; border-color:rgba(59,130,246,.35); }
.tr-active__x { cursor:pointer; opacity:.7; }
.tr-active__x:hover { opacity:1; }

.tr-grid { display:grid; gap:1rem; grid-template-columns:repeat(1,minmax(0,1fr)); }
@media (min-width:640px) { .tr-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media (min-width:1024px) { .tr-grid { grid-template-columns:repeat(3,minmax(0,1fr)); } }
@media (min-width:1280px) { .tr-grid { grid-template-columns:repeat(4,minmax(0,1fr)); } }

.tr-card { display:flex; flex-direction:column; gap:.6rem; padding:1rem;
    background:#fff; border:1px solid #e5e7eb; border-radius:.85rem;
    color:inherit; text-decoration:none;
    transition:border-color .15s, box-shadow .15s, transform .15s; }
.dark .tr-card { background:#1f2937; border-color:rgba(255,255,255,.08); }
.tr-card:hover { border-color:#3b82f6; box-shadow:0 4px 14px rgba(59,130,246,.12); transform:translateY(-1px); }

.tr-card__head { display:flex; align-items:flex-start; gap:.75rem; }
.tr-avatar { flex:0 0 auto; width:44px; height:44px; border-radius:9999px;
    background:linear-gradient(135deg,#3b82f6,#8b5cf6); color:#fff;
    display:flex; align-items:center; justify-content:center; font-weight:600; font-size:1rem; }
.tr-card__id { flex:1; min-width:0; }
.tr-card__name { font-weight:600; color:#111827; font-size:.95rem; line-height:1.2;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.dark .tr-card__name { color:#f3f4f6; }
.tr-card__sub { font-size:.75rem; color:#6b7280; line-height:1.35; margin-top:.15rem;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.tr-status { font-size:.65rem; font-weight:600; padding:.15rem .5rem; border-radius:9999px; text-transform:uppercase; letter-spacing:.04em; }
.tr-status--success { background:#d1fae5; color:#065f46; }
.tr-status--info    { background:#dbeafe; color:#1e40af; }
.tr-status--warning { background:#fef3c7; color:#92400e; }
.tr-status--danger  { background:#fee2e2; color:#991b1b; }
.tr-status--gray    { background:#f3f4f6; color:#374151; }
.dark .tr-status--success { background:rgba(16,185,129,.15); color:#6ee7b7; }
.dark .tr-status--info    { background:rgba(59,130,246,.15); color:#93c5fd; }
.dark .tr-status--warning { background:rgba(245,158,11,.15); color:#fcd34d; }
.dark .tr-status--danger  { background:rgba(239,68,68,.15); color:#fca5a5; }
.dark .tr-status--gray    { background:rgba(255,255,255,.08); color:#d1d5db; }

.tr-divider { height:1px; background:#f3f4f6; }
.dark .tr-divider { background:rgba(255,255,255,.06); }

.tr-readiness { display:flex; align-items:center; gap:.5rem; font-size:.8rem; }
.tr-dot { width:10px; height:10px; border-radius:9999px; flex:0 0 auto; }
.tr-dot--success { background:#10b981; box-shadow:0 0 0 3px rgba(16,185,129,.18); }
.tr-dot--warning { background:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.18); }
.tr-dot--danger  { background:#ef4444; box-shadow:0 0 0 3px rgba(239,68,68,.18); }

.tr-kpis { display:grid; grid-template-columns:1fr 1fr; gap:.35rem .75rem; font-size:.78rem; }
.tr-kpis__row { display:flex; justify-content:space-between; gap:.5rem; }
.tr-kpis__k { color:#6b7280; }
.dark .tr-kpis__k { color:#9ca3af; }
.tr-kpis__v { font-weight:600; color:#111827; }
.dark .tr-kpis__v { color:#f3f4f6; }
.tr-kpis__v--muted { color:#9ca3af; font-weight:500; }

.tr-footer { display:flex; flex-wrap:wrap; gap:.35rem; align-items:center; }
.tr-flag { display:inline-flex; align-items:center; gap:.25rem; font-size:.7rem; font-weight:500;
    padding:.18rem .5rem; border-radius:9999px; }
.tr-flag--warning { background:#fef3c7; color:#92400e; }
.tr-flag--danger  { background:#fee2e2; color:#991b1b; }
.tr-flag--info    { background:#dbeafe; color:#1e40af; }
.tr-flag--gray    { background:#f3f4f6; color:#374151; }
.dark .tr-flag--warning { background:rgba(245,158,11,.15); color:#fcd34d; }
.dark .tr-flag--danger  { background:rgba(239,68,68,.15); color:#fca5a5; }
.dark .tr-flag--info    { background:rgba(59,130,246,.15); color:#93c5fd; }
.dark .tr-flag--gray    { background:rgba(255,255,255,.08); color:#d1d5db; }

.tr-empty { padding:3rem 1rem; text-align:center; color:#6b7280;
    background:#fff; border:1px dashed #e5e7eb; border-radius:.85rem; }
.dark .tr-empty { background:#1f2937; border-color:rgba(255,255,255,.1); color:#9ca3af; }

.tr-saved__menu { position:relative; }
</style>

@php
    $activeChips = [];
    $labels = [
        'campus' => $campus, 'dept' => $dept, 'subject' => $subject,
        'status' => $statusOptions[$status] ?? $status,
        'employment' => $employment, 'gender' => $genderOptions[$gender] ?? $gender,
        'readiness' => $readinessOptions[$readiness] ?? $readiness,
        'tenureBand' => $tenureOptions[$tenureBand] ?? $tenureBand,
        'attendanceBand' => $attendanceOptions[$attendanceBand] ?? $attendanceBand,
        'obsBand' => $obsOptions[$obsBand] ?? $obsBand,
        'onLeaveWindow' => $leaveOptions[$onLeaveWindow] ?? $onLeaveWindow,
    ];
    foreach ($labels as $k => $v) if ($v) $activeChips[$k] = $v;
    if ($dueOnly)         $activeChips['dueOnly']         = 'Due for review';
    if ($openQueriesOnly) $activeChips['openQueriesOnly'] = 'Open queries';
    if ($pinnedNotesOnly) $activeChips['pinnedNotesOnly'] = 'Pinned notes';
    if ($contractEnding90)$activeChips['contractEnding90']= 'Contract ≤ 90d';
    if ($missingDocsOnly) $activeChips['missingDocsOnly'] = 'Missing docs';
    $hasAny = ! empty($activeChips) || $search !== '';
@endphp

<div class="tr-toolbar">
    {{-- Row 1: search · sort · view-switcher · saved views --}}
    <div class="tr-toolbar__row">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search name, code, subject…" />

        <select wire:model.live="sort" title="Sort by">
            @foreach ($sortOptions as $k => $lbl)
                <option value="{{ $k }}">Sort: {{ $lbl }}</option>
            @endforeach
        </select>

        <div style="display:flex; gap:.25rem;">
            <span class="tr-chip tr-chip--on">Grid</span>
            <a href="{{ $compareUrl }}" class="tr-chip">Compare</a>
            <a href="{{ $leaderboardUrl }}" class="tr-chip">Leaderboard</a>
        </div>

        @if ($savedViews->isNotEmpty())
            <select wire:change="selectSavedView($event.target.value || null)" style="min-width:160px;">
                <option value="">Saved views…</option>
                @foreach ($savedViews as $sv)
                    <option value="{{ $sv->id }}" @selected($savedViewId === $sv->id)>
                        {{ $sv->name }}{{ $sv->is_default ? ' ★' : '' }}
                    </option>
                @endforeach
            </select>
            @if ($savedViewId)
                @php $cur = $savedViews->firstWhere('id', $savedViewId); @endphp
                @if ($cur)
                    @if (! $cur->is_default)
                        <button type="button" class="tr-chip" wire:click="setDefaultSavedView({{ $cur->id }})" title="Set as my default">Set default</button>
                    @endif
                    <button type="button" class="tr-chip" wire:click="deleteSavedView({{ $cur->id }})" wire:confirm="Delete this saved view?">Delete view</button>
                @endif
            @endif
        @endif
    </div>

    {{-- Row 2: select pills --}}
    <div class="tr-toolbar__row">
        @if (! empty($campusOptions))
            <select wire:model.live="campus"><option value="">Campus</option>
                @foreach ($campusOptions as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
            </select>
        @endif
        @if (! empty($deptOptions))
            <select wire:model.live="dept"><option value="">Dept</option>
                @foreach ($deptOptions as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
            </select>
        @endif
        @if (! empty($subjectOptions))
            <select wire:model.live="subject"><option value="">Subject</option>
                @foreach ($subjectOptions as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
            </select>
        @endif
        <select wire:model.live="status"><option value="">Status</option>
            @foreach ($statusOptions as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
        </select>
        @if (! empty($employmentOptions))
            <select wire:model.live="employment"><option value="">Employment</option>
                @foreach ($employmentOptions as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
            </select>
        @endif
        <select wire:model.live="readiness"><option value="">Readiness</option>
            @foreach ($readinessOptions as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
        </select>
        <select wire:model.live="tenureBand"><option value="">Tenure</option>
            @foreach ($tenureOptions as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
        </select>
        <select wire:model.live="attendanceBand"><option value="">Attendance</option>
            @foreach ($attendanceOptions as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
        </select>
        <select wire:model.live="obsBand"><option value="">Obs score</option>
            @foreach ($obsOptions as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
        </select>
        <select wire:model.live="gender"><option value="">Gender</option>
            @foreach ($genderOptions as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
        </select>
        <select wire:model.live="onLeaveWindow"><option value="">On leave…</option>
            @foreach ($leaveOptions as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
        </select>
    </div>

    {{-- Row 3: toggle chips --}}
    <div class="tr-toolbar__row">
        <button type="button" wire:click="$toggle('dueOnly')"          class="tr-chip @if($dueOnly) tr-chip--on @endif">Due for review</button>
        <button type="button" wire:click="$toggle('openQueriesOnly')"  class="tr-chip @if($openQueriesOnly) tr-chip--on @endif">Open queries</button>
        <button type="button" wire:click="$toggle('pinnedNotesOnly')"  class="tr-chip @if($pinnedNotesOnly) tr-chip--on @endif">Pinned notes</button>
        <button type="button" wire:click="$toggle('contractEnding90')" class="tr-chip @if($contractEnding90) tr-chip--on @endif">Contract ≤ 90d</button>
        <button type="button" wire:click="$toggle('missingDocsOnly')"  class="tr-chip @if($missingDocsOnly) tr-chip--on @endif">Missing docs</button>

        @if ($hasAny)
            <span style="flex:1;"></span>
            <button type="button" wire:click="resetFilters" class="tr-chip">Clear all</button>
        @endif
    </div>

    {{-- Active chips row --}}
    @if (! empty($activeChips))
        <div class="tr-toolbar__row" style="gap:.35rem;">
            @foreach ($activeChips as $key => $label)
                <span class="tr-active">
                    {{ $label }}
                    <span class="tr-active__x" wire:click="clearFilter('{{ $key }}')" title="Clear">×</span>
                </span>
            @endforeach
        </div>
    @endif
</div>

@if (empty($rows))
    <div class="tr-empty">
        <div style="font-weight:600; color:#374151; margin-bottom:.35rem;">No teachers match your filters</div>
        <div style="font-size:.85rem; margin-bottom:.85rem;">Try clearing filters or broadening your search.</div>
        @if ($hasAny)
            <button type="button" class="tr-chip" wire:click="resetFilters">Clear all filters</button>
        @endif
    </div>
@else
    <div class="tr-grid">
        @foreach ($rows as $r)
            @php
                $t = $r['t']; $rd = $r['readiness'];
                $statusColor = match ($t->status) {
                    'permanent' => 'success', 'contract' => 'info',
                    'probation' => 'warning', 'leave' => 'warning',
                    'alumni' => 'danger', default => 'gray',
                };
                $statusLbl = \App\Models\Teacher::STATUSES[$t->status] ?? ($t->status ?? '—');
                $sub = collect([$t->subject, $t->dept, $t->campus])->filter()->implode(' · ');
                $reviewIsDue = ! $t->last_review
                    || \Illuminate\Support\Carbon::parse($t->last_review)->lt(now()->subMonths(12));
            @endphp
            <a href="{{ \App\Filament\Principal\Resources\TeacherResource::getUrl('view', ['record' => $t->id]) }}" class="tr-card">
                <div class="tr-card__head">
                    <div class="tr-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($t->name, 0, 1)) }}</div>
                    <div class="tr-card__id">
                        <div class="tr-card__name">{{ $t->name }}</div>
                        <div class="tr-card__sub">{{ $sub ?: '—' }}</div>
                    </div>
                    <span class="tr-status tr-status--{{ $statusColor }}">{{ $statusLbl }}</span>
                </div>

                <div class="tr-divider"></div>

                <div class="tr-readiness" title="{{ $rd['reason'] }}">
                    <span class="tr-dot tr-dot--{{ $rd['color'] }}"></span>
                    <strong>{{ $rd['label'] }}</strong>
                    <span style="color:#9ca3af; font-size:.72rem;">· {{ $rd['source'] === 'manual' ? 'manual' : 'auto' }}</span>
                    <span style="flex:1;"></span>
                    <span class="tr-kpis__k">{{ number_format($r['tenure'], 1) }} yrs</span>
                </div>

                <div class="tr-kpis">
                    <div class="tr-kpis__row">
                        <span class="tr-kpis__k">Attn (90d)</span>
                        <span class="@if($r['attn']===null) tr-kpis__v tr-kpis__v--muted @else tr-kpis__v @endif">
                            {{ $r['attn'] === null ? '—' : $r['attn'].'%' }}
                        </span>
                    </div>
                    <div class="tr-kpis__row">
                        <span class="tr-kpis__k">Obs avg</span>
                        <span class="@if($r['obs']===null) tr-kpis__v tr-kpis__v--muted @else tr-kpis__v @endif">
                            {{ $r['obs'] === null ? '—' : number_format($r['obs'], 1).'/4' }}
                        </span>
                    </div>
                    <div class="tr-kpis__row">
                        <span class="tr-kpis__k">Goals</span>
                        <span class="@if($r['goals']===null) tr-kpis__v tr-kpis__v--muted @else tr-kpis__v @endif">
                            {{ $r['goals'] === null ? '—' : $r['goals'].'%' }}
                        </span>
                    </div>
                    <div class="tr-kpis__row">
                        <span class="tr-kpis__k">Queries</span>
                        <span class="tr-kpis__v">{{ $r['open'] }} open</span>
                    </div>
                </div>

                <div class="tr-footer">
                    @if ($reviewIsDue)
                        <span class="tr-flag tr-flag--warning">Due for review</span>
                    @else
                        <span class="tr-flag tr-flag--gray">Reviewed {{ \Illuminate\Support\Carbon::parse($t->last_review)->format('M Y') }}</span>
                    @endif
                    @if ($r['open'] > 0)
                        <span class="tr-flag tr-flag--danger">{{ $r['open'] }} open quer{{ $r['open'] === 1 ? 'y' : 'ies' }}</span>
                    @endif
                    @if ($t->contract_end && $t->isContractEndingWithin(90))
                        <span class="tr-flag tr-flag--warning">Contract ends {{ \Illuminate\Support\Carbon::parse($t->contract_end)->format('M j') }}</span>
                    @endif
                    @if ($r['onLeaveT'])
                        <span class="tr-flag tr-flag--info">On leave today</span>
                    @endif
                    @if ($r['pinned'])
                        <span class="tr-flag tr-flag--info">Pinned note</span>
                    @endif
                    @if ($r['missing'])
                        <span class="tr-flag tr-flag--danger">Missing docs</span>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
@endif
</x-filament-panels::page>
