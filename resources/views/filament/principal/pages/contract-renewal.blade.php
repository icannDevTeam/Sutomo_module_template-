<x-filament-panels::page>
    {{-- Intro line --}}
    <div class="sp-cm-intro">
        Contracts inside their renewal window — recommend, submit to Yayasan, and record their decision.
        On approval, the next-tier contract is spawned automatically.
        Renewal window = <b>{{ \App\Models\ObservationSetting::current()->renewal_window_months }} months</b>
        before contract end.
    </div>

    {{-- Tier filter --}}
    <div class="sp-tabs">
        <div class="sp-tabs__group">
            @foreach ($tierLabels as $key => $label)
                <button type="button"
                    wire:click='$set("tier", @js($key))'
                    class="sp-tab {{ $tier === $key ? 'is-active' : '' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- KPIs --}}
    <div class="sp-kpis">
        @php
            $kpis = [
                ['Total in window',       $stats['total'],    'slate', 'heroicon-o-users'],
                ['Decision due ≤ ' . $dueDays . 'd', $stats['due'], 'rose', 'heroicon-o-clock'],
                ['Recommendation pending', $stats['pending'], 'amber', 'heroicon-o-document-arrow-up'],
                ['Awaiting Yayasan',       $stats['awaiting'], 'sky',  'heroicon-o-paper-airplane'],
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

    {{-- Rows --}}
    @forelse ($rows as $r)
        @php
            $c = $r->contract;
            $hasRec = ! is_null($c->recommendation_decision);
            $hasFile = ! is_null($c->recommendation_letter_path);
            $submitted = ! is_null($c->submitted_to_yayasan_at);
            $yayDecision = $c->yayasan_decision;
            $allowedNext = \App\Filament\Principal\Pages\ContractRenewal::allowedNextTiers($c->type);

            $rowClass = match (true) {
                $yayDecision === 'approved' => 'is-success',
                $yayDecision === 'rejected' => 'is-danger',
                ! is_null($r->days_left) && $r->days_left <= $dueDays => 'is-warning',
                default => 'is-info',
            };
            $daysCls = match (true) {
                is_null($r->days_left)     => null,
                $r->days_left < 0          => 'sp-days--past',
                $r->days_left <= $dueDays  => 'sp-days--due',
                default                    => 'sp-days--ok',
            };

            // Stage progression for stepper
            $stages = [
                ['label' => 'Recommend',      'state' => $hasRec ? 'done' : (! $submitted ? 'current' : 'locked')],
                ['label' => 'Submitted',      'state' => $submitted ? 'done' : ($hasRec ? 'current' : 'locked')],
                ['label' => 'Yayasan',        'state' => $yayDecision ? 'done' : ($submitted ? 'current' : 'locked')],
                ['label' => 'Spawned',        'state' => $c->status !== 'active' ? 'done' : ($yayDecision ? 'current' : 'locked')],
            ];
        @endphp

        <div class="sp-card sp-cm-card {{ $rowClass }}">
            <div class="sp-onb-head">
                <div class="sp-onb-id" style="display:flex; align-items:center; gap:.75rem;">
                    <div class="sp-avatar">{{ \Illuminate\Support\Str::of($r->teacher?->name ?? '?')->substr(0, 1) }}</div>
                    <div style="min-width:0;">
                        <div class="sp-onb-name">
                            {{ $r->teacher?->name ?? 'Unknown teacher' }}
                            <span class="sp-tier sp-tier--{{ $c->type }}">{{ \App\Models\TeacherContract::TYPES[$c->type] ?? $c->type }}</span>
                        </div>
                        <div class="sp-onb-meta">
                            {{ $r->teacher?->employee_no ?? '—' }}
                            @if ($r->teacher?->campus) · {{ strtoupper($r->teacher->campus) }} @endif
                            · Window
                            <b style="color:#0f172a;">{{ $c->renewal_window_starts_at?->format('d M') }} → {{ $c->ends_at?->format('d M Y') }}</b>
                        </div>
                    </div>
                </div>
                @if ($daysCls)
                    <div class="sp-onb-actions">
                        <span class="sp-days {{ $daysCls }}">
                            <x-filament::icon icon="heroicon-o-clock" style="width:13px;height:13px;" />
                            @if ($r->days_left < 0)
                                Ended {{ abs($r->days_left) }}d ago
                            @else
                                {{ $r->days_left }} days left
                            @endif
                        </span>
                    </div>
                @endif
            </div>

            {{-- Stepper --}}
            <div class="sp-stepper" style="margin-top:.9rem;">
                @foreach ($stages as $s)
                    <div class="sp-step sp-step-{{ $s['state'] }}">
                        <span class="sp-step-dot">
                            @if ($s['state'] === 'done')
                                <x-filament::icon icon="heroicon-m-check" style="width:12px;height:12px;color:#fff;" />
                            @elseif ($s['state'] === 'current')
                                <span class="sp-step-pulse"></span>
                            @else
                                <x-filament::icon icon="heroicon-m-lock-closed" style="width:11px;height:11px;color:#94a3b8;" />
                            @endif
                        </span>
                        <span class="sp-step-label">{{ $s['label'] }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Counters --}}
            <div class="sp-counters">
                <div class="sp-counter">
                    <div class="sp-counter__label">Supervisions ({{ $c->academic_year }})</div>
                    <div class="sp-counter__value">
                        <strong>{{ $r->supervisions }}</strong>
                    </div>
                </div>
                <div class="sp-counter">
                    <div class="sp-counter__label">Peer obs ({{ $c->academic_year }})</div>
                    <div class="sp-counter__value">
                        <strong>{{ $r->peer_obs }}</strong>
                    </div>
                </div>
                <div class="sp-counter">
                    <div class="sp-counter__label">Recommendation</div>
                    <div class="sp-counter__value" style="display:block;">
                        @if ($c->recommendation_decision === 'renew')
                            <span class="sp-pill sp-pill-green">Renew</span>
                        @elseif ($c->recommendation_decision === 'not_renew')
                            <span class="sp-pill sp-pill-red">Don't renew</span>
                        @else
                            <span class="sp-pill sp-pill-gray">Not set</span>
                        @endif
                        @if ($hasFile)
                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($c->recommendation_letter_path) }}" target="_blank"
                                style="display:inline-block;margin-left:.4rem;font-size:.7rem;color:#0284c7;text-decoration:underline;">view letter</a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Action row --}}
            @if ($c->status === 'active')
                <div class="sp-act-row">
                    {{-- Step 1: Upload recommendation letter --}}
                    @if (! $submitted)
                        <label class="sp-act-file">
                            <x-filament::icon icon="heroicon-o-arrow-up-tray" style="width:14px;height:14px;" />
                            {{ $hasFile ? 'Replace letter' : 'Choose letter' }}
                            <input type="file" wire:model="recommendationUploads.{{ $c->id }}" accept="application/pdf,image/*" />
                        </label>
                        @if (isset($recommendationUploads[$c->id]) && $recommendationUploads[$c->id])
                            <button type="button" wire:click="uploadRecommendation({{ $c->id }})" class="sp-act sp-act--slate">
                                Save upload
                            </button>
                        @endif
                    @endif

                    {{-- Step 2: Set recommendation --}}
                    @if (! $hasRec && ! $submitted)
                        <button type="button" wire:click="setRecommendation({{ $c->id }}, 'renew')"
                            wire:confirm="Set recommendation: RENEW?" class="sp-act sp-act--success">
                            <x-filament::icon icon="heroicon-o-check-circle" style="width:14px;height:14px;" /> Recommend Renew
                        </button>
                        <button type="button" wire:click="setRecommendation({{ $c->id }}, 'not_renew')"
                            wire:confirm="Set recommendation: DON'T RENEW?" class="sp-act sp-act--danger">
                            <x-filament::icon icon="heroicon-o-x-circle" style="width:14px;height:14px;" /> Recommend Don't Renew
                        </button>
                    @endif

                    <div class="sp-act-spacer"></div>

                    {{-- Step 3: Submit to Yayasan --}}
                    @if ($hasRec && ! $submitted)
                        <button type="button" wire:click="submitToYayasan({{ $c->id }})"
                            wire:confirm="Submit to Yayasan with recommendation: {{ str_replace('_', ' ', $c->recommendation_decision) }}?"
                            class="sp-act sp-act--sky">
                            <x-filament::icon icon="heroicon-o-paper-airplane" style="width:14px;height:14px;" /> Submit to Yayasan
                        </button>
                    @endif

                    {{-- Step 4: Record Yayasan decision --}}
                    @if ($submitted && is_null($yayDecision))
                        @if ($c->recommendation_decision === 'renew' && count($allowedNext) > 1)
                            <select wire:model="nextTierChoices.{{ $c->id }}"
                                style="padding:.45rem .65rem;border-radius:8px;border:1px solid #e2e8f0;font-size:.8rem;">
                                <option value="">Choose next tier…</option>
                                @foreach ($allowedNext as $val => $lbl)
                                    <option value="{{ $val }}">{{ $lbl }}</option>
                                @endforeach
                            </select>
                        @endif
                        <button type="button" wire:click="recordYayasanDecision({{ $c->id }}, 'approved')"
                            wire:confirm="Yayasan APPROVED — this will spawn the next-tier contract automatically. Continue?"
                            class="sp-act sp-act--success">
                            <x-filament::icon icon="heroicon-o-check-badge" style="width:14px;height:14px;" /> Yayasan Approved
                        </button>
                        <button type="button" wire:click="recordYayasanDecision({{ $c->id }}, 'rejected')"
                            wire:confirm="Yayasan REJECTED — teacher will be marked not_renewed. Continue?"
                            class="sp-act sp-act--danger">
                            <x-filament::icon icon="heroicon-o-x-mark" style="width:14px;height:14px;" /> Yayasan Rejected
                        </button>
                    @endif
                </div>
            @else
                <div class="sp-banner" style="--accent:#94a3b8;margin-top:.75rem;border-left:3px solid var(--accent);background:#f8fafc;border-radius:8px;padding:.55rem .85rem;">
                    <div class="sp-banner-body">
                        Contract status: <b>{{ \App\Models\TeacherContract::STATUSES[$c->status] ?? $c->status }}</b> · No further actions available here.
                    </div>
                </div>
            @endif
        </div>
    @empty
        <div class="sp-cm-empty">
            <x-filament::icon icon="heroicon-o-arrow-path" style="width:42px;height:42px;color:#cbd5e1;margin:0 auto;" />
            <h3>No contracts in renewal window</h3>
            <p>No PKWT-I/II/III contracts are currently inside their renewal window for this filter.</p>
        </div>
    @endforelse
</x-filament-panels::page>
