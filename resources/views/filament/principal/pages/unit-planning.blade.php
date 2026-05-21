<x-filament-panels::page>
@include('filament.principal.partials.planning-module-tabs', ['module' => 'units'])
@php
    $filters = [
        ['id' => 'all',       'label' => 'All units',  'icon' => 'heroicon-o-squares-2x2'],
        ['id' => 'active',    'label' => 'In progress','icon' => 'heroicon-o-bolt'],
        ['id' => 'draft',     'label' => 'Drafts',     'icon' => 'heroicon-o-document'],
        ['id' => 'completed', 'label' => 'Completed',  'icon' => 'heroicon-o-check-badge'],
        ['id' => 'mine',      'label' => 'Mine',       'icon' => 'heroicon-o-user-circle'],
    ];

    $tabs = [
        ['id' => 'planning',  'label' => 'Plan',      'icon' => 'heroicon-o-pencil-square'],
        ['id' => 'flow',      'label' => 'Lessons',   'icon' => 'heroicon-o-rectangle-stack'],
        ['id' => 'resources', 'label' => 'Resources', 'icon' => 'heroicon-o-folder-open'],
    ];

    $coverGradients = [
        'amber'   => 'linear-gradient(135deg,#fbbf24,#f97316)',
        'sky'     => 'linear-gradient(135deg,#38bdf8,#6366f1)',
        'indigo'  => 'linear-gradient(135deg,#6366f1,#a855f7)',
        'rose'    => 'linear-gradient(135deg,#fb7185,#dc2626)',
        'emerald' => 'linear-gradient(135deg,#34d399,#059669)',
        'slate'   => 'linear-gradient(135deg,#64748b,#334155)',
    ];

    $statusTone = [
        'draft'     => ['label' => 'Draft',       'cls' => 'sp-plan-pill--gray'],
        'active'    => ['label' => 'In progress', 'cls' => 'sp-plan-pill--indigo'],
        'completed' => ['label' => 'Completed',   'cls' => 'sp-plan-pill--emerald'],
        'archived'  => ['label' => 'Archived',    'cls' => 'sp-plan-pill--gray'],
    ];

    $lessonStatusTone = [
        'done'        => ['label' => 'Done',        'cls' => 'sp-plan-pill--emerald'],
        'in_progress' => ['label' => 'In progress', 'cls' => 'sp-plan-pill--amber'],
        'planned'     => ['label' => 'Planned',     'cls' => 'sp-plan-pill--gray'],
    ];
@endphp

<div class="sp-plan-shell">

    {{-- ============ LEFT: outline rail + unit list ============ --}}
    <aside class="sp-plan-side" aria-label="Units">
        <div class="sp-plan-side__head">
            <div class="sp-plan-side__title">
                <span class="sp-plan-side__icon">
                    <x-filament::icon icon="heroicon-s-puzzle-piece" class="w-4 h-4" />
                </span>
                <div>
                    <div class="sp-plan-side__title-main">Unit planner</div>
                    <div class="sp-plan-side__title-sub">{{ $counts['all'] }} units · {{ $counts['active'] }} active</div>
                </div>
            </div>
            <button type="button" class="sp-plan-newbtn" title="New unit plan">
                <x-filament::icon icon="heroicon-m-plus" class="w-4 h-4" />
                New
            </button>
        </div>

        <div class="sp-plan-side__filters">
            @foreach ($filters as $f)
                <button type="button" wire:click="setFilter('{{ $f['id'] }}')"
                        class="sp-plan-filter {{ $filter === $f['id'] ? 'is-active' : '' }}">
                    <x-filament::icon :icon="$f['icon']" class="w-4 h-4" />
                    <span>{{ $f['label'] }}</span>
                    <span class="sp-plan-filter__count">{{ $counts[$f['id']] ?? 0 }}</span>
                </button>
            @endforeach
        </div>

        <div class="sp-plan-side__search">
            <x-filament::icon icon="heroicon-m-magnifying-glass" class="w-4 h-4" />
            <input type="text" wire:model.live.debounce.300ms="q" placeholder="Search units, grade, theme…" />
        </div>

        <div class="sp-plan-side__list">
            @forelse ($plans as $p)
                @php
                    $active = $current && $current->id === $p->id;
                    $online = $p->collaborators->where('online_now', true);
                @endphp
                <button type="button" wire:click="select({{ $p->id }})"
                        class="sp-plan-card {{ $active ? 'is-active' : '' }}">
                    <span class="sp-plan-card__cover" style="background: {{ $coverGradients[$p->cover_color ?? 'indigo'] ?? $coverGradients['indigo'] }};">
                        <span>{{ $p->cover_emoji ?? '📘' }}</span>
                    </span>
                    <div class="sp-plan-card__body">
                        <div class="sp-plan-card__meta">
                            <span class="sp-plan-pill {{ $statusTone[$p->status]['cls'] }}">
                                {{ $statusTone[$p->status]['label'] }}
                            </span>
                            <span class="sp-plan-card__grade">{{ $p->grade }}</span>
                        </div>
                        <div class="sp-plan-card__title">{{ $p->title }}</div>
                        <div class="sp-plan-card__bar"><span style="width: {{ $p->completion_pct }}%"></span></div>
                        <div class="sp-plan-card__footer">
                            <div class="sp-plan-avatars">
                                @foreach ($p->collaborators->take(3) as $c)
                                    <span class="sp-plan-avatar sp-plan-avatar--xs" style="background: {{ $c->color }};" title="{{ $c->name }}">{{ $c->initials }}</span>
                                @endforeach
                                @if ($p->collaborators->count() > 3)
                                    <span class="sp-plan-avatar sp-plan-avatar--xs sp-plan-avatar--more">+{{ $p->collaborators->count() - 3 }}</span>
                                @endif
                            </div>
                            @if ($online->isNotEmpty())
                                <span class="sp-plan-live">
                                    <span class="sp-plan-live__dot"></span>
                                    {{ $online->count() }} live
                                </span>
                            @else
                                <span class="sp-plan-card__when">{{ $p->last_activity_at?->diffForHumans(null, true) }}</span>
                            @endif
                        </div>
                    </div>
                </button>
            @empty
                <div class="sp-plan-side__empty">No units match this filter.</div>
            @endforelse
        </div>
    </aside>

    {{-- ============ CENTER: canvas ============ --}}
    <main class="sp-plan-main">
        @if (! $current)
            <div class="sp-plan-empty">
                <span class="sp-plan-empty__icon">
                    <x-filament::icon icon="heroicon-o-puzzle-piece" class="w-7 h-7" />
                </span>
                <h2>Select a unit to start planning</h2>
                <p>Or create a new unit to collaborate with your team.</p>
            </div>
        @else
            @php
                $sections = $current->sections ?? [];
                $lessonGroups = collect($sections['lessons'] ?? [])->groupBy('week');
                $onlineCollabs = $current->collaborators->where('online_now', true);
            @endphp

            {{-- Cover header --}}
            <header class="sp-plan-hero" style="--sp-plan-hero-bg: {{ $coverGradients[$current->cover_color ?? 'indigo'] ?? $coverGradients['indigo'] }};">
                <div class="sp-plan-hero__bg" aria-hidden="true">
                    <div class="sp-plan-hero__emoji">{{ $current->cover_emoji ?? '📘' }}</div>
                </div>
                <div class="sp-plan-hero__overlay">
                    <div class="sp-plan-hero__top">
                        <div class="sp-plan-hero__crumbs">
                            <span>Academics</span>
                            <span aria-hidden="true">/</span>
                            <span>Planning</span>
                            <span aria-hidden="true">/</span>
                            <span>{{ $current->grade }}</span>
                        </div>
                        <div class="sp-plan-hero__live">
                            <div class="sp-plan-avatars">
                                @foreach ($current->collaborators->take(5) as $c)
                                    <span class="sp-plan-avatar" style="background: {{ $c->color }};" title="{{ $c->name }} · {{ ucfirst($c->role) }}{{ $c->online_now ? ' (online)' : '' }}">
                                        {{ $c->initials }}
                                        @if ($c->online_now)
                                            <span class="sp-plan-avatar__dot"></span>
                                        @endif
                                    </span>
                                @endforeach
                                @if ($current->collaborators->count() > 5)
                                    <span class="sp-plan-avatar sp-plan-avatar--more">+{{ $current->collaborators->count() - 5 }}</span>
                                @endif
                            </div>
                            @if ($onlineCollabs->isNotEmpty())
                                <span class="sp-plan-hero__livetxt">
                                    <span class="sp-plan-live__dot sp-plan-live__dot--lg"></span>
                                    {{ $onlineCollabs->pluck('name')->take(2)->implode(' & ') }}{{ $onlineCollabs->count() > 2 ? ' +'.($onlineCollabs->count() - 2) : '' }} editing now
                                </span>
                            @endif
                            <button type="button" class="sp-plan-hero__share" title="Invite collaborators">
                                <x-filament::icon icon="heroicon-m-user-plus" class="w-4 h-4" />
                                <span>Share</span>
                            </button>
                        </div>
                    </div>
                    <h1 class="sp-plan-hero__title">{{ $current->title }}</h1>
                    <div class="sp-plan-hero__meta">
                        <span class="sp-plan-pill {{ $statusTone[$current->status]['cls'] }} sp-plan-pill--solid">
                            {{ $statusTone[$current->status]['label'] }}
                        </span>
                        <span><x-filament::icon icon="heroicon-m-academic-cap" class="w-3.5 h-3.5 inline" /> {{ $current->grade }}</span>
                        @if ($current->theme)
                            <span><x-filament::icon icon="heroicon-m-sparkles" class="w-3.5 h-3.5 inline" /> {{ $current->theme }}</span>
                        @endif
                        @if ($current->starts_on)
                            <span><x-filament::icon icon="heroicon-m-calendar" class="w-3.5 h-3.5 inline" />
                                {{ $current->starts_on->format('d M') }} – {{ $current->ends_on?->format('d M Y') }}
                            </span>
                        @endif
                        <span><x-filament::icon icon="heroicon-m-user" class="w-3.5 h-3.5 inline" /> {{ $current->owner_name }}</span>
                    </div>
                    <div class="sp-plan-hero__progress" title="{{ $current->completion_pct }}% complete">
                        <div class="sp-plan-hero__progress-row">
                            <span>Unit completion</span>
                            <strong>{{ $current->completion_pct }}%</strong>
                        </div>
                        <div class="sp-plan-hero__bar"><span style="width: {{ $current->completion_pct }}%"></span></div>
                    </div>
                </div>
            </header>

            {{-- Tabs --}}
            <nav class="sp-plan-tabs" role="tablist">
                @foreach ($tabs as $t)
                    <button type="button" role="tab" wire:click="setTab('{{ $t['id'] }}')"
                            class="sp-plan-tab {{ $tab === $t['id'] ? 'is-active' : '' }}">
                        <x-filament::icon :icon="$t['icon']" class="w-4 h-4" />
                        {{ $t['label'] }}
                    </button>
                @endforeach
            </nav>

            {{-- Canvas body --}}
            <div class="sp-plan-canvas">
                @if ($tab === 'planning')
                    {{-- Overview --}}
                    @if (! empty($sections['overview']) || ! empty($sections['central_idea']))
                        <section class="sp-plan-sec">
                            <div class="sp-plan-sec__head">
                                <h3><x-filament::icon icon="heroicon-o-light-bulb" class="w-4 h-4 inline" /> Overview</h3>
                                <span class="sp-plan-sec__hint">Auto-saved · last edit {{ $current->last_activity_at?->diffForHumans() }}</span>
                            </div>
                            <div class="sp-plan-sec__body sp-plan-central">{{ $sections['overview'] ?? $sections['central_idea'] }}</div>
                        </section>
                    @endif

                    {{-- Learning objectives --}}
                    @php $objectives = $sections['objectives'] ?? $sections['lines_of_inquiry'] ?? []; @endphp
                    @if (! empty($objectives))
                        <section class="sp-plan-sec">
                            <div class="sp-plan-sec__head">
                                <h3><x-filament::icon icon="heroicon-o-flag" class="w-4 h-4 inline" /> Learning objectives</h3>
                                <button type="button" class="sp-plan-sec__add">+ Add objective</button>
                            </div>
                            <ol class="sp-plan-loi">
                                @foreach ($objectives as $i => $line)
                                    <li>
                                        <span class="sp-plan-loi__num">{{ $i + 1 }}</span>
                                        <span class="sp-plan-loi__text">{{ $line }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        </section>
                    @endif

                    {{-- Topics --}}
                    @if (! empty($sections['topics']))
                        <section class="sp-plan-sec">
                            <div class="sp-plan-sec__head">
                                <h3><x-filament::icon icon="heroicon-o-tag" class="w-4 h-4 inline" /> Topics</h3>
                                <button type="button" class="sp-plan-sec__add">+ Add topic</button>
                            </div>
                            <div class="sp-plan-chips">
                                @foreach ($sections['topics'] as $t)
                                    <span class="sp-plan-chip sp-plan-chip--sky">{{ $t }}</span>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    {{-- Prerequisites + Assessment --}}
                    <div class="sp-plan-grid2">
                        @if (! empty($sections['prerequisites']) || ! empty($sections['prior_learning']))
                            <section class="sp-plan-sec">
                                <div class="sp-plan-sec__head">
                                    <h3><x-filament::icon icon="heroicon-o-clock" class="w-4 h-4 inline" /> Prerequisites</h3>
                                </div>
                                <p class="sp-plan-sec__body">{{ $sections['prerequisites'] ?? $sections['prior_learning'] }}</p>
                            </section>
                        @endif
                        @if (! empty($sections['assessment']) || ! empty($sections['action']))
                            <section class="sp-plan-sec">
                                <div class="sp-plan-sec__head">
                                    <h3><x-filament::icon icon="heroicon-o-clipboard-document-check" class="w-4 h-4 inline" /> Assessment</h3>
                                </div>
                                <p class="sp-plan-sec__body">{{ $sections['assessment'] ?? $sections['action'] }}</p>
                            </section>
                        @endif
                    </div>
                @elseif ($tab === 'flow' || $tab === 'implementing')
                    {{-- Lesson flow --}}
                    @if ($lessonGroups->isEmpty())
                        <div class="sp-plan-sec sp-plan-empty-inline">
                            <p>No lessons added yet. Start sketching the weekly flow.</p>
                            <button type="button" class="sp-plan-newbtn">+ Add lesson</button>
                        </div>
                    @else
                        <section class="sp-plan-sec">
                            <div class="sp-plan-sec__head">
                                <h3><x-filament::icon icon="heroicon-o-rectangle-stack" class="w-4 h-4 inline" /> Lesson flow</h3>
                                <button type="button" class="sp-plan-sec__add">+ Add lesson</button>
                            </div>
                            <div class="sp-plan-weeks">
                                @foreach ($lessonGroups as $week => $lessons)
                                    <div class="sp-plan-week">
                                        <div class="sp-plan-week__label">Week {{ $week }}</div>
                                        <div class="sp-plan-week__items">
                                            @foreach ($lessons as $lsn)
                                                @php $tone = $lessonStatusTone[$lsn['status']] ?? $lessonStatusTone['planned']; @endphp
                                                <div class="sp-plan-lesson">
                                                    <span class="sp-plan-pill {{ $tone['cls'] }}">{{ $tone['label'] }}</span>
                                                    <span class="sp-plan-lesson__title">{{ $lsn['title'] }}</span>
                                                    <button type="button" class="sp-plan-lesson__more" aria-label="More">
                                                        <x-filament::icon icon="heroicon-m-ellipsis-horizontal" class="w-4 h-4" />
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                @elseif ($tab === 'resources' || $tab === 'evidencing' || $tab === 'reflecting')
                    @if (! empty($sections['resources']))
                        <section class="sp-plan-sec">
                            <div class="sp-plan-sec__head">
                                <h3><x-filament::icon icon="heroicon-o-folder-open" class="w-4 h-4 inline" /> Resources & materials</h3>
                                <button type="button" class="sp-plan-sec__add">+ Attach</button>
                            </div>
                            <ul class="sp-plan-files">
                                @foreach ($sections['resources'] as $r)
                                    <li>
                                        <x-filament::icon icon="heroicon-o-document" class="w-4 h-4" />
                                        <span>{{ $r }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @else
                        <section class="sp-plan-sec sp-plan-empty-inline">
                            <p>No resources attached yet. Drop in books, slides, worksheets, or links.</p>
                            <button type="button" class="sp-plan-newbtn">+ Attach resource</button>
                        </section>
                    @endif
                @endif
            </div>
        @endif
    </main>

    {{-- ============ RIGHT: inspector ============ --}}
    @if ($current)
        <aside class="sp-plan-inspector" aria-label="Collaboration">

            <div class="sp-plan-insp-block">
                <div class="sp-plan-insp__head">
                    <h4>Collaborators</h4>
                    <button type="button" class="sp-plan-insp__add" title="Invite">+</button>
                </div>
                <ul class="sp-plan-collab-list">
                    @foreach ($current->collaborators as $c)
                        <li>
                            <span class="sp-plan-avatar" style="background: {{ $c->color }};">
                                {{ $c->initials }}
                                @if ($c->online_now)
                                    <span class="sp-plan-avatar__dot"></span>
                                @endif
                            </span>
                            <div class="sp-plan-collab__body">
                                <div class="sp-plan-collab__name">{{ $c->name }}</div>
                                <div class="sp-plan-collab__role">
                                    {{ ucfirst($c->role) }}
                                    @if ($c->online_now)
                                        <span class="sp-plan-collab__online">· online</span>
                                    @elseif ($c->last_seen_at)
                                        <span>· seen {{ $c->last_seen_at->diffForHumans() }}</span>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="sp-plan-insp-block">
                <div class="sp-plan-insp__head">
                    <h4>Activity</h4>
                </div>
                <ol class="sp-plan-activity">
                    @php
                        $events = collect([
                            ['who' => $current->owner_name, 'color' => $current->collaborators->first()->color ?? '#6366f1', 'verb' => 'created the unit', 'at' => $current->created_at],
                        ]);
                        foreach ($current->comments as $cm) {
                            $events->push(['who' => $cm->author_name, 'color' => $cm->author_color, 'verb' => 'left a comment', 'at' => $cm->created_at]);
                        }
                        foreach ($current->collaborators->where('online_now', true) as $oc) {
                            $events->push(['who' => $oc->name, 'color' => $oc->color, 'verb' => 'is editing now', 'at' => null]);
                        }
                        $events = $events->sortByDesc(fn ($e) => $e['at']?->timestamp ?? PHP_INT_MAX)->take(6);
                    @endphp
                    @foreach ($events as $e)
                        <li>
                            <span class="sp-plan-activity__dot" style="background: {{ $e['color'] }};"></span>
                            <div>
                                <div class="sp-plan-activity__line"><strong>{{ $e['who'] }}</strong> {{ $e['verb'] }}</div>
                                <div class="sp-plan-activity__when">{{ $e['at']?->diffForHumans() ?? 'now' }}</div>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div class="sp-plan-insp-block sp-plan-insp-block--flex">
                <div class="sp-plan-insp__head">
                    <h4>Comments <span class="sp-plan-insp__badge">{{ $current->comments->count() }}</span></h4>
                </div>
                <div class="sp-plan-comments">
                    @forelse ($current->comments as $cm)
                        <div class="sp-plan-comment">
                            <span class="sp-plan-avatar sp-plan-avatar--xs" style="background: {{ $cm->author_color }};">{{ $cm->author_initials }}</span>
                            <div class="sp-plan-comment__body">
                                <div class="sp-plan-comment__head">
                                    <strong>{{ $cm->author_name }}</strong>
                                    <span>· {{ $cm->created_at->diffForHumans() }}</span>
                                </div>
                                @if ($cm->section_key)
                                    <div class="sp-plan-comment__ref">on {{ str_replace('_', ' ', $cm->section_key) }}</div>
                                @endif
                                <p class="sp-plan-comment__text">{{ $cm->body }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="sp-plan-empty-inline sp-plan-empty-inline--soft">
                            <p>No comments yet. Start the discussion.</p>
                        </div>
                    @endforelse
                </div>

                <form wire:submit.prevent="postComment" class="sp-plan-composer">
                    <textarea wire:model="newComment" rows="2" placeholder="Leave a comment for your team…"></textarea>
                    <div class="sp-plan-composer__bar">
                        <span>Press ⏎ to send</span>
                        <button type="submit" class="sp-plan-newbtn">
                            <x-filament::icon icon="heroicon-m-paper-airplane" class="w-3.5 h-3.5" />
                            Post
                        </button>
                    </div>
                </form>
            </div>
        </aside>
    @endif
</div>
</x-filament-panels::page>
