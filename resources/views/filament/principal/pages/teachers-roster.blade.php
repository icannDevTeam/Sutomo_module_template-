<x-filament-panels::page>
    {{-- Intro --}}
    <div class="sp-cm-intro">
        Cross-cutting view of every teacher's current contract state.
        <b>{{ $counts['active'] }}</b> active · <b>{{ $counts['inactive'] }}</b> inactive.
        Click <b>Open</b> to jump into the right workspace for each teacher.
    </div>

    {{-- Tier breakdown chips --}}
    <div class="sp-tier-strip">
        @foreach ([
            'pkwt_1'  => 'PKWT-I',
            'pkwt_2'  => 'PKWT-II',
            'pkwt_3'  => 'PKWT-III',
            'guru_sk' => 'Guru SK',
            'other'   => 'Other',
        ] as $key => $label)
            <span class="sp-tier-strip__item">
                {{ $label }}
                <b>{{ $counts['by_tier'][$key] ?? 0 }}</b>
            </span>
        @endforeach
    </div>

    {{-- KPIs --}}
    <div class="sp-kpis">
        @php
            $kpis = [
                ['Active teachers',   $counts['active'],    'slate',   'heroicon-o-users'],
                ['In probation',      $counts['probation'], 'amber',   'heroicon-o-academic-cap'],
                ['In renewal window', $counts['renewal'],   'sky',     'heroicon-o-arrow-path'],
                ['Inactive',          $counts['inactive'],  'violet',  'heroicon-o-archive-box'],
            ];
        @endphp
        @foreach ($kpis as [$label, $value, $tone, $icon])
            <div class="sp-kpi sp-kpi--{{ $tone }}">
                <div class="sp-kpi-head">
                    <span class="sp-kpi-label">{{ $label }}</span>
                    <span class="sp-kpi-icon"><x-filament::icon :icon="$icon" style="width:16px;height:16px;" /></span>
                </div>
                <div class="sp-kpi-value">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    {{-- Filter strip --}}
    <div class="sp-tabs sp-tabs--with-search">
        <div class="sp-tabs__group">
            @foreach ($tierLabels as $key => $label)
                <button type="button"
                    wire:click='$set("tier", @js($key))'
                    class="sp-tab {{ $tier === $key ? 'is-active' : '' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
        <input type="text"
            wire:model.live.debounce.350ms="search"
            placeholder="Search by name…"
            class="sp-tabs__search" />
    </div>

    {{-- Roster table --}}
    <div class="sp-roster">
        <table>
            <thead>
                <tr>
                    <th>Teacher</th>
                    <th>Tier</th>
                    <th>Period</th>
                    <th>Status</th>
                    <th>Flags</th>
                    <th style="text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $r)
                    @php
                        $t = $r->teacher;
                        $c = $r->contract;
                        $tierKey = $r->tier ?? 'inactive';
                        $tierLabel = match ($r->tier) {
                            'pkwt_1' => 'PKWT-I',
                            'pkwt_2' => 'PKWT-II',
                            'pkwt_3' => 'PKWT-III',
                            'guru_sk' => 'Guru SK',
                            default => '—',
                        };
                        $statusLabel = \App\Models\Teacher::STATUSES[$t->status] ?? $t->status;
                        $statusColor = \App\Models\Teacher::STATUS_COLORS[$t->status] ?? 'gray';
                        $statusPill = match ($statusColor) {
                            'success' => 'sp-pill-green',
                            'warning' => 'sp-pill-amber',
                            'danger'  => 'sp-pill-red',
                            'info'    => 'sp-pill-blue',
                            'primary' => 'sp-pill-indigo',
                            default   => 'sp-pill-gray',
                        };
                        $cta = match ($r->destination) {
                            'probation-watch'        => ['Open · Probation', \App\Filament\Principal\Pages\ProbationWatch::getUrl()],
                            'contract-renewal'       => ['Open · Renewal',   \App\Filament\Principal\Pages\ContractRenewal::getUrl()],
                            'contract-continuation'  => ['Open · LOI',       \App\Filament\Principal\Pages\ContractContinuation::getUrl()],
                            default                  => ['Open · Profile',   \App\Filament\Principal\Resources\TeacherResource::getUrl('view', ['record' => $t->id])],
                        };
                    @endphp
                    <tr>
                        <td>
                            <div class="sp-roster__cell-id">
                                <div class="sp-avatar" style="width:32px;height:32px;font-size:.75rem;">{{ \Illuminate\Support\Str::of($t->name)->substr(0,1) }}</div>
                                <div style="min-width:0;">
                                    <div class="sp-roster__name">{{ $t->name }}</div>
                                    <div class="sp-roster__sub">{{ $t->email ?? '—' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="sp-tier sp-tier--{{ $tierKey }}">{{ $tierLabel }}</span>
                        </td>
                        <td>
                            @if ($c)
                                <div class="sp-roster__period">
                                    {{ \Illuminate\Support\Carbon::parse($c->starts_at)->format('d M Y') }}
                                    →
                                    {{ $c->ends_at ? \Illuminate\Support\Carbon::parse($c->ends_at)->format('d M Y') : 'Open-ended' }}
                                    @if (! is_null($r->days_left))
                                        <small style="{{ $r->days_left <= 30 ? 'color:#9f1239;font-weight:700;' : '' }}">
                                            @if ($r->days_left < 0)
                                                Ended {{ abs($r->days_left) }}d ago
                                            @else
                                                {{ $r->days_left }}d remaining
                                            @endif
                                        </small>
                                    @endif
                                </div>
                            @else
                                <span class="sp-roster__sub">No active contract</span>
                            @endif
                        </td>
                        <td>
                            <span class="sp-pill {{ $statusPill }}">{{ $statusLabel }}</span>
                        </td>
                        <td>
                            <div class="sp-roster__flags">
                                @if ($r->in_probation)
                                    <span class="sp-pill sp-pill-amber">PROBATION</span>
                                @endif
                                @if ($r->in_renewal)
                                    <span class="sp-pill sp-pill-blue">RENEWAL</span>
                                @endif
                                @if ($c && $c->submitted_to_yayasan_at && is_null($c->yayasan_decision))
                                    <span class="sp-pill sp-pill-indigo">AT YAYASAN</span>
                                @endif
                                @if ($r->is_inactive)
                                    <span class="sp-pill sp-pill-slate">INACTIVE</span>
                                @endif
                            </div>
                        </td>
                        <td style="text-align:right;">
                            <a href="{{ $cta[1] }}" class="sp-act sp-act--primary">
                                {{ $cta[0] }}
                                <x-filament::icon icon="heroicon-m-arrow-right" style="width:13px;height:13px;" />
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:3rem 1.5rem;text-align:center;color:#94a3b8;">
                            No teachers match the current filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
