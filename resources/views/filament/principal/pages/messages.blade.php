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
                    <button type="button" class="msg-new" title="New message">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
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
                        <span class="msg-avatar msg-avatar--{{ $t->partner_color }}">{{ $t->partner_initials }}</span>
                        <div style="min-width:0;">
                            <div class="msg-list__name">{{ $t->partner_name }}</div>
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
                    <span class="msg-avatar msg-avatar--{{ $active->partner_color }}">{{ $active->partner_initials }}</span>
                    <div>
                        <div class="msg-thread__title">{{ $active->partner_name }}</div>
                        <div class="msg-thread__sub">{{ $active->partner_role }}{{ $active->subject ? ' · ' . $active->subject : '' }}</div>
                    </div>
                    <button type="button" class="msg-thread__menu" title="More">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="12" cy="19" r="1.6"/></svg>
                    </button>
                </div>

                <div class="msg-thread__body" id="msg-scroll" wire:key="thread-{{ $active->id }}">
                    @foreach($messages as $m)
                        <div class="msg-row {{ $m->is_me ? 'msg-row--me' : '' }}">
                            @if(! $m->is_me)
                                <span class="msg-avatar msg-avatar--{{ $active->partner_color }}" style="width:2rem;height:2rem;font-size:.7rem;">{{ $m->sender_initials ?? $active->partner_initials }}</span>
                            @endif
                            <div class="msg-bubble-wrap">
                                <div class="msg-bubble {{ $m->is_me ? 'msg-bubble--me' : '' }}">{{ $m->body }}</div>
                                <div class="msg-stamp">{{ optional($m->sent_at)->format('H:i') }}</div>
                            </div>
                            @if($m->is_me)
                                <span class="msg-avatar msg-avatar--gray" style="width:2rem;height:2rem;font-size:.7rem;">SR</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="msg-thread__foot">
                    <form wire:submit.prevent="send" class="msg-composer"
                          x-data="{
                              autosize(el) {
                                  el.style.height = 'auto';
                                  el.style.height = Math.min(el.scrollHeight, 128) + 'px';
                              }
                          }"
                          x-init="$nextTick(() => { const t = $el.querySelector('textarea'); if (t) { autosize(t); t.focus(); } })">
                        <textarea wire:model="draft"
                                  placeholder="Write a message..."
                                  rows="1"
                                  x-ref="draft"
                                  x-on:input="autosize($event.target)"
                                  x-on:keydown.enter="if (! $event.shiftKey) { $event.preventDefault(); $el.form.requestSubmit(); }"></textarea>
                        <div class="msg-composer__actions">
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

    <script>
        const msgScrollToBottom = () => {
            const el = document.getElementById('msg-scroll');
            if (el) {
                // double-rAF to wait for Livewire DOM patch
                requestAnimationFrame(() => requestAnimationFrame(() => {
                    el.scrollTop = el.scrollHeight;
                }));
            }
        };
        const msgRefocus = () => {
            const ta = document.querySelector('.msg-composer textarea');
            if (ta) {
                ta.style.height = 'auto';
                ta.focus();
            }
        };
        document.addEventListener('livewire:navigated', () => { msgScrollToBottom(); msgRefocus(); });
        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updated', () => msgScrollToBottom());
            Livewire.on('msg-sent', () => { msgScrollToBottom(); msgRefocus(); });
            Livewire.on('msg-thread-changed', () => { msgScrollToBottom(); msgRefocus(); });
        });
        window.addEventListener('load', () => { msgScrollToBottom(); msgRefocus(); });
    </script>
</x-filament-panels::page>
