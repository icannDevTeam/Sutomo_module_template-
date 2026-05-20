<x-filament-panels::page>
    @php
        $folders = [
            ['id' => 'inbox',     'label' => 'Inbox',     'icon' => 'heroicon-o-inbox',           'count' => $counts['inbox']],
            ['id' => 'scheduled', 'label' => 'Scheduled', 'icon' => 'heroicon-o-clock',           'count' => $counts['scheduled']],
            ['id' => 'drafts',    'label' => 'Drafts',    'icon' => 'heroicon-o-document',        'count' => $counts['drafts']],
            ['id' => 'sent',      'label' => 'Sent',      'icon' => 'heroicon-o-check-circle',    'count' => $counts['sent']],
            ['id' => 'archived',  'label' => 'Archived',  'icon' => 'heroicon-o-archive-box',     'count' => $counts['archived']],
        ];
        $cat = $current?->category ?? 'general';
        $catColors = \App\Models\Announcement::CATEGORY_COLORS;
        $catLabels = \App\Models\Announcement::CATEGORIES;
        $catBg = ['gray'=>'#64748b','info'=>'#0ea5e9','success'=>'#10b981','danger'=>'#ef4444','warning'=>'#f59e0b'];
    @endphp

    <div class="sp-inbox-shell" aria-label="Announcements inbox">

        {{-- Folder rail --}}
        <nav class="sp-ann-rail" aria-label="Mailbox folders">
            @foreach ($folders as $f)
                @php $active = $folder === $f['id']; @endphp
                <button type="button" wire:click="setFolder('{{ $f['id'] }}')"
                    aria-label="{{ $f['label'] }} ({{ $f['count'] }} items)"
                    aria-pressed="{{ $active ? 'true' : 'false' }}"
                    title="{{ $f['label'] }} ({{ $f['count'] }})"
                    class="sp-ann-rail-btn {{ $active ? 'is-active' : '' }}">
                    <x-filament::icon :icon="$f['icon']" style="width:20px;height:20px;" />
                    @if ($f['count'] > 0)
                        <span>{{ $f['count'] }}</span>
                    @endif
                </button>
            @endforeach
        </nav>

        {{-- List pane --}}
        <section style="border-right:1px solid #e5e7eb;display:flex;flex-direction:column;min-width:0;">
            <header class="sp-ann-list-h">
                <h2>{{ $folder }}</h2>
                <button type="button" class="sp-ann-icon-btn" aria-label="Sort announcements" title="Sort"><x-filament::icon icon="heroicon-o-bars-arrow-down" style="width:18px;height:18px;" /></button>
                <button type="button" class="sp-ann-icon-btn" aria-label="Filter announcements" title="Filter"><x-filament::icon icon="heroicon-o-funnel" style="width:18px;height:18px;" /></button>
            </header>
            <div style="padding:.5rem .85rem .65rem;border-bottom:1px solid #f1f5f9;">
                <input type="text" wire:model.live.debounce.400ms="q" placeholder="Search announcements..." class="sp-ann-search" />
            </div>
            <div style="overflow-y:auto;flex:1;">
                @forelse ($list as $a)
                    @php $isActive = $current && $current->id === $a->id; @endphp
                    <button type="button" wire:click="select({{ $a->id }})" class="sp-ann-row {{ $isActive ? 'is-active' : '' }}">
                        <div style="display:flex;align-items:flex-start;gap:.5rem;">
                            <div style="flex:1;min-width:0;">
                                <div style="display:flex;align-items:center;gap:.35rem;">
                                    @if ($a->pinned)
                                        <x-filament::icon icon="heroicon-s-bookmark" style="width:12px;height:12px;color:#f59e0b;" />
                                    @endif
                                    <span class="sp-ann-title">{{ $a->title }}</span>
                                </div>
                                <p class="sp-ann-preview">{!! \Illuminate\Support\Str::limit(strip_tags((string) $a->body), 100) !!}</p>
                                <div style="display:flex;align-items:center;gap:.45rem;">
                                    <div style="width:22px;height:22px;border-radius:9999px;background:#e0e7ff;color:#4338ca;font-size:.62rem;font-weight:700;display:flex;align-items:center;justify-content:center;">{{ \Illuminate\Support\Str::of($a->author_name)->split('/\s+/')->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->join('') }}</div>
                                    <span style="font-size:.72rem;color:#6b7280;">{{ $a->author_name }}</span>
                                </div>
                            </div>
                            <div style="text-align:right;flex-shrink:0;">
                                <div style="font-size:.7rem;color:#94a3b8;white-space:nowrap;">{{ ($a->sent_at ?? $a->updated_at)?->format('d M') }}</div>
                                @if ($a->status !== 'sent')
                                    <span style="display:inline-block;margin-top:.3rem;font-size:.58rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#fff;background:{{ $a->status === 'scheduled' ? '#f59e0b' : ($a->status === 'draft' ? '#64748b' : '#94a3b8') }};padding:1px 6px;border-radius:.25rem;">{{ $a->status }}</span>
                                @endif
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="sp-empty">No announcements in this folder.</div>
                @endforelse
            </div>
        </section>

        {{-- Reader pane --}}
        <section style="display:flex;flex-direction:column;background:#fff;min-width:0;">
            @if ($current)
                <header class="sp-ann-reader-h" style="padding:1.25rem 1.5rem;border-bottom:1px solid #f1f5f9;display:flex;flex-direction:column;gap:.85rem;">
                    {{-- Top row: badges (left) + action toolbar (right) --}}
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;">
                        <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                            <span style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#fff;background:{{ $catBg[$catColors[$cat] ?? 'gray'] }};padding:3px 9px;border-radius:.35rem;">{{ $catLabels[$cat] ?? $cat }}</span>
                            @if ($current->pinned)
                                <span style="font-size:.65rem;font-weight:700;color:#b45309;background:#fef3c7;padding:3px 9px;border-radius:.35rem;display:inline-flex;align-items:center;gap:.25rem;">
                                    <x-filament::icon icon="heroicon-s-bookmark" style="width:12px;height:12px;" /> PINNED
                                </span>
                            @endif
                        </div>
                        <div class="sp-ann-toolbar">
                            {{ ($this->editAction)(['id' => $current->id]) }}
                            {{ ($this->pinAction)(['id' => $current->id]) }}
                            @if ($current->status !== 'sent')
                                {{ ($this->publishAction)(['id' => $current->id]) }}
                            @endif
                            {{ ($this->archiveAction)(['id' => $current->id]) }}
                            {{ ($this->deleteAction)(['id' => $current->id]) }}
                        </div>
                    </div>

                    {{-- Title + meta --}}
                    <div style="min-width:0;">
                        <h1 style="overflow-wrap:anywhere;">{{ $current->title }}</h1>
                        <div class="sp-meta">
                            <strong style="color:#0f172a;">{{ $current->author_name }}</strong>
                            @if ($current->author_role) · <span>{{ $current->author_role }}</span>@endif
                            · {{ ($current->sent_at ?? $current->updated_at)?->format('d M Y, H:i') }}
                        </div>
                        <div class="sp-tags">
                            @foreach ($current->audienceLabels() as $label)
                                <span style="font-size:.7rem;background:#eef2ff;color:#4338ca;padding:3px 9px;border-radius:.35rem;font-weight:600;">{{ $label }}</span>
                            @endforeach
                            @foreach ($current->channelLabels() as $label)
                                <span style="font-size:.7rem;background:#ecfeff;color:#0e7490;padding:3px 9px;border-radius:.35rem;font-weight:600;">{{ $label }}</span>
                            @endforeach
                        </div>
                    </div>
                </header>
                <article class="sp-prose" style="padding:1.75rem 2rem;overflow-y:auto;flex:1;">
                    {!! $current->body !!}
                </article>
            @else
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;flex:1;color:#94a3b8;text-align:center;padding:2rem;">
                    <x-filament::icon icon="heroicon-o-megaphone" style="width:64px;height:64px;color:#67e8f9;" />
                    <p style="margin-top:1rem;font-weight:600;color:#475569;">No announcement selected</p>
                    <p style="font-size:.85rem;margin-top:.25rem;">Please select an announcement to view the details</p>
                </div>
            @endif
        </section>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
