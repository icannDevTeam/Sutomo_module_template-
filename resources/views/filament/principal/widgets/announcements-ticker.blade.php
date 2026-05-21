<x-filament-widgets::widget>
    <x-filament::section>
        @php
            $catIcons = [
                'urgent'   => 'heroicon-o-exclamation-triangle',
                'event'    => 'heroicon-o-calendar-days',
                'academic' => 'heroicon-o-academic-cap',
                'reminder' => 'heroicon-o-bell-alert',
                'general'  => 'heroicon-o-megaphone',
            ];
            $catTone = fn ($c) => match ($c) {
                'urgent'   => 'sp-ann2--urgent',
                'event'    => 'sp-ann2--event',
                'academic' => 'sp-ann2--academic',
                'reminder' => 'sp-ann2--reminder',
                default    => 'sp-ann2--general',
            };
            $announcementsUrl = \App\Filament\Principal\Pages\Announcements::getUrl();
        @endphp

        <div class="sp-ann2">
            {{-- Header --}}
            <header class="sp-ann2__head">
                <div class="sp-ann2__head-left">
                    <span class="sp-ann2__head-icon">
                        <x-filament::icon icon="heroicon-s-megaphone" class="w-4 h-4" />
                    </span>
                    <div>
                        <h2 class="sp-ann2__head-title">Announcements</h2>
                        <p class="sp-ann2__head-sub">Latest updates from the Principal&rsquo;s office</p>
                    </div>
                </div>

                <div class="sp-ann2__head-right">
                    <span class="sp-ann2__stat">
                        <strong>{{ $newThisWeek }}</strong>
                        <span>this week</span>
                    </span>
                    @if ($scheduled > 0)
                        <span class="sp-ann2__stat sp-ann2__stat--muted">
                            <strong>{{ $scheduled }}</strong>
                            <span>scheduled</span>
                        </span>
                    @endif
                    <a href="{{ $announcementsUrl }}" class="sp-ann2__view-all">
                        View all
                        <x-filament::icon icon="heroicon-m-arrow-right" class="w-3.5 h-3.5" />
                    </a>
                </div>
            </header>

            @if (! $featured)
                {{-- Empty state --}}
                <div class="sp-ann2__empty">
                    <span class="sp-ann2__empty-icon">
                        <x-filament::icon icon="heroicon-o-megaphone" class="w-6 h-6" />
                    </span>
                    <div>
                        <p class="sp-ann2__empty-title">No announcements yet</p>
                        <p class="sp-ann2__empty-sub">Create one to keep parents, staff and students aligned.</p>
                    </div>
                    <a href="{{ $announcementsUrl }}" class="sp-ann2__empty-cta">New announcement</a>
                </div>
            @else
                <div class="sp-ann2__grid">
                    {{-- Featured hero --}}
                    @php
                        $cat    = $featured->category ?? 'general';
                        $catLbl = \App\Models\Announcement::CATEGORIES[$cat] ?? ucfirst($cat);
                        $when   = ($featured->sent_at ?? $featured->scheduled_at ?? $featured->updated_at);
                        $body   = strip_tags((string) $featured->body);
                        $excerpt = \Illuminate\Support\Str::limit($body, 220);
                        $audiences = collect($featured->audienceLabels())->take(3)->all();
                        $extraAud  = max(0, count($featured->audienceLabels()) - count($audiences));
                        $readPct = $featured->recipient_count > 0
                            ? min(100, round($featured->read_count / $featured->recipient_count * 100))
                            : 0;
                    @endphp

                    <a href="{{ \App\Filament\Principal\Pages\Announcements::getUrl(['selected' => $featured->id]) }}"
                       class="sp-ann2-hero {{ $catTone($cat) }}">
                        <div class="sp-ann2-hero__glow" aria-hidden="true"></div>
                        <div class="sp-ann2-hero__icon" aria-hidden="true">
                            <x-filament::icon :icon="$catIcons[$cat] ?? 'heroicon-o-megaphone'" class="w-7 h-7" />
                        </div>

                        <div class="sp-ann2-hero__body">
                            <div class="sp-ann2-hero__chips">
                                <span class="sp-ann2-chip sp-ann2-chip--solid">{{ $catLbl }}</span>
                                @if ($featured->pinned)
                                    <span class="sp-ann2-chip sp-ann2-chip--pin">
                                        <x-filament::icon icon="heroicon-s-bookmark" class="w-3 h-3" />
                                        Pinned
                                    </span>
                                @endif
                                @if ($featured->status === 'scheduled')
                                    <span class="sp-ann2-chip sp-ann2-chip--outline">
                                        <x-filament::icon icon="heroicon-o-clock" class="w-3 h-3" />
                                        Scheduled
                                    </span>
                                @endif
                                @foreach ($audiences as $aud)
                                    <span class="sp-ann2-chip sp-ann2-chip--ghost">{{ $aud }}</span>
                                @endforeach
                                @if ($extraAud > 0)
                                    <span class="sp-ann2-chip sp-ann2-chip--ghost">+{{ $extraAud }}</span>
                                @endif
                            </div>

                            <h3 class="sp-ann2-hero__title">{{ $featured->title }}</h3>
                            <p class="sp-ann2-hero__excerpt">{{ $excerpt }}</p>

                            <div class="sp-ann2-hero__footer">
                                <div class="sp-ann2-hero__author">
                                    <span class="sp-ann2-avatar">
                                        {{ \Illuminate\Support\Str::of($featured->author_name ?? 'P O')->explode(' ')->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('') }}
                                    </span>
                                    <div>
                                        <div class="sp-ann2-hero__author-name">{{ $featured->author_name ?? 'Principal Office' }}</div>
                                        <div class="sp-ann2-hero__author-role">
                                            {{ $featured->author_role ?? 'Principal' }}
                                            @if ($when)
                                                <span>·</span>
                                                <span title="{{ $when->toDayDateTimeString() }}">{{ $when->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if ($featured->recipient_count > 0)
                                    <div class="sp-ann2-hero__reach" title="{{ $featured->read_count }} of {{ $featured->recipient_count }} have read">
                                        <div class="sp-ann2-hero__reach-row">
                                            <span>Read</span>
                                            <strong>{{ $readPct }}%</strong>
                                        </div>
                                        <div class="sp-ann2-hero__bar">
                                            <span style="width: {{ $readPct }}%"></span>
                                        </div>
                                        <div class="sp-ann2-hero__reach-meta">
                                            {{ number_format($featured->read_count) }} / {{ number_format($featured->recipient_count) }} recipients
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <span class="sp-ann2-hero__cta" aria-hidden="true">
                            Open
                            <x-filament::icon icon="heroicon-m-arrow-up-right" class="w-3.5 h-3.5" />
                        </span>
                    </a>

                    {{-- Side rail --}}
                    <aside class="sp-ann2-side">
                        @forelse ($more as $a)
                            @php
                                $sCat   = $a->category ?? 'general';
                                $sLbl   = \App\Models\Announcement::CATEGORIES[$sCat] ?? ucfirst($sCat);
                                $sWhen  = ($a->sent_at ?? $a->scheduled_at ?? $a->updated_at);
                            @endphp
                            <a href="{{ \App\Filament\Principal\Pages\Announcements::getUrl(['selected' => $a->id]) }}"
                               class="sp-ann2-mini {{ $catTone($sCat) }}">
                                <span class="sp-ann2-mini__icon" aria-hidden="true">
                                    <x-filament::icon :icon="$catIcons[$sCat] ?? 'heroicon-o-megaphone'" class="w-4 h-4" />
                                </span>
                                <div class="sp-ann2-mini__body">
                                    <div class="sp-ann2-mini__chips">
                                        <span class="sp-ann2-chip sp-ann2-chip--tonal">{{ $sLbl }}</span>
                                        @if ($a->pinned)
                                            <span class="sp-ann2-chip sp-ann2-chip--pin sp-ann2-chip--xs">Pinned</span>
                                        @endif
                                        @if ($a->status === 'scheduled')
                                            <span class="sp-ann2-chip sp-ann2-chip--outline sp-ann2-chip--xs">Scheduled</span>
                                        @endif
                                    </div>
                                    <div class="sp-ann2-mini__title">{{ $a->title }}</div>
                                    <div class="sp-ann2-mini__meta">
                                        <span>{{ $a->author_name ?? 'Principal Office' }}</span>
                                        @if ($sWhen)
                                            <span aria-hidden="true">·</span>
                                            <span>{{ $sWhen->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="sp-ann2-side__empty">No other recent announcements.</div>
                        @endforelse
                    </aside>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

