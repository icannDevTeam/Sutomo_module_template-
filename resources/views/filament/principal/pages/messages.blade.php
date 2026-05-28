<x-filament-panels::page>
    <style>
        .msg-shell { display: grid; grid-template-columns: 340px 1fr; gap: 1.15rem; height: calc(100vh - 9.5rem); min-height: 560px; }
        @media (max-width: 900px) { .msg-shell { grid-template-columns: 1fr; height: auto; } }

        .msg-card {
            background: #fff; border: 1px solid #e5e7eb; border-radius: .85rem;
            box-shadow: 0 1px 2px rgba(0,0,0,.03);
            display: flex; flex-direction: column; overflow: hidden;
        }

        /* ---------- LEFT: thread list ---------- */
        .msg-list__head { padding: .9rem 1rem .25rem; display: flex; flex-direction: column; gap: .65rem; }
        .msg-search { position: relative; display: flex; gap: .5rem; align-items: center; }
        .msg-search__input { position: relative; flex: 1; }
        .msg-search__input svg { position: absolute; left: .65rem; top: 50%; transform: translateY(-50%); width: .95rem; height: .95rem; color: #9ca3af; }
        .msg-search__input input {
            width: 100%; padding: .55rem .75rem .55rem 2.05rem;
            border: 1px solid #e5e7eb; border-radius: .55rem; font-size: .82rem; color: #111827; background: #f9fafb;
        }
        .msg-search__input input:focus { outline: none; border-color: #c4b5fd; box-shadow: 0 0 0 3px rgba(167,139,250,.18); background: #fff; }
        .msg-new {
            width: 2.25rem; height: 2.25rem; display: inline-flex; align-items: center; justify-content: center;
            background: #6366f1; color: #fff; border: 0; border-radius: .5rem; cursor: pointer;
        }
        .msg-new svg { width: 1.1rem; height: 1.1rem; }

        .msg-tabs { display: inline-flex; background: #f3f4f6; padding: .2rem; border-radius: 999px; align-self: flex-start; gap: .15rem; }
        .msg-tabs button {
            background: transparent; border: 0; padding: .35rem .75rem;
            font-size: .78rem; font-weight: 500; color: #6b7280;
            border-radius: 999px; cursor: pointer;
        }
        .msg-tabs button.is-active { background: #fff; color: #111827; box-shadow: 0 1px 2px rgba(0,0,0,.06); }

        .msg-list { flex: 1; overflow-y: auto; padding: .25rem .5rem .5rem; }
        .msg-list__item {
            display: grid; grid-template-columns: 2.5rem 1fr auto; gap: .65rem; align-items: flex-start;
            padding: .75rem .65rem;
            border-radius: .6rem;
            cursor: pointer;
            border: 1px solid transparent;
        }
        .msg-list__item:hover { background: #f9fafb; }
        .msg-list__item.is-active { background: #eef2ff; border-color: #e0e7ff; }
        .msg-list__item .msg-avatar { margin-top: .15rem; }
        .msg-list__name { font-size: .85rem; font-weight: 600; color: #111827; }
        .msg-list__role { font-size: .72rem; color: #9ca3af; margin-top: .1rem; }
        .msg-list__preview { font-size: .78rem; color: #6b7280; margin-top: .25rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .msg-list__meta { display: flex; flex-direction: column; align-items: flex-end; gap: .35rem; flex-shrink: 0; }
        .msg-list__stamp { font-size: .7rem; color: #9ca3af; }
        .msg-list__dot { width: .55rem; height: .55rem; background: #2563eb; border-radius: 999px; }

        .msg-avatar {
            width: 2.4rem; height: 2.4rem; border-radius: 999px;
            display: inline-flex; align-items: center; justify-content: center;
            color: #fff; font-size: .75rem; font-weight: 700;
            flex-shrink: 0;
        }
        .msg-avatar--emerald { background: #10b981; }
        .msg-avatar--blue    { background: #3b82f6; }
        .msg-avatar--rose    { background: #f43f5e; }
        .msg-avatar--violet  { background: #8b5cf6; }
        .msg-avatar--amber   { background: #f59e0b; }
        .msg-avatar--slate   { background: #64748b; }
        .msg-avatar--gray    { background: #9ca3af; }

        /* ---------- RIGHT: thread view ---------- */
        .msg-thread { display: flex; flex-direction: column; min-height: 0; }
        .msg-thread__head {
            display: flex; align-items: center; gap: .75rem;
            padding: 1rem 1.25rem; border-bottom: 1px solid #f3f4f6;
        }
        .msg-thread__title { font-size: .9rem; font-weight: 700; color: #111827; }
        .msg-thread__sub { font-size: .78rem; color: #6b7280; margin-top: .1rem; }
        .msg-thread__menu {
            margin-left: auto;
            width: 2rem; height: 2rem;
            display: inline-flex; align-items: center; justify-content: center;
            color: #9ca3af; background: transparent; border: 0; border-radius: .4rem; cursor: pointer;
        }
        .msg-thread__menu:hover { background: #f3f4f6; color: #6b7280; }

        .msg-thread__body {
            flex: 1; overflow-y: auto;
            padding: 1.25rem;
            display: flex; flex-direction: column; gap: 1rem;
            background: #fafafa;
        }
        .msg-row { display: flex; gap: .6rem; align-items: flex-end; }
        .msg-row--me { justify-content: flex-end; }
        .msg-bubble {
            max-width: 70%;
            padding: .65rem .85rem;
            border-radius: 1rem;
            font-size: .85rem;
            line-height: 1.45;
            color: #111827;
            background: #fff;
            border: 1px solid #f3f4f6;
            box-shadow: 0 1px 2px rgba(0,0,0,.02);
        }
        .msg-bubble--me {
            background: #dbeafe;
            color: #1e3a8a;
            border-color: #bfdbfe;
        }
        .msg-stamp { font-size: .68rem; color: #9ca3af; margin-top: .25rem; padding: 0 .25rem; }
        .msg-row--me .msg-bubble-wrap { display: flex; flex-direction: column; align-items: flex-end; }
        .msg-row .msg-bubble-wrap { display: flex; flex-direction: column; }

        .msg-thread__foot {
            padding: .85rem 1rem;
            border-top: 1px solid #f3f4f6;
            background: #fff;
        }
        .msg-composer {
            display: grid; grid-template-columns: 1fr auto; gap: .5rem;
            align-items: end;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: .75rem;
            padding: .4rem .45rem .4rem .9rem;
            transition: border-color .15s, box-shadow .15s;
        }
        .msg-composer:focus-within {
            border-color: #c4b5fd;
            box-shadow: 0 0 0 3px rgba(167,139,250,.18);
        }
        .msg-composer textarea {
            background: transparent;
            border: 0;
            outline: 0;
            box-shadow: none;
            resize: none;
            width: 100%;
            font-size: .875rem;
            line-height: 1.45;
            color: #111827;
            padding: .55rem 0;
            min-height: 2.4rem;
            max-height: 8rem;
            font-family: inherit;
            display: block;
            overflow-y: auto;
        }
        .msg-composer textarea::placeholder { color: #9ca3af; }
        .msg-composer textarea:focus,
        .msg-composer textarea:focus-visible {
            outline: none !important;
            box-shadow: none !important;
            border: 0 !important;
            background: transparent !important;
        }
        .msg-composer textarea:disabled { opacity: .6; cursor: wait; }
        .msg-composer__actions { display: inline-flex; align-items: center; gap: .35rem; }
        .msg-icon-btn {
            width: 2rem; height: 2rem;
            background: transparent; border: 0; border-radius: .4rem; cursor: pointer;
            color: #9ca3af;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .msg-icon-btn:hover { background: #f3f4f6; color: #6b7280; }
        .msg-send-btn {
            width: 2.25rem; height: 2.25rem;
            background: #2563eb;
            color: #fff;
            border: 0; border-radius: .55rem; cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .msg-send-btn:hover { background: #1d4ed8; }
        .msg-send-btn svg { width: 1rem; height: 1rem; }

        .msg-empty { padding: 4rem 1rem; text-align: center; color: #9ca3af; font-size: .9rem; }

        /* ---------- Mentions ---------- */
        .msg-mention {
            display: inline-block;
            padding: 0 .35rem;
            background: #eef2ff;
            color: #4338ca;
            border-radius: .35rem;
            font-weight: 600;
            font-size: .82rem;
        }
        .msg-mention--everyone { background: #fff7ed; color: #c2410c; }
        .msg-bubble--me .msg-mention { background: rgba(255,255,255,.65); color: #1e3a8a; }
        .msg-bubble--me .msg-mention--everyone { background: #fff7ed; color: #c2410c; }
        .msg-bubble--system {
            background: transparent;
            color: #6b7280;
            border: 0;
            box-shadow: none;
            font-size: .75rem;
            font-style: italic;
            padding: .25rem .5rem;
        }

        /* ---------- Composer mention picker ---------- */
        .msg-mention-picker {
            position: absolute;
            bottom: 100%;
            left: 0;
            margin-bottom: .35rem;
            min-width: 240px;
            max-height: 220px;
            overflow-y: auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: .55rem;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,.12), 0 4px 6px -2px rgba(0,0,0,.05);
            padding: .3rem;
            z-index: 40;
        }
        .msg-mention-picker__item {
            display: flex; align-items: center; gap: .55rem;
            padding: .45rem .55rem;
            border-radius: .4rem;
            cursor: pointer;
            font-size: .82rem;
        }
        .msg-mention-picker__item:hover,
        .msg-mention-picker__item.is-active { background: #f3f4f6; }
        .msg-mention-picker__item .msg-avatar { width: 1.75rem; height: 1.75rem; font-size: .65rem; }
        .msg-mention-picker__name { font-weight: 600; color: #111827; }
        .msg-mention-picker__sub { font-size: .7rem; color: #9ca3af; }

        /* ---------- Participant stack (group header) ---------- */
        .msg-stack { display: inline-flex; align-items: center; margin-left: .5rem; }
        .msg-stack .msg-avatar {
            width: 1.6rem; height: 1.6rem; font-size: .6rem;
            border: 2px solid #fff;
            margin-left: -.45rem;
        }
        .msg-stack .msg-avatar:first-child { margin-left: 0; }
        .msg-stack__more {
            margin-left: -.45rem;
            width: 1.6rem; height: 1.6rem;
            border-radius: 999px;
            background: #e5e7eb; color: #4b5563;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: .6rem; font-weight: 700;
            border: 2px solid #fff;
        }

        /* ---------- Modal ---------- */
        .msg-modal-backdrop {
            position: fixed; inset: 0;
            background: rgba(15, 23, 42, .45);
            display: flex; align-items: center; justify-content: center;
            z-index: 70;
            padding: 1rem;
        }
        .msg-modal {
            background: #fff; border-radius: .85rem;
            width: 100%; max-width: 460px;
            max-height: 86vh; display: flex; flex-direction: column;
            box-shadow: 0 20px 40px rgba(0,0,0,.18);
            overflow: hidden;
        }
        .msg-modal__head {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1rem 1.15rem;
            border-bottom: 1px solid #f3f4f6;
        }
        .msg-modal__title { font-size: .95rem; font-weight: 700; color: #111827; }
        .msg-modal__body { padding: 1rem 1.15rem; overflow-y: auto; display: flex; flex-direction: column; gap: .85rem; }
        .msg-modal__foot {
            display: flex; justify-content: flex-end; gap: .5rem;
            padding: .85rem 1.15rem;
            border-top: 1px solid #f3f4f6;
            background: #fafafa;
        }
        .msg-modal label { font-size: .78rem; font-weight: 600; color: #374151; }
        .msg-modal input[type=text], .msg-modal select {
            width: 100%; padding: .55rem .7rem;
            border: 1px solid #e5e7eb; border-radius: .5rem;
            font-size: .85rem; color: #111827; background: #fff;
        }
        .msg-modal input[type=text]:focus, .msg-modal select:focus {
            outline: none; border-color: #c4b5fd; box-shadow: 0 0 0 3px rgba(167,139,250,.18);
        }
        .msg-toggle {
            display: inline-flex; align-items: center; gap: .5rem;
            font-size: .82rem; color: #374151;
        }
        .msg-member-list {
            border: 1px solid #f3f4f6; border-radius: .55rem;
            max-height: 240px; overflow-y: auto;
        }
        .msg-member-row {
            display: flex; align-items: center; gap: .6rem;
            padding: .55rem .7rem;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            font-size: .82rem;
        }
        .msg-member-row:last-child { border-bottom: 0; }
        .msg-member-row:hover { background: #f9fafb; }
        .msg-member-row.is-selected { background: #eef2ff; }
        .msg-member-row__main { flex: 1; min-width: 0; }
        .msg-member-row__name { font-weight: 600; color: #111827; }
        .msg-member-row__role { font-size: .7rem; color: #9ca3af; }
        .msg-member-row .msg-check {
            width: 1.1rem; height: 1.1rem; border: 1px solid #d1d5db; border-radius: .25rem;
            display: inline-flex; align-items: center; justify-content: center;
            color: #fff; background: #fff;
        }
        .msg-member-row.is-selected .msg-check { background: #6366f1; border-color: #6366f1; }
        .msg-btn {
            padding: .55rem 1rem; border-radius: .55rem; font-size: .82rem; font-weight: 600; cursor: pointer; border: 0;
        }
        .msg-btn--ghost { background: #f3f4f6; color: #374151; }
        .msg-btn--primary { background: #6366f1; color: #fff; }
        .msg-btn--primary:disabled { background: #c7d2fe; cursor: not-allowed; }
    </style>

    <div class="msg-shell">
        {{-- ===== LEFT: list ===== --}}
        <div class="msg-card">
            <div class="msg-list__head">
                <div class="msg-search">
                    <div class="msg-search__input">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="text" placeholder="Search" wire:model.live.debounce.300ms="search">
                    </div>
                    <button type="button" class="msg-new" title="New message" wire:click="openNew">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                    </button>
                </div>
                <div class="msg-tabs">
                    @foreach($tabs as $k => $label)
                        <button type="button"
                                class="{{ $category === $k ? 'is-active' : '' }}"
                                wire:click="setCategory('{{ $k }}')">{{ $label }}</button>
                    @endforeach
                </div>
            </div>

            <div class="msg-list">
                @forelse($threads as $t)
                    <div class="msg-list__item {{ $active && $active->id === $t->id ? 'is-active' : '' }}"
                         wire:click="selectThread({{ $t->id }})">
                        <span class="msg-avatar msg-avatar--{{ $t->partner_color }}">{{ $t->displayInitials() }}</span>
                        <div style="min-width:0;">
                            <div class="msg-list__name">
                                {{ $t->displayName() }}
                                @if($t->is_group)
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:.8rem;height:.8rem;color:#8b5cf6;display:inline;margin-left:.25rem;vertical-align:-2px;">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="msg-list__role">{{ $t->partner_role }}</div>
                            <div class="msg-list__preview">{{ $t->preview }}</div>
                        </div>
                        <div class="msg-list__meta">
                            <span class="msg-list__stamp">{{ $t->stamp }}</span>
                            @if($t->unread_count > 0)
                                <span class="msg-list__dot"></span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="msg-empty">No conversations.</div>
                @endforelse
            </div>
        </div>

        {{-- ===== RIGHT: thread ===== --}}
        <div class="msg-card msg-thread">
            @if($active)
                <div class="msg-thread__head">
                    <span class="msg-avatar msg-avatar--{{ $active->partner_color }}">{{ $active->displayInitials() }}</span>
                    <div style="min-width:0; flex:1;">
                        <div class="msg-thread__title">{{ $active->displayName() }}</div>
                        <div class="msg-thread__sub">
                            @if($active->is_group)
                                {{ $participants->count() ?: '0' }} members{{ $active->subject && $active->subject !== $active->displayName() ? ' · ' . $active->subject : '' }}
                            @else
                                {{ $active->partner_role }}{{ $active->subject ? ' · ' . $active->subject : '' }}
                            @endif
                        </div>
                    </div>
                    @if($active->is_group && $participants->count())
                        <div class="msg-stack">
                            @foreach($participants->take(4) as $p)
                                <span class="msg-avatar msg-avatar--{{ $p->color }}" title="{{ $p->name }}">{{ $p->initials ?: mb_strtoupper(mb_substr($p->name,0,2)) }}</span>
                            @endforeach
                            @if($participants->count() > 4)
                                <span class="msg-stack__more">+{{ $participants->count() - 4 }}</span>
                            @endif
                        </div>
                    @endif
                    <button type="button" class="msg-thread__menu" title="Add members" wire:click="openAdd">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:1.05rem;height:1.05rem;">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="8" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/>
                        </svg>
                    </button>
                    <button type="button" class="msg-thread__menu" title="More">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="12" cy="19" r="1.6"/></svg>
                    </button>
                </div>

                <div class="msg-thread__body" id="msg-scroll" wire:key="thread-{{ $active->id }}">
                    @php
                        $renderBody = function ($text) {
                            $escaped = e($text);
                            // Highlight @everyone and @Name mentions (names may contain spaces; supported up to 3 words).
                            $escaped = preg_replace(
                                '/@everyone\b/i',
                                '<span class="msg-mention msg-mention--everyone">@everyone</span>',
                                $escaped
                            );
                            $escaped = preg_replace(
                                '/@([A-Z][\p{L}\.\-]+(?:\s[A-Z][\p{L}\.\-]+){0,2})/u',
                                '<span class="msg-mention">@$1</span>',
                                $escaped
                            );
                            return $escaped;
                        };
                    @endphp
                    @foreach($messages as $m)
                        @if($m->sender_name === 'System')
                            <div class="msg-row" style="justify-content:center;">
                                <div class="msg-bubble msg-bubble--system">{{ $m->body }}</div>
                            </div>
                        @else
                            <div class="msg-row {{ $m->is_me ? 'msg-row--me' : '' }}">
                                @if(! $m->is_me)
                                    <span class="msg-avatar msg-avatar--{{ $active->partner_color }}" style="width:2rem;height:2rem;font-size:.7rem;">{{ $m->sender_initials ?? $active->partner_initials }}</span>
                                @endif
                                <div class="msg-bubble-wrap">
                                    @if($active->is_group && ! $m->is_me)
                                        <div style="font-size:.7rem;color:#6b7280;margin:0 .25rem .15rem;font-weight:600;">{{ $m->sender_name }}</div>
                                    @endif
                                    <div class="msg-bubble {{ $m->is_me ? 'msg-bubble--me' : '' }}">{!! $renderBody($m->body) !!}</div>
                                    <div class="msg-stamp">{{ optional($m->sent_at)->format('H:i') }}</div>
                                </div>
                                @if($m->is_me)
                                    <span class="msg-avatar msg-avatar--gray" style="width:2rem;height:2rem;font-size:.7rem;">SR</span>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="msg-thread__foot" style="position:relative;">
                    <form wire:submit.prevent="send" class="msg-composer"
                          x-data="messageComposer(@js($mentionables))"
                          x-init="init()"
                          @click.outside="close()"
                          @keydown.window.escape="close()"
                          style="position:relative;">
                        <template x-if="open && filtered.length">
                            <div class="msg-mention-picker">
                                <template x-for="(m, idx) in filtered" :key="m.key">
                                    <div class="msg-mention-picker__item"
                                         :class="{ 'is-active': idx === active }"
                                         @mouseenter="active = idx"
                                         @mousedown.prevent="pick(m)">
                                        <template x-if="m.key === 'everyone'">
                                            <span class="msg-avatar msg-avatar--amber">@</span>
                                        </template>
                                        <template x-if="m.key !== 'everyone'">
                                            <span class="msg-avatar msg-avatar--slate" x-text="initialsOf(m.label)"></span>
                                        </template>
                                        <div style="min-width:0;">
                                            <div class="msg-mention-picker__name" x-text="m.label"></div>
                                            <div class="msg-mention-picker__sub" x-text="m.sub"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <textarea wire:model="draft"
                                  placeholder="Write a message... (use @ to mention)"
                                  rows="1"
                                  x-ref="draft"
                                  x-on:input="onInput($event)"
                                  x-on:keydown="onKeydown($event)"></textarea>
                        <div class="msg-composer__actions">
                            <button type="button" class="msg-icon-btn" title="Mention"
                                    @click="triggerMention()">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:1.1rem;height:1.1rem;"><circle cx="12" cy="12" r="4"/><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-3.92 7.94"/></svg>
                            </button>
                            <button type="button" class="msg-icon-btn" title="Emoji">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:1.1rem;height:1.1rem;"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                            </button>
                            <button type="button" class="msg-icon-btn" title="Attach">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:1.1rem;height:1.1rem;"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                            </button>
                            <button type="submit" class="msg-send-btn" title="Send">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="msg-empty">Select a conversation to begin.</div>
            @endif
        </div>
    </div>

    {{-- ===== New chat / group modal ===== --}}
    @if($showNewModal)
        <div class="msg-modal-backdrop" wire:click.self="closeNew">
            <div class="msg-modal">
                <div class="msg-modal__head">
                    <div class="msg-modal__title">{{ $newIsGroup ? 'New group chat' : 'New conversation' }}</div>
                    <button type="button" class="msg-icon-btn" wire:click="closeNew" title="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:1.05rem;height:1.05rem;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div class="msg-modal__body">
                    <label class="msg-toggle">
                        <input type="checkbox" wire:model.live="newIsGroup">
                        Create as group chat
                    </label>
                    @if($newIsGroup)
                        <div>
                            <label>Group name</label>
                            <input type="text" placeholder="e.g. Class 4A Parents" wire:model.live="newGroupName">
                        </div>
                    @endif
                    <div>
                        <label>Category</label>
                        <select wire:model.live="newCategory">
                            <option value="parent">Parents</option>
                            <option value="students">Students</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    <div>
                        <label>Add members ({{ count($newMembers) }} selected)</label>
                        <div class="msg-member-list">
                            @foreach($directory as $p)
                                @php $sel = in_array($p['name'], $newMembers, true); @endphp
                                <div class="msg-member-row {{ $sel ? 'is-selected' : '' }}"
                                     wire:click="toggleNewMember(@js($p['name']))">
                                    <span class="msg-avatar msg-avatar--{{ $p['color'] }}" style="width:1.9rem;height:1.9rem;font-size:.65rem;">{{ mb_strtoupper(mb_substr($p['name'],0,1) . mb_substr(explode(' ',$p['name'])[1] ?? '',0,1)) }}</span>
                                    <div class="msg-member-row__main">
                                        <div class="msg-member-row__name">{{ $p['name'] }}</div>
                                        <div class="msg-member-row__role">{{ $p['role'] }}</div>
                                    </div>
                                    <span class="msg-check">
                                        @if($sel)
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="width:.75rem;height:.75rem;"><polyline points="20 6 9 17 4 12"/></svg>
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="msg-modal__foot">
                    <button type="button" class="msg-btn msg-btn--ghost" wire:click="closeNew">Cancel</button>
                    <button type="button" class="msg-btn msg-btn--primary" wire:click="createThread" @if(empty($newMembers)) disabled @endif>
                        {{ $newIsGroup ? 'Create group' : 'Start conversation' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== Add members modal ===== --}}
    @if($showAddModal && $active)
        <div class="msg-modal-backdrop" wire:click.self="closeAdd">
            <div class="msg-modal">
                <div class="msg-modal__head">
                    <div class="msg-modal__title">Add members to “{{ $active->displayName() }}”</div>
                    <button type="button" class="msg-icon-btn" wire:click="closeAdd" title="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:1.05rem;height:1.05rem;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div class="msg-modal__body">
                    @if(! $active->is_group)
                        <p style="font-size:.78rem;color:#6b7280;margin:0;">Adding members will turn this conversation into a group chat.</p>
                    @endif
                    <div>
                        <label>People ({{ count($addMembers) }} selected)</label>
                        @php $existingNames = $participants->pluck('name')->all(); @endphp
                        <div class="msg-member-list">
                            @foreach($directory as $p)
                                @php
                                    $alreadyIn = in_array($p['name'], $existingNames, true)
                                        || (! $active->is_group && $p['name'] === $active->partner_name);
                                    $sel = in_array($p['name'], $addMembers, true);
                                @endphp
                                <div class="msg-member-row {{ $sel ? 'is-selected' : '' }}"
                                     style="{{ $alreadyIn ? 'opacity:.45;pointer-events:none;' : '' }}"
                                     wire:click="toggleAddMember(@js($p['name']))">
                                    <span class="msg-avatar msg-avatar--{{ $p['color'] }}" style="width:1.9rem;height:1.9rem;font-size:.65rem;">{{ mb_strtoupper(mb_substr($p['name'],0,1) . mb_substr(explode(' ',$p['name'])[1] ?? '',0,1)) }}</span>
                                    <div class="msg-member-row__main">
                                        <div class="msg-member-row__name">{{ $p['name'] }}{{ $alreadyIn ? ' (already in)' : '' }}</div>
                                        <div class="msg-member-row__role">{{ $p['role'] }}</div>
                                    </div>
                                    <span class="msg-check">
                                        @if($sel)
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="width:.75rem;height:.75rem;"><polyline points="20 6 9 17 4 12"/></svg>
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="msg-modal__foot">
                    <button type="button" class="msg-btn msg-btn--ghost" wire:click="closeAdd">Cancel</button>
                    <button type="button" class="msg-btn msg-btn--primary" wire:click="addMembersToThread" @if(empty($addMembers)) disabled @endif>
                        Add to chat
                    </button>
                </div>
            </div>
        </div>
    @endif

    <script>
        const msgScrollToBottom = () => {
            const el = document.getElementById('msg-scroll');
            if (el) {
                requestAnimationFrame(() => requestAnimationFrame(() => {
                    el.scrollTop = el.scrollHeight;
                }));
            }
        };
        const msgRefocus = () => {
            const ta = document.querySelector('.msg-composer textarea');
            if (ta) { ta.style.height = 'auto'; ta.focus(); }
        };
        document.addEventListener('livewire:navigated', () => { msgScrollToBottom(); msgRefocus(); });
        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updated', () => msgScrollToBottom());
            Livewire.on('msg-sent', () => { msgScrollToBottom(); msgRefocus(); });
            Livewire.on('msg-thread-changed', () => { msgScrollToBottom(); msgRefocus(); });
        });
        window.addEventListener('load', () => { msgScrollToBottom(); msgRefocus(); });

        // ----- Composer + mention picker -----
        window.messageComposer = (mentionables) => ({
            mentionables,
            open: false,
            query: '',
            anchor: 0,
            active: 0,
            get filtered() {
                const q = (this.query || '').toLowerCase();
                return this.mentionables.filter(m => m.label.toLowerCase().includes(q)).slice(0, 8);
            },
            init() {
                this.$nextTick(() => {
                    const t = this.$refs.draft;
                    if (t) { this.autosize(t); t.focus(); }
                });
            },
            autosize(el) {
                el.style.height = 'auto';
                el.style.height = Math.min(el.scrollHeight, 128) + 'px';
            },
            onInput(e) {
                this.autosize(e.target);
                const ta = e.target;
                const pos = ta.selectionStart;
                const upto = ta.value.slice(0, pos);
                const match = upto.match(/(^|\s)@([\p{L}\.\-]*)$/u);
                if (match) {
                    this.open = true;
                    this.anchor = pos - match[2].length - 1; // index of '@'
                    this.query = match[2];
                    this.active = 0;
                } else {
                    this.open = false;
                }
            },
            onKeydown(e) {
                if (this.open && this.filtered.length) {
                    if (e.key === 'ArrowDown') { e.preventDefault(); this.active = (this.active + 1) % this.filtered.length; return; }
                    if (e.key === 'ArrowUp')   { e.preventDefault(); this.active = (this.active - 1 + this.filtered.length) % this.filtered.length; return; }
                    if (e.key === 'Enter' || e.key === 'Tab') {
                        e.preventDefault();
                        this.pick(this.filtered[this.active]);
                        return;
                    }
                    if (e.key === 'Escape') { this.open = false; e.preventDefault(); return; }
                }
                if (e.key === 'Enter' && ! e.shiftKey && ! this.open) {
                    e.preventDefault();
                    this.$el.requestSubmit();
                }
            },
            pick(m) {
                const ta = this.$refs.draft;
                const before = ta.value.slice(0, this.anchor);
                const afterStart = this.anchor + 1 + this.query.length;
                const after = ta.value.slice(afterStart);
                const insert = '@' + m.label + ' ';
                ta.value = before + insert + after;
                const pos = (before + insert).length;
                ta.setSelectionRange(pos, pos);
                ta.dispatchEvent(new Event('input', { bubbles: true }));
                this.open = false;
                ta.focus();
            },
            triggerMention() {
                const ta = this.$refs.draft;
                const pos = ta.selectionStart;
                const before = ta.value.slice(0, pos);
                const after = ta.value.slice(pos);
                const prefix = (pos === 0 || /\s$/.test(before)) ? '@' : ' @';
                ta.value = before + prefix + after;
                const newPos = before.length + prefix.length;
                ta.setSelectionRange(newPos, newPos);
                ta.dispatchEvent(new Event('input', { bubbles: true }));
                ta.focus();
                this.open = true;
                this.query = '';
                this.anchor = newPos - 1;
                this.active = 0;
            },
            initialsOf(name) {
                const p = (name || '').trim().split(/\s+/);
                return ((p[0]?.[0] || '') + (p[1]?.[0] || '')).toUpperCase();
            },
            close() { this.open = false; },
        });
    </script>
</x-filament-panels::page>
