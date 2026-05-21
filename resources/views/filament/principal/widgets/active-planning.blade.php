<x-filament-widgets::widget>
    <x-filament::section>
        @php
            $coverGradients = [
                'amber'   => 'linear-gradient(135deg,#fbbf24,#f97316)',
                'sky'     => 'linear-gradient(135deg,#38bdf8,#6366f1)',
                'indigo'  => 'linear-gradient(135deg,#6366f1,#a855f7)',
                'rose'    => 'linear-gradient(135deg,#fb7185,#dc2626)',
                'emerald' => 'linear-gradient(135deg,#34d399,#059669)',
                'slate'   => 'linear-gradient(135deg,#64748b,#334155)',
            ];
            $planUrl = \App\Filament\Principal\Pages\UnitPlanning::getUrl();
        @endphp

        <div class="sp-plan2">
            <header class="sp-plan2__head">
                <div class="sp-plan2__head-left">
                    <span class="sp-plan2__head-icon"><x-filament::icon icon="heroicon-s-puzzle-piece" class="w-4 h-4" /></span>
                    <div>
                        <h2 class="sp-plan2__head-title">Active unit planning</h2>
                        <p class="sp-plan2__head-sub">Collaborate with your team on what students are learning this week</p>
                    </div>
                </div>
                <div class="sp-plan2__head-right">
                    <span class="sp-ann2__stat"><strong>{{ $totalActive }}</strong><span>in progress</span></span>
                    <span class="sp-ann2__stat sp-ann2__stat--muted"><strong>{{ $totalDraft }}</strong><span>drafts</span></span>
                    @if ($liveNow > 0)
                        <span class="sp-plan2__live">
                            <span class="sp-plan-live__dot sp-plan-live__dot--lg"></span>
                            {{ $liveNow }} live
                        </span>
                    @endif
                    <a href="{{ $planUrl }}" class="sp-ann2__view-all">
                        Open planner
                        <x-filament::icon icon="heroicon-m-arrow-right" class="w-3.5 h-3.5" />
                    </a>
                </div>
            </header>

            @if ($units->isEmpty())
                <div class="sp-ann2__empty">
                    <span class="sp-ann2__empty-icon"><x-filament::icon icon="heroicon-o-puzzle-piece" class="w-6 h-6" /></span>
                    <div>
                        <p class="sp-ann2__empty-title">No active unit plans yet</p>
                        <p class="sp-ann2__empty-sub">Start a unit so your team can collaborate on lessons and resources.</p>
                    </div>
                    <a href="{{ $planUrl }}" class="sp-ann2__empty-cta">Open planner</a>
                </div>
            @else
                <div class="sp-plan2__grid">
                    @foreach ($units as $p)
                        @php $online = $p->collaborators->where('online_now', true); @endphp
                        <a href="{{ $planUrl }}" class="sp-plan2-card">
                            <span class="sp-plan2-card__cover" style="background: {{ $coverGradients[$p->cover_color ?? 'indigo'] ?? $coverGradients['indigo'] }};">
                                <span>{{ $p->cover_emoji ?? '📘' }}</span>
                            </span>
                            <div class="sp-plan2-card__body">
                                <div class="sp-plan2-card__meta">
                                    <span class="sp-plan2-card__grade">{{ $p->grade }}</span>
                                    @if ($online->isNotEmpty())
                                        <span class="sp-plan-live">
                                            <span class="sp-plan-live__dot"></span>
                                            {{ $online->count() }} live
                                        </span>
                                    @endif
                                </div>
                                <div class="sp-plan2-card__title">{{ $p->title }}</div>
                                <div class="sp-plan2-card__bar"><span style="width: {{ $p->completion_pct }}%"></span></div>
                                <div class="sp-plan2-card__footer">
                                    <div class="sp-plan-avatars">
                                        @foreach ($p->collaborators->take(4) as $c)
                                            <span class="sp-plan-avatar sp-plan-avatar--xs" style="background: {{ $c->color }};" title="{{ $c->name }}">{{ $c->initials }}</span>
                                        @endforeach
                                        @if ($p->collaborators->count() > 4)
                                            <span class="sp-plan-avatar sp-plan-avatar--xs sp-plan-avatar--more">+{{ $p->collaborators->count() - 4 }}</span>
                                        @endif
                                    </div>
                                    <span class="sp-plan2-card__pct">{{ $p->completion_pct }}%</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
