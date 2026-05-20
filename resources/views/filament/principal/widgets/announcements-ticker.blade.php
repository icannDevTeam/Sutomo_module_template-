<x-filament-widgets::widget>
    <x-filament::section>
        <div class="sp-ann-ticker">
            <div class="sp-ann-ticker__head">
                <div class="sp-ann-ticker__title">
                    <x-filament::icon icon="heroicon-o-megaphone" class="sp-ann-ticker__icon" />
                    <span>Announcements</span>
                </div>
                <a href="{{ \App\Filament\Principal\Pages\Announcements::getUrl() }}" class="sp-ann-ticker__link">View all</a>
            </div>

            @if ($items->isEmpty())
                <div class="sp-ann-ticker__empty">No announcements yet.</div>
            @else
                <div class="sp-ann-ticker__rail" role="list">
                    @foreach ($items as $a)
                        @php
                            $cat   = $a->category ?? 'general';
                            $catLb = \App\Models\Announcement::CATEGORIES[$cat] ?? ucfirst($cat);
                            $when  = ($a->sent_at ?? $a->updated_at)?->diffForHumans();
                            $tone  = match ($cat) {
                                'urgent'   => 'sp-ann-card--urgent',
                                'event'    => 'sp-ann-card--event',
                                'academic' => 'sp-ann-card--academic',
                                'reminder' => 'sp-ann-card--reminder',
                                default    => 'sp-ann-card--general',
                            };
                        @endphp
                        <a role="listitem"
                           href="{{ \App\Filament\Principal\Pages\Announcements::getUrl(['selected' => $a->id]) }}"
                           class="sp-ann-card {{ $tone }}">
                            <div class="sp-ann-card__top">
                                <span class="sp-ann-card__tag">{{ $catLb }}</span>
                                @if ($a->pinned)
                                    <span class="sp-ann-card__pin" title="Pinned">Pinned</span>
                                @endif
                            </div>
                            <div class="sp-ann-card__title">{{ $a->title }}</div>
                            <div class="sp-ann-card__meta">
                                <span>{{ $a->author_name ?? 'Principal Office' }}</span>
                                @if ($when) <span>·</span> <span>{{ $when }}</span> @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
