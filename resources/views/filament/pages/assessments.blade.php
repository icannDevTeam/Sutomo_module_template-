<x-filament-panels::page>
    @php
        $tabs = [
            'tests'      => ['label' => 'Written tests', 'count' => $writtenCands->count()],
            'interviews' => ['label' => 'Interviews',    'count' => $interviews->count()],
        ];
    @endphp

    {{-- KPI strip --}}
    <div class="sp-kpis">
        <div class="sp-kpi" style="border-left:3px solid #4338ca;">
            <div class="sp-kpi-label">Tests scheduled</div>
            <div class="sp-kpi-value" style="color:#4338ca;">{{ $scheduled }}</div>
        </div>
        <div class="sp-kpi" style="border-left:3px solid #f59e0b;">
            <div class="sp-kpi-label">Awaiting score</div>
            <div class="sp-kpi-value" style="color:#b45309;">{{ $awaiting }}</div>
        </div>
        <div class="sp-kpi" style="border-left:3px solid #10b981;">
            <div class="sp-kpi-label">Pass rate</div>
            <div class="sp-kpi-value" style="color:#047857;">{{ $passRate }}%</div>
        </div>
        <div class="sp-kpi" style="border-left:3px solid #0ea5e9;">
            <div class="sp-kpi-label">Average score</div>
            <div class="sp-kpi-value" style="color:#0369a1;">{{ $avg }}</div>
        </div>
    </div>

    <div class="sp-assess-grid">
        {{-- LEFT: tabs + content --}}
        <div class="sp-card" style="padding:0;overflow:hidden;">
            <div class="sp-assess-tabs">
                @foreach ($tabs as $id => $tab)
                    <button wire:click="setActiveTab('{{ $id }}')" @class([
                        'sp-assess-tab',
                        'sp-assess-tab--active' => $activeTab === $id,
                    ])>
                        <span>{{ $tab['label'] }}</span>
                        <span class="sp-assess-tab__count">{{ $tab['count'] }}</span>
                    </button>
                @endforeach
            </div>

            <div style="padding:1.25rem;">
                @if ($activeTab === 'tests')
                    @forelse ($writtenCands as $c)
                        @php
                            $score = (int) $c->score_written;
                            $tone = $score >= 85 ? 'emerald' : ($score >= 70 ? 'blue' : ($score >= 50 ? 'amber' : 'rose'));
                            $label = $score >= 85 ? 'Strong' : ($score >= 70 ? 'Pass' : ($score >= 50 ? 'Marginal' : 'Fail'));
                            $pill = $tone === 'emerald' ? 'green' : ($tone === 'blue' ? 'blue' : ($tone === 'amber' ? 'amber' : 'red'));
                            $initials = collect(explode(' ', $c->name))->map(fn($p)=>mb_substr($p,0,1))->take(2)->implode('');
                        @endphp
                        <a href="{{ \App\Filament\Resources\CandidateResource::getUrl('view', ['record' => $c]) }}"
                           class="sp-assess-row sp-assess-row--{{ $tone }}">
                            <div class="sp-assess-row__avatar">{{ $initials }}</div>
                            <div class="sp-assess-row__id">
                                <div class="sp-assess-row__name">{{ $c->name }}</div>
                                <div class="sp-assess-row__meta">
                                    <span style="font-family:ui-monospace,monospace;">{{ $c->code }}</span>
                                    @if ($c->vacancy) <span>{{ $c->vacancy->title }}</span> @endif
                                </div>
                            </div>
                            <div class="sp-assess-row__bar">
                                <div class="sp-assess-bar">
                                    <div class="sp-assess-bar__fill sp-assess-bar__fill--{{ $tone }}" style="width:{{ min(100, $score) }}%;"></div>
                                </div>
                                <div class="sp-assess-row__score sp-assess-row__score--{{ $tone }}">{{ $score }}<span>/100</span></div>
                            </div>
                            <span class="sp-pill sp-pill-{{ $pill }}">{{ $label }}</span>
                        </a>
                    @empty
                        <div style="text-align:center;padding:3rem 1rem;color:#94a3b8;">
                            <x-filament::icon icon="heroicon-o-document-text" style="width:40px;height:40px;margin:0 auto .5rem;display:block;" />
                            <div style="font-weight:600;color:#475569;">No written tests scored yet.</div>
                        </div>
                    @endforelse
                @else
                    @forelse ($interviews as $iv)
                        @php
                            $ivStatusTone = $iv->status === 'completed' ? 'green' : ($iv->status === 'scheduled' ? 'blue' : 'amber');
                            $rec = $iv->recommendation;
                            $recTone = match (true) {
                                $rec === 'Strong Hire'                => 'green',
                                $rec === 'Hire'                        => 'blue',
                                $rec === 'Maybe'                       => 'amber',
                                in_array($rec, ['No Hire','Reject'])   => 'red',
                                default                                => 'slate',
                            };
                        @endphp
                        <div class="sp-assess-row sp-assess-row--slate" style="cursor:default;">
                            <div class="sp-assess-iv-date">
                                <div class="sp-assess-iv-date__d">{{ $iv->scheduled_date?->format('d') ?? '--' }}</div>
                                <div class="sp-assess-iv-date__m">{{ $iv->scheduled_date?->format('M') ?? '' }}</div>
                            </div>
                            <div class="sp-assess-row__id">
                                <div class="sp-assess-row__name">{{ $iv->candidate?->name ?? 'Unknown candidate' }}</div>
                                <div class="sp-assess-row__meta">
                                    <span>{{ $iv->type }}</span>
                                    @if ($iv->room) <span>Room {{ $iv->room }}</span> @endif
                                    @if ($iv->scheduled_time) <span>{{ $iv->scheduled_time }}</span> @endif
                                </div>
                            </div>
                            <span class="sp-pill sp-pill-{{ $ivStatusTone }}">{{ ucfirst($iv->status) }}</span>
                            @if ($rec)
                                <span class="sp-pill sp-pill-{{ $recTone }}">{{ $rec }}</span>
                            @else
                                <span style="font-size:.75rem;color:#94a3b8;">No recommendation</span>
                            @endif
                        </div>
                    @empty
                        <div style="text-align:center;padding:3rem 1rem;color:#94a3b8;">
                            <x-filament::icon icon="heroicon-o-calendar" style="width:40px;height:40px;margin:0 auto .5rem;display:block;" />
                            <div style="font-weight:600;color:#475569;">No interviews scheduled.</div>
                        </div>
                    @endforelse
                @endif
            </div>
        </div>

        {{-- RIGHT: insights --}}
        <div>
            @if ($activeTab === 'tests')
                <div class="sp-card">
                    <div class="sp-card-h">Score distribution</div>
                    <div class="sp-card-sub">Across {{ $writtenCands->count() }} scored candidates.</div>
                    <div class="sp-assess-hist">
                        @foreach ($buckets as $b)
                            @php $pct = $maxBucket > 0 ? round(($b['count'] / $maxBucket) * 100) : 0; @endphp
                            <div class="sp-assess-hist__row">
                                <div class="sp-assess-hist__lbl">{{ $b['label'] }}</div>
                                <div class="sp-assess-hist__track">
                                    <div class="sp-assess-hist__fill sp-assess-hist__fill--{{ $b['tone'] }}" style="width:{{ $pct }}%;"></div>
                                </div>
                                <div class="sp-assess-hist__val">{{ $b['count'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="sp-card">
                    <div class="sp-card-h">Top performers</div>
                    <div class="sp-card-sub">Highest written-test scores.</div>
                    @forelse ($top5 as $i => $c)
                        <a href="{{ \App\Filament\Resources\CandidateResource::getUrl('view', ['record' => $c]) }}" class="sp-assess-top">
                            <div class="sp-assess-top__rank">{{ $i + 1 }}</div>
                            <div class="sp-assess-top__name">
                                <div style="font-weight:600;color:#0f172a;">{{ $c->name }}</div>
                                <div style="font-size:.7rem;color:#94a3b8;">{{ $c->vacancy?->title ?? '--' }}</div>
                            </div>
                            <div class="sp-assess-top__score">{{ $c->score_written }}</div>
                        </a>
                    @empty
                        <div style="font-size:.85rem;color:#94a3b8;padding:.5rem 0;">No scored candidates yet.</div>
                    @endforelse
                </div>
            @else
                <div class="sp-card">
                    <div class="sp-card-h">Today</div>
                    <div class="sp-card-sub">{{ $todayIvs->count() }} interview{{ $todayIvs->count() === 1 ? '' : 's' }} scheduled.</div>
                    @forelse ($todayIvs as $iv)
                        <div class="sp-assess-mini">
                            <div class="sp-assess-mini__time">{{ $iv->scheduled_time ?? '--' }}</div>
                            <div>
                                <div style="font-weight:600;color:#0f172a;">{{ $iv->candidate?->name }}</div>
                                <div style="font-size:.7rem;color:#64748b;">{{ $iv->type }} @if($iv->room) · Room {{ $iv->room }} @endif</div>
                            </div>
                        </div>
                    @empty
                        <div style="font-size:.85rem;color:#94a3b8;padding:.5rem 0;">Nothing today.</div>
                    @endforelse
                </div>

                <div class="sp-card">
                    <div class="sp-card-h">Recommendations</div>
                    <div class="sp-card-sub">Outcomes from {{ $completedIvs }} completed interview{{ $completedIvs === 1 ? '' : 's' }}.</div>
                    <div class="sp-assess-rec">
                        <div class="sp-assess-rec__cell sp-assess-rec__cell--green">
                            <div class="sp-assess-rec__v">{{ $recBreak['strong'] }}</div>
                            <div class="sp-assess-rec__l">Strong hire</div>
                        </div>
                        <div class="sp-assess-rec__cell sp-assess-rec__cell--blue">
                            <div class="sp-assess-rec__v">{{ $recBreak['hire'] }}</div>
                            <div class="sp-assess-rec__l">Hire</div>
                        </div>
                        <div class="sp-assess-rec__cell sp-assess-rec__cell--amber">
                            <div class="sp-assess-rec__v">{{ $recBreak['maybe'] }}</div>
                            <div class="sp-assess-rec__l">Maybe</div>
                        </div>
                        <div class="sp-assess-rec__cell sp-assess-rec__cell--red">
                            <div class="sp-assess-rec__v">{{ $recBreak['no'] }}</div>
                            <div class="sp-assess-rec__l">No hire</div>
                        </div>
                    </div>
                </div>

                <div class="sp-card">
                    <div class="sp-card-h">Upcoming</div>
                    <div class="sp-card-sub">Next scheduled interviews.</div>
                    @forelse ($upcomingIvs->take(5) as $iv)
                        <div class="sp-assess-mini">
                            <div class="sp-assess-mini__time">{{ $iv->scheduled_date?->format('d M') }}</div>
                            <div>
                                <div style="font-weight:600;color:#0f172a;">{{ $iv->candidate?->name }}</div>
                                <div style="font-size:.7rem;color:#64748b;">{{ $iv->type }} @if($iv->scheduled_time) · {{ $iv->scheduled_time }} @endif</div>
                            </div>
                        </div>
                    @empty
                        <div style="font-size:.85rem;color:#94a3b8;padding:.5rem 0;">No upcoming interviews.</div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
