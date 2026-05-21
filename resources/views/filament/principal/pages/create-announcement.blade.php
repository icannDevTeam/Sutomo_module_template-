<x-filament-panels::page>
    @php
        $groupOptions = $this->getGroupOptions();
        $recipientCount = $this->recipientCount();
    @endphp

    <div class="sp-ca"
         x-data="{
            title: @entangle('title_').live,
            body:  @entangle('body').live,
            charCount: 0,
            previewMode: 'web',
            init() {
                this.charCount = (this.title || '').length;
                this.$refs.editor.innerHTML = this.body || '';
                this.$watch('title', v => { this.charCount = (v || '').length; });
            },
            syncBody() { this.body = this.$refs.editor.innerHTML; },
            exec(cmd, val=null) { document.execCommand(cmd, false, val); this.syncBody(); this.$refs.editor.focus(); },
            insertEmoji(emoji) { this.exec('insertText', emoji); },
         }">

        {{-- Top bar --}}
        <header class="sp-ca__topbar">
            <a href="{{ \App\Filament\Principal\Pages\Announcements::getUrl() }}" class="sp-ca__back">
                <x-filament::icon icon="heroicon-m-chevron-left" class="w-4 h-4" />
                <span>{{ $record ? 'Edit announcement' : 'Create announcement' }}</span>
            </a>

            <div class="sp-ca__topbar-right">
                <div class="sp-ca__publish-group" x-data="{ menu: false }" @click.outside="menu = false">
                    <button type="button" wire:click="publish" wire:loading.attr="disabled" class="sp-ca__publish-btn">
                        <x-filament::icon icon="heroicon-s-paper-airplane" class="w-4 h-4" />
                        <span>{{ $scheduledAt ? 'Schedule' : ($record ? 'Update' : 'Publish') }}</span>
                    </button>
                    <button type="button" class="sp-ca__publish-caret" @click="menu = !menu" title="More options">
                        <x-filament::icon icon="heroicon-m-chevron-down" class="w-3.5 h-3.5" />
                    </button>
                    <div class="sp-ca__publish-menu" x-show="menu" x-cloak x-transition.origin.top.right>
                        <button type="button" class="sp-ca__publish-menu-item" @click="menu = false" wire:click="saveDraft">
                            <x-filament::icon icon="heroicon-m-bookmark" class="w-4 h-4" />
                            <span>
                                <strong>Save as draft</strong>
                                <em>Keep editing later — nothing is sent.</em>
                            </span>
                        </button>
                        <button type="button" class="sp-ca__publish-menu-item" @click="menu = false" wire:click="publish">
                            <x-filament::icon icon="heroicon-m-paper-airplane" class="w-4 h-4" />
                            <span>
                                <strong>{{ $scheduledAt ? 'Schedule for later' : 'Publish now' }}</strong>
                                <em>{{ $scheduledAt ? 'Will be sent at the chosen time.' : 'Send to selected recipients right away.' }}</em>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <div class="sp-ca__shell">

            {{-- ============ CANVAS ============ --}}
            <main class="sp-ca__canvas">

                {{-- Banner --}}
                <div class="sp-ca__banner" x-data="{ open: false }">
                    @if ($bannerEmoji)
                        <div class="sp-ca__banner-img">
                            <span class="sp-ca__banner-emoji">{{ $bannerEmoji }}</span>
                            <button type="button" class="sp-ca__banner-remove" wire:click="$set('bannerEmoji', null)">
                                <x-filament::icon icon="heroicon-m-x-mark" class="w-3.5 h-3.5" />
                            </button>
                        </div>
                    @else
                        <button type="button" class="sp-ca__banner-add" @click="open = !open">
                            <x-filament::icon icon="heroicon-m-photo" class="w-4 h-4" />
                            Add a banner image
                        </button>
                    @endif
                    <div class="sp-ca__banner-pop" x-show="open" x-cloak @click.outside="open = false">
                        @foreach ($bannerEmojiPalette as $e)
                            <button type="button" class="sp-ca__banner-pop-btn"
                                wire:click="$set('bannerEmoji', @js($e))" @click="open = false">{{ $e }}</button>
                        @endforeach
                    </div>
                </div>

                {{-- Title --}}
                <div class="sp-ca__title-wrap" wire:ignore>
                    <input type="text"
                        x-model="title"
                        class="sp-ca__title-input"
                        placeholder="Announcement title*"
                        maxlength="100" />
                    <span class="sp-ca__title-counter">
                        <span class="sp-ca__title-counter-dot" :class="charCount >= 1 ? 'is-on' : ''">
                            <x-filament::icon icon="heroicon-s-check" class="w-3 h-3" />
                        </span>
                        <span x-text="charCount + '/100'"></span>
                    </span>
                </div>
                @error('title_') <div class="sp-ca__error">{{ $message }}</div> @enderror

                {{-- Rich text editor --}}
                <div class="sp-ca__editor-card">
                    <div class="sp-ca__toolbar">
                        <button type="button" class="sp-ca__tb-btn" @click="exec('formatBlock','P')"><span class="sp-ca__tb-text">A</span><x-filament::icon icon="heroicon-m-chevron-down" class="w-3 h-3" /></button>
                        <span class="sp-ca__tb-sep"></span>
                        <button type="button" class="sp-ca__tb-btn" @click="exec('bold')" title="Bold"><strong>B</strong></button>
                        <button type="button" class="sp-ca__tb-btn" @click="exec('italic')" title="Italic"><em>I</em></button>
                        <button type="button" class="sp-ca__tb-btn" @click="exec('underline')" title="Underline"><span style="text-decoration:underline">U</span></button>
                        <button type="button" class="sp-ca__tb-btn sp-ca__tb-color" @click="exec('foreColor', '#dc2626')" title="Color"><span class="sp-ca__tb-color-dot"></span><x-filament::icon icon="heroicon-m-chevron-down" class="w-3 h-3" /></button>
                        <span class="sp-ca__tb-sep"></span>
                        <button type="button" class="sp-ca__tb-btn" @click="exec('justifyLeft')" title="Align"><x-filament::icon icon="heroicon-m-bars-3-bottom-left" class="w-4 h-4" /></button>
                        <button type="button" class="sp-ca__tb-btn" @click="exec('insertUnorderedList')" title="Bullets"><x-filament::icon icon="heroicon-m-list-bullet" class="w-4 h-4" /></button>
                        <span class="sp-ca__tb-sep"></span>
                        <button type="button" class="sp-ca__tb-btn" @click="const u = prompt('Link URL'); if(u) exec('createLink', u);" title="Link"><x-filament::icon icon="heroicon-m-link" class="w-4 h-4" /></button>
                        <button type="button" class="sp-ca__tb-btn" @click="insertEmoji('😀')" title="Emoji">😊</button>
                        <button type="button" class="sp-ca__tb-btn" title="Equation">√x̄</button>
                        <button type="button" class="sp-ca__tb-btn" title="Table"><x-filament::icon icon="heroicon-m-table-cells" class="w-4 h-4" /></button>
                        <button type="button" class="sp-ca__tb-btn" title="Upload"><x-filament::icon icon="heroicon-m-arrow-up-tray" class="w-4 h-4" /><x-filament::icon icon="heroicon-m-chevron-down" class="w-3 h-3" /></button>
                        <span class="sp-ca__tb-spacer"></span>
                        <button type="button" class="sp-ca__tb-btn sp-ca__tb-ai" title="AI assist">
                            <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4" stroke="currentColor" stroke-width="2.2"><path d="M12 3v3M12 18v3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M3 12h3M18 12h3M5.6 18.4l2.1-2.1M16.3 7.7l2.1-2.1"/></svg>
                        </button>
                    </div>
                    <div class="sp-ca__editor"
                        x-ref="editor"
                        wire:ignore
                        contenteditable="true"
                        @input="syncBody()"
                        @blur="syncBody()"
                        data-placeholder='Type "/" for AI assist or start typing…'></div>
                </div>
                @error('body') <div class="sp-ca__error">{{ $message }}</div> @enderror

                {{-- Attachments --}}
                <div class="sp-ca__attach">
                    <span class="sp-ca__attach-label">Attachments</span>
                    <button type="button" class="sp-ca__attach-btn" title="From device"><x-filament::icon icon="heroicon-m-computer-desktop" class="w-4 h-4" /></button>
                    <button type="button" class="sp-ca__attach-btn" title="Link"><x-filament::icon icon="heroicon-m-link" class="w-4 h-4" /></button>
                    <button type="button" class="sp-ca__attach-btn sp-ca__attach-btn--gdrive" title="Google Drive">▲</button>
                    <button type="button" class="sp-ca__attach-btn sp-ca__attach-btn--onedrive" title="OneDrive">☁</button>
                    <button type="button" class="sp-ca__attach-btn sp-ca__attach-btn--ms" title="Microsoft 365">■</button>
                    <button type="button" class="sp-ca__attach-btn" title="Voice note"><x-filament::icon icon="heroicon-m-microphone" class="w-4 h-4" /></button>
                </div>

                {{-- Recipients --}}
                <section class="sp-ca__recip">
                    <h3 class="sp-ca__recip-title">Who should receive this announcement?</h3>

                    <div class="sp-ca__recip-block">
                        <div class="sp-ca__recip-h">Select from recents</div>
                        <div class="sp-ca__chips">
                            @foreach ($recents as $r)
                                <button type="button" class="sp-ca__chip" wire:click="pickRecent(@js($r))">
                                    <span class="sp-ca__chip-icon"><x-filament::icon icon="heroicon-s-user-group" class="w-3.5 h-3.5" /></span>
                                    <span class="sp-ca__chip-text">{{ \Illuminate\Support\Str::limit($r, 36) }}</span>
                                </button>
                            @endforeach
                            <button type="button" class="sp-ca__chip sp-ca__chip--more">…</button>
                        </div>
                    </div>

                    <div class="sp-ca__recip-block">
                        <div class="sp-ca__recip-h">Select recipient type</div>
                        <div class="sp-ca__opts">
                            @foreach ([
                                'all_members' => 'All members',
                                'staff'       => 'Staff members',
                                'students'    => 'Students',
                                'family'      => 'Family members',
                            ] as $key => $label)
                                <label class="sp-ca__opt">
                                    <input type="checkbox" class="sp-ca__opt-cb"
                                        wire:click="toggleType('{{ $key }}')"
                                        @checked($recipientTypes[$key] ?? false) />
                                    <span class="sp-ca__opt-box"></span>
                                    <span class="sp-ca__opt-label">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="sp-ca__recip-block">
                        <div class="sp-ca__recip-h">Select recipient group</div>
                        <div class="sp-ca__opts">
                            @foreach (['programmes' => 'Programmes', 'grades' => 'Grades', 'classes' => 'Classes', 'custom' => 'Custom'] as $key => $label)
                                <label class="sp-ca__opt">
                                    <input type="radio" name="recipientGroup" class="sp-ca__opt-rb"
                                        wire:click="setGroup('{{ $key }}')"
                                        @checked($recipientGroup === $key) />
                                    <span class="sp-ca__opt-radio"></span>
                                    <span class="sp-ca__opt-label">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    @if ($recipientGroup === 'custom')
                        @php $customTotal = $this->customTotal(); @endphp
                        <div class="sp-ca__custom-summary">
                            <div class="sp-ca__custom-summary-left">
                                <x-filament::icon icon="heroicon-s-adjustments-horizontal" class="w-5 h-5" />
                                <div>
                                    <strong>Custom audience</strong>
                                    <span>{{ $customTotal }} reference{{ $customTotal === 1 ? '' : 's' }} across teachers, classes, students, campus, unit &amp; stream.</span>
                                </div>
                            </div>
                            <div class="sp-ca__custom-summary-actions">
                                @if ($customTotal > 0)
                                    <button type="button" class="sp-ca__btn sp-ca__btn--ghost" wire:click="clearAllCustom">
                                        <x-filament::icon icon="heroicon-m-trash" class="w-4 h-4" />
                                        Clear
                                    </button>
                                @endif
                                <button type="button" class="sp-ca__btn sp-ca__btn--primary" wire:click="openCustom">
                                    <x-filament::icon icon="heroicon-m-pencil-square" class="w-4 h-4" />
                                    {{ $customTotal > 0 ? 'Edit selection' : 'Pick recipients' }}
                                </button>
                            </div>
                        </div>

                        @if ($customTotal > 0)
                            <div class="sp-ca__chips sp-ca__chips--selected">
                                @foreach ($customPicked as $bucket => $ids)
                                    @foreach ($ids as $id)
                                        @php $lbl = $customLabels[$bucket.':'.$id] ?? $id; @endphp
                                        <span class="sp-ca__pill sp-ca__pill--{{ $bucket }}">
                                            <span class="sp-ca__pill-tag">{{ ucfirst($bucket) }}</span>
                                            {{ $lbl }}
                                            <button type="button" wire:click="toggleCustomItem('{{ $bucket }}', @js($id), @js($lbl))"><x-filament::icon icon="heroicon-m-x-mark" class="w-3 h-3" /></button>
                                        </span>
                                    @endforeach
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="sp-ca__recip-add" x-data="{ open: false }">
                            <button type="button" class="sp-ca__recip-add-btn" @click="open = !open">
                                <x-filament::icon icon="heroicon-m-plus" class="w-4 h-4" />
                                Add {{ $recipientGroup }}
                            </button>
                            <span class="sp-ca__recip-add-count">{{ count($selectedGroups) }} {{ \Illuminate\Support\Str::plural(rtrim($recipientGroup, 's'), count($selectedGroups)) }} selected</span>

                            <div class="sp-ca__recip-pop" x-show="open" x-cloak @click.outside="open = false">
                                @forelse ($groupOptions as $opt)
                                    <button type="button" class="sp-ca__recip-pop-item {{ in_array($opt, $selectedGroups) ? 'is-active' : '' }}"
                                        wire:click="pickGroup(@js($opt))">
                                        <span class="sp-ca__recip-pop-check">
                                            @if (in_array($opt, $selectedGroups))
                                                <x-filament::icon icon="heroicon-s-check" class="w-3.5 h-3.5" />
                                            @endif
                                        </span>
                                        {{ $opt }}
                                    </button>
                                @empty
                                    <div class="sp-ca__recip-pop-empty">No options</div>
                                @endforelse
                            </div>
                        </div>

                        @if (! empty($selectedGroups))
                            <div class="sp-ca__chips sp-ca__chips--selected">
                                @foreach ($selectedGroups as $g)
                                    <span class="sp-ca__pill">
                                        {{ $g }}
                                        <button type="button" wire:click="pickGroup(@js($g))"><x-filament::icon icon="heroicon-m-x-mark" class="w-3 h-3" /></button>
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </section>

                {{-- Meta strip --}}
                <section class="sp-ca__meta">
                    <div class="sp-ca__meta-cell">
                        <label class="sp-ca__meta-label">Category</label>
                        <select wire:model.live="category" class="sp-ca__meta-select">
                            @foreach (\App\Models\Announcement::CATEGORIES as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sp-ca__meta-cell">
                        <label class="sp-ca__meta-label">Delivery channels</label>
                        <div class="sp-ca__meta-channels">
                            @foreach (\App\Models\Announcement::CHANNELS as $k => $v)
                                <button type="button"
                                    wire:click="toggleChannel('{{ $k }}')"
                                    class="sp-ca__chan {{ ($channels[$k] ?? false) ? 'is-on' : '' }}">
                                    @if ($channels[$k] ?? false) <x-filament::icon icon="heroicon-s-check" class="w-3 h-3" /> @endif
                                    {{ $v }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div class="sp-ca__meta-cell">
                        <label class="sp-ca__meta-label">Schedule for later</label>
                        <input type="datetime-local" wire:model="scheduledAt" class="sp-ca__meta-input" />
                    </div>
                    <div class="sp-ca__meta-cell sp-ca__meta-cell--toggle">
                        <label class="sp-ca__meta-label">Pin to top</label>
                        <button type="button" wire:click="$toggle('pinned')" class="sp-ca__toggle {{ $pinned ? 'is-on' : '' }}">
                            <span></span>
                        </button>
                    </div>
                </section>
            </main>

            {{-- ============ PREVIEW PANE ============ --}}
            <aside class="sp-ca__preview">
                <div class="sp-ca__preview-tabs">
                    <button class="sp-ca__preview-tab is-active">
                        <span class="sp-ca__preview-tab-dot"></span>
                        Live preview
                    </button>
                    <div class="sp-ca__preview-modes">
                        <button type="button" @click="previewMode='web'"    :class="previewMode==='web'    ? 'is-active' : ''">Web</button>
                        <button type="button" @click="previewMode='tablet'" :class="previewMode==='tablet' ? 'is-active' : ''">Tablet</button>
                        <button type="button" @click="previewMode='mobile'" :class="previewMode==='mobile' ? 'is-active' : ''">Mobile</button>
                    </div>
                </div>

                <div class="sp-ca__preview-body" :class="'sp-ca__preview-body--' + previewMode">
                    <div class="sp-ca__preview-frame" :class="'sp-ca__preview-frame--' + previewMode">
                    <article class="sp-ca__preview-card" x-show="title || body" x-cloak>
                        @if ($bannerEmoji)
                            <div class="sp-ca__preview-banner">{{ $bannerEmoji }}</div>
                        @endif
                        <header class="sp-ca__preview-meta">
                            <span class="sp-ca__preview-avatar">PD</span>
                            <div>
                                <div class="sp-ca__preview-author">{{ auth()->user()?->name ?? 'Pak Dwi' }}</div>
                                <div class="sp-ca__preview-role">Principal · {{ \App\Models\Announcement::CATEGORIES[$category] ?? '' }}</div>
                            </div>
                        </header>
                        <h2 class="sp-ca__preview-title" x-show="title" x-text="title"></h2>
                        <div class="sp-ca__preview-rich" x-html="body || '<em style=&quot;color:#94a3b8&quot;>Start writing to see preview…</em>'"></div>
                        <footer class="sp-ca__preview-footer">
                            <span>{{ number_format($recipientCount) }} recipients</span>
                            <span>{{ collect($this->getChannelsPayload())->map(fn($c)=>\App\Models\Announcement::CHANNELS[$c]??$c)->implode(' · ') }}</span>
                        </footer>
                    </article>
                    <div class="sp-ca__preview-empty" x-show="!title && !body">
                        <div class="sp-ca__preview-empty-art">
                            <span style="background:#ffe4e6">📒</span>
                            <span style="background:#fef3c7">📎</span>
                            <span style="background:#dcfce7">✏️</span>
                        </div>
                        <strong>No preview available</strong>
                        <p>Start adding content to your announcement to see a preview</p>
                    </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    {{-- ============ CUSTOM AUDIENCE MODAL ============ --}}
    @if ($customOpen)
        @php
            $tabs    = $this->customTabs();
            $options = $this->customOptions();
            $picked  = collect($customPicked[$customTab] ?? [])->map(fn ($x) => (string) $x)->all();
            $total   = $this->customTotal();
        @endphp
        <div class="sp-ca__modal-backdrop" wire:click="closeCustom"></div>
        <div class="sp-ca__modal" role="dialog" aria-modal="true">
            <header class="sp-ca__modal-head">
                <div>
                    <h3>Pick custom audience</h3>
                    <p>Select teachers, classes, students, campuses, units and streams — alone or in combination.</p>
                </div>
                <button type="button" class="sp-ca__modal-close" wire:click="closeCustom" title="Close">
                    <x-filament::icon icon="heroicon-m-x-mark" class="w-5 h-5" />
                </button>
            </header>

            <div class="sp-ca__modal-body">
                <nav class="sp-ca__modal-tabs">
                    @foreach ($tabs as $key => [$label, $icon])
                        @php $bucketCount = count($customPicked[$key] ?? []); @endphp
                        <button type="button"
                            wire:click="setCustomTab('{{ $key }}')"
                            class="sp-ca__modal-tab {{ $customTab === $key ? 'is-active' : '' }}">
                            <x-filament::icon :icon="$icon" class="w-4 h-4" />
                            <span>{{ $label }}</span>
                            @if ($bucketCount > 0)
                                <span class="sp-ca__modal-tab-count">{{ $bucketCount }}</span>
                            @endif
                        </button>
                    @endforeach
                </nav>

                <div class="sp-ca__modal-mid">
                    <div class="sp-ca__modal-search">
                        <x-filament::icon icon="heroicon-m-magnifying-glass" class="w-4 h-4" />
                        <input type="text"
                            wire:model.live.debounce.250ms="customSearch"
                            placeholder="Search {{ strtolower($tabs[$customTab][0] ?? '') }}…" />
                    </div>

                    <div class="sp-ca__modal-list">
                        @forelse ($options as $opt)
                            @php $isPicked = in_array((string) $opt['id'], $picked, true); @endphp
                            <button type="button"
                                wire:click="toggleCustomItem('{{ $customTab }}', @js($opt['id']), @js($opt['label']))"
                                class="sp-ca__modal-row {{ $isPicked ? 'is-picked' : '' }}">
                                <span class="sp-ca__modal-row-check">
                                    @if ($isPicked)
                                        <x-filament::icon icon="heroicon-s-check" class="w-3 h-3" />
                                    @endif
                                </span>
                                <span class="sp-ca__modal-row-body">
                                    <span class="sp-ca__modal-row-label">{{ $opt['label'] }}</span>
                                    @if (! empty($opt['meta']))
                                        <span class="sp-ca__modal-row-meta">{{ $opt['meta'] }}</span>
                                    @endif
                                </span>
                            </button>
                        @empty
                            <div class="sp-ca__modal-empty">No matches for “{{ $customSearch }}”.</div>
                        @endforelse
                    </div>
                </div>

                <aside class="sp-ca__modal-side">
                    <div class="sp-ca__modal-side-head">
                        <span>Selected</span>
                        <strong>{{ $total }}</strong>
                    </div>

                    @if ($total === 0)
                        <div class="sp-ca__modal-side-empty">
                            <x-filament::icon icon="heroicon-o-inbox" class="w-7 h-7" />
                            <p>Nothing selected yet. Pick items from any tab on the left.</p>
                        </div>
                    @else
                        <div class="sp-ca__modal-side-list">
                            @foreach ($customPicked as $bucket => $ids)
                                @continue (empty($ids))
                                <div class="sp-ca__modal-bucket">
                                    <div class="sp-ca__modal-bucket-head">
                                        <span class="sp-ca__modal-bucket-name">{{ $tabs[$bucket][0] ?? ucfirst($bucket) }} <em>· {{ count($ids) }}</em></span>
                                        <button type="button" wire:click="clearCustomBucket('{{ $bucket }}')" title="Clear">
                                            <x-filament::icon icon="heroicon-m-x-mark" class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                    <div class="sp-ca__modal-bucket-pills">
                                        @foreach ($ids as $id)
                                            @php $lbl = $customLabels[$bucket.':'.$id] ?? $id; @endphp
                                            <span class="sp-ca__pill sp-ca__pill--{{ $bucket }}">
                                                {{ $lbl }}
                                                <button type="button" wire:click="toggleCustomItem('{{ $bucket }}', @js($id), @js($lbl))">
                                                    <x-filament::icon icon="heroicon-m-x-mark" class="w-3 h-3" />
                                                </button>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </aside>
            </div>

            <footer class="sp-ca__modal-foot">
                <button type="button" class="sp-ca__btn sp-ca__btn--ghost" wire:click="clearAllCustom" @disabled($total === 0)>
                    <x-filament::icon icon="heroicon-m-trash" class="w-4 h-4" />
                    Clear all
                </button>
                <div class="sp-ca__modal-foot-right">
                    <button type="button" class="sp-ca__btn sp-ca__btn--ghost" wire:click="closeCustom">Cancel</button>
                    <button type="button" class="sp-ca__btn sp-ca__btn--primary" wire:click="applyCustom">
                        <x-filament::icon icon="heroicon-s-check" class="w-4 h-4" />
                        Apply{{ $total > 0 ? " ({$total})" : '' }}
                    </button>
                </div>
            </footer>
        </div>
    @endif
</x-filament-panels::page>
