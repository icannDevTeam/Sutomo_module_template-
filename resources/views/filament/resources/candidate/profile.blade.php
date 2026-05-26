<x-filament-panels::page>
    @php
        $c = $record;
        $v = $c->vacancy;
        $stages = $this->getStages();
        $currentIdx = array_search($c->stage, $stages);
        $stageLabels = \App\Models\Candidate::STAGES;
        $notes = $c->meta['notes'] ?? [];
        $audits = \App\Models\AuditLog::where('target', 'like', $c->code . '%')->orderByDesc('occurred_at')->get();
        $interviews = $c->interviews()->orderByDesc('scheduled_date')->get();
        $deposit = $c->deposit;
        $psycho = $c->meta['psycho'] ?? null;
        $medical = $c->meta['medical'] ?? null;
        $yayasan = $c->meta['yayasan'] ?? null;
        $initials = collect(explode(' ', $c->name))->map(fn($p)=>mb_substr($p,0,1))->take(2)->implode('');

        $tabs = [
            'overview'  => 'Overview',
            'docs'      => 'Documents',
            'assess'    => 'Assessments',
            'interview' => 'Interview Notes',
            'deposit'   => 'Deposit',
            'medical'   => 'Medical',
            'psycho'    => 'Psycho',
            'timeline'  => 'Timeline',
            'audit'     => 'Audit',
        ];
    @endphp

    {{-- HERO --}}
    <x-filament::section>
        <div class="sp-cand-hero">
            <div class="sp-cand-avatar">{{ $initials }}</div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-2xl font-bold text-gray-950 dark:text-white">{{ $c->name }}</h1>
                    @if ($c->priority === 'high')
                        <span class="inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-700 dark:bg-rose-500/10 dark:text-rose-400">High priority</span>
                    @endif
                    <span class="inline-flex items-center rounded-full bg-primary-100 px-2.5 py-0.5 text-xs font-semibold text-primary-700 dark:bg-primary-500/10 dark:text-primary-400">
                        {{ $stageLabels[$c->stage] ?? $c->stage }}
                    </span>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $c->education }}</div>
                <div class="sp-cand-meta-row">
                    @if ($v)
                        <span><x-filament::icon icon="heroicon-m-briefcase" class="w-3.5 h-3.5" /> {{ $v->title }}</span>
                    @endif
                    <span><x-filament::icon icon="heroicon-m-envelope" class="w-3.5 h-3.5" /> {{ $c->email ?: '—' }}</span>
                    <span><x-filament::icon icon="heroicon-m-phone" class="w-3.5 h-3.5" /> {{ $c->phone ?: '—' }}</span>
                    <span><x-filament::icon icon="heroicon-m-calendar" class="w-3.5 h-3.5" /> Applied {{ $c->applied_at?->format('d M Y') }}</span>
                    <code>{{ $c->code }}</code>
                </div>
            </div>
        </div>

        {{-- STAGE STEPPER --}}
        <div class="mt-5 -mx-2 px-2 overflow-x-auto">
            <div class="sp-stage-strip">
                @foreach ($stages as $i => $s)
                    @php $done = $i < $currentIdx; $active = $i === $currentIdx; @endphp
                    <div @class([
                        'sp-stage-pill',
                        'sp-stage-pill--done'     => $done,
                        'sp-stage-pill--active'   => $active,
                        'sp-stage-pill--upcoming' => !$done && !$active,
                    ])>
                        <span class="sp-stage-num">{{ $i + 1 }}</span>
                        <span class="sp-stage-label">{{ $stageLabels[$s] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </x-filament::section>

    {{-- ASSESSMENT SCORES --}}
    @php
        $scoreCards = [
            ['label' => 'Written',     'value' => $c->score_written],
            ['label' => 'Interview',   'value' => $c->score_interview],
            ['label' => 'Micro-teach', 'value' => $c->score_micro],
        ];
        $colorFor = function ($s) {
            if ($s === null) return ['bar' => 'bg-gray-300 dark:bg-white/10', 'text' => 'text-gray-400'];
            if ($s >= 85) return ['bar' => 'bg-emerald-500', 'text' => 'text-emerald-600 dark:text-emerald-400'];
            if ($s >= 70) return ['bar' => 'bg-indigo-500',  'text' => 'text-indigo-600 dark:text-indigo-400'];
            if ($s >= 50) return ['bar' => 'bg-amber-500',   'text' => 'text-amber-600 dark:text-amber-400'];
            return ['bar' => 'bg-rose-500', 'text' => 'text-rose-600 dark:text-rose-400'];
        };
        $vals = collect($scoreCards)->pluck('value')->filter(fn ($v) => $v !== null && $v !== '');
        $avg = $vals->count() ? (int) round($vals->avg()) : null;
    @endphp
    <x-filament::section class="mt-4">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">Assessment Scores</h2>
            <div class="text-sm text-gray-500">
                Average:
                <span class="font-bold text-lg {{ $avg !== null ? $colorFor($avg)['text'] : 'text-gray-400' }}">{{ $avg ?? '—' }}</span>
                <span class="text-gray-400 text-xs">/ 100</span>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-3">
            @foreach ($scoreCards as $sc)
                @php
                    $val = $sc['value'];
                    $hasVal = $val !== null && $val !== '';
                    $col = $colorFor($hasVal ? (int) $val : null);
                    $width = $hasVal ? max(0, min(100, (int) $val)) : 0;
                @endphp
                <div class="rounded-lg bg-gray-50 dark:bg-white/5 p-4">
                    <div class="text-xs uppercase font-bold text-gray-400 tracking-wider mb-2">{{ $sc['label'] }}</div>
                    <div class="flex items-baseline gap-1 mb-2">
                        <div class="font-bold text-3xl {{ $hasVal ? $col['text'] : 'text-gray-400' }} leading-none">{{ $hasVal ? $val : '—' }}</div>
                        <div class="text-xs text-gray-400">/ 100</div>
                    </div>
                    <div class="h-2 bg-gray-100 dark:bg-white/10 rounded overflow-hidden">
                        <div class="h-2 {{ $col['bar'] }} rounded" style="width: {{ $width }}%; {{ $hasVal ? '' : 'opacity:.4;' }}"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>

    {{-- TABS --}}
    <div class="mt-4 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
        <div class="flex gap-1 px-3 border-b border-gray-200 dark:border-white/10 overflow-x-auto">
            @foreach ($tabs as $id => $label)
                <button
                    wire:click="setActiveTab('{{ $id }}')"
                    @class([
                        'px-3 py-3 text-sm whitespace-nowrap border-b-2 -mb-px transition',
                        'border-primary-600 text-primary-700 dark:text-primary-400 font-semibold' => $activeTab === $id,
                        'border-transparent text-gray-500 hover:text-gray-900 dark:hover:text-white' => $activeTab !== $id,
                    ])
                >{{ $label }}</button>
            @endforeach
        </div>

        <div class="p-5">
            @if ($activeTab === 'overview')
                <div class="sp-cand-overview-grid">
                    <div class="space-y-5">
                        <div>
                            <div class="sp-cand-block-h">Candidate summary</div>
                            <p class="sp-cand-summary">
                                {{ $c->years }} years of teaching experience in {{ collect($c->subjects ?? [])->join(', ') ?: 'general subjects' }}.
                                Applying for <b>{{ $v?->title }}</b>. Strong written-test performance and consistent micro-teaching evaluations.
                            </p>
                            @if (!empty($c->subjects))
                                <div class="sp-cand-chip-row">
                                    @foreach ($c->subjects as $sub)
                                        <span class="sp-cand-chip">{{ $sub }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        @if ($c->score_written && $c->score_interview)
                            <div>
                                <div class="sp-cand-block-h">Internal recommendation</div>
                                <div class="rounded-lg bg-emerald-50 dark:bg-emerald-500/10 p-4 flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 flex items-center justify-center">
                                        <x-filament::icon icon="heroicon-m-hand-thumb-up" class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <div class="font-semibold text-base text-gray-950 dark:text-white">Strong Hire</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Per panel evaluation. 3 of 3 evaluators agreed.</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div>
                            <div class="sp-cand-block-h">Internal notes ({{ count($notes) }})</div>
                            <div class="sp-cand-notes-grid">
                                @forelse ($notes as $n)
                                    <div class="sp-cand-sticky">
                                        <div class="sp-cand-sticky-head">
                                            <span class="sp-cand-sticky-who">{{ $n['who'] }}</span>
                                            <span class="sp-cand-sticky-when">{{ $n['when'] }}</span>
                                        </div>
                                        <div class="sp-cand-sticky-text">{{ $n['text'] }}</div>
                                    </div>
                                @empty
                                    <div class="text-sm text-gray-400">No notes yet.</div>
                                @endforelse
                            </div>
                            <div class="mt-3">
                                <textarea wire:model="newNote" rows="2" class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm" placeholder="Add internal note (visible to hiring team only)"></textarea>
                                <x-filament::button wire:click="addNote" size="sm" icon="heroicon-m-paper-airplane" class="mt-2">Post note</x-filament::button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="sp-cand-side-card">
                            <div class="sp-cand-block-h">Scores</div>
                            <div class="sp-cand-score-grid">
                                @foreach ([['Written',$c->score_written],['Interview',$c->score_interview],['Micro',$c->score_micro]] as $s)
                                    <div class="sp-cand-score">
                                        <div class="sp-cand-score-v">{{ $s[1] ?: '—' }}</div>
                                        <div class="sp-cand-score-l">{{ $s[0] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="sp-cand-side-card">
                            <div class="sp-cand-block-h">Verifications</div>
                            @php
                                $rows = [
                                    ['Deposit',  $deposit?->status, $deposit?->status === 'verified' ? 'success' : 'warning'],
                                    ['Psycho',   $psycho['status'] ?? null, ($psycho['status'] ?? null) === 'passed' ? 'success' : 'warning'],
                                    ['Medical',  $medical['status'] ?? null, ($medical['status'] ?? null) === 'passed' ? 'success' : 'warning'],
                                    ['Yayasan',  $yayasan['status'] ?? null, ($yayasan['status'] ?? null) === 'approved' ? 'success' : 'warning'],
                                ];
                            @endphp
                            @foreach ($rows as [$label, $state, $color])
                                <div class="sp-cand-verif-row">
                                    <span class="text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                    @if ($state)
                                        <x-filament::badge :color="$color" size="xs">{{ $state }}</x-filament::badge>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @if ($v)
                            <div class="sp-cand-side-card">
                                <div class="sp-cand-block-h">Vacancy</div>
                                <div class="font-semibold text-sm text-gray-950 dark:text-white">{{ $v->title }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ $v->code }} · {{ strtoupper($v->campus) }}</div>
                            </div>
                        @endif
                        @if (!empty($c->languages) || !empty($c->certifications))
                            <div class="sp-cand-side-card">
                                <div class="sp-cand-block-h">Profile</div>
                                @if (!empty($c->languages))
                                    <div class="text-xs text-gray-500 mb-1">Languages</div>
                                    <div class="sp-cand-chip-row" style="margin-top:0;margin-bottom:.5rem;">
                                        @foreach ($c->languages as $lang)
                                            <span class="sp-cand-chip">{{ $lang }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                @if (!empty($c->certifications))
                                    <div class="text-xs text-gray-500 mb-1">Certifications</div>
                                    <div class="sp-cand-chip-row" style="margin-top:0;">
                                        @foreach ($c->certifications as $cert)
                                            <span class="sp-cand-chip">{{ $cert }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

            @elseif ($activeTab === 'docs')
                <table class="w-full text-sm">
                    <thead class="text-xs uppercase text-gray-400">
                        <tr><th class="text-left py-2">Document</th><th class="text-left">Uploaded</th><th class="text-left">Verified by</th><th class="text-left">Status</th></tr>
                    </thead>
                    <tbody>
                        @foreach ([
                            ['CV.pdf','2026-04-23','Sri Lestari','verified'],
                            ['Ijazah_S2.pdf','2026-04-23','Sri Lestari','verified'],
                            ['Transkrip.pdf','2026-04-23','Sri Lestari','verified'],
                            ['Sertifikat_TOEFL.pdf','2026-04-25','Sri Lestari','verified'],
                            ['KTP.jpg','2026-04-23','Sri Lestari','verified'],
                            ['Teaching_Portfolio.pdf','2026-04-26','—','pending'],
                        ] as $r)
                            <tr class="border-t border-gray-100 dark:border-white/5">
                                <td class="py-2 font-semibold">📄 {{ $r[0] }}</td>
                                <td>{{ $r[1] }}</td>
                                <td>{{ $r[2] }}</td>
                                <td><x-filament::badge :color="$r[3] === 'verified' ? 'success' : 'warning'">{{ $r[3] }}</x-filament::badge></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            @elseif ($activeTab === 'assess')
                <div x-data="{ w: {{ $c->score_written ?: 0 }}, i: {{ $c->score_interview ?: 0 }}, m: {{ $c->score_micro ?: 0 }} }" class="grid md:grid-cols-2 gap-4">
                    <div class="rounded-lg bg-gray-50 dark:bg-white/5 p-4">
                        <div class="text-xs uppercase font-bold text-gray-400 tracking-wider mb-2">Written test</div>
                        <div class="font-bold text-3xl text-gray-950 dark:text-white">{{ $c->score_written ?: '—' }}<span class="text-base text-gray-400 font-normal"> / 100</span></div>
                        <div class="text-xs text-gray-500 mt-1">Pass threshold 70</div>
                        <input type="number" x-model="w" min="0" max="100" class="mt-3 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm" placeholder="Enter score…">
                    </div>
                    <div class="rounded-lg bg-gray-50 dark:bg-white/5 p-4">
                        <div class="text-xs uppercase font-bold text-gray-400 tracking-wider mb-2">Interview</div>
                        <div class="font-bold text-3xl text-gray-950 dark:text-white">{{ $c->score_interview ?: '—' }}<span class="text-base text-gray-400 font-normal"> / 100</span></div>
                        <div class="text-xs text-gray-500 mt-1">Panel score</div>
                        <input type="number" x-model="i" min="0" max="100" class="mt-3 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm" placeholder="Enter score…">
                    </div>
                    <div class="rounded-lg bg-gray-50 dark:bg-white/5 p-4 md:col-span-2">
                        <div class="text-xs uppercase font-bold text-gray-400 tracking-wider mb-2">Micro-teaching</div>
                        <div class="flex items-end justify-between gap-4 flex-wrap">
                            <div>
                                <div class="font-bold text-3xl text-gray-950 dark:text-white">{{ $c->score_micro ?: '—' }}<span class="text-base text-gray-400 font-normal"> / 100</span></div>
                                <div class="text-xs text-gray-500 mt-1">30 min lesson</div>
                            </div>
                            <input type="number" x-model="m" min="0" max="100" class="w-48 rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm" placeholder="Enter score…">
                        </div>
                        <div class="mt-4 flex justify-end">
                            <x-filament::button x-on:click="$wire.saveScores(parseInt(w)||null, parseInt(i)||null, parseInt(m)||null)" icon="heroicon-m-check">Save scores</x-filament::button>
                        </div>
                    </div>
                </div>

            @elseif ($activeTab === 'interview')
                <div class="space-y-3">
                    @forelse ($interviews as $iv)
                        <div class="rounded-lg bg-gray-50 dark:bg-white/5 p-4">
                            <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-md bg-primary-100 dark:bg-primary-500/20 text-primary-700 dark:text-primary-400 flex items-center justify-center">
                                        <x-filament::icon icon="heroicon-m-video-camera" class="w-5 h-5" />
                                    </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-base text-gray-950 dark:text-white">{{ $iv->type }}</div>
                                    <div class="text-xs text-gray-500">{{ $iv->scheduled_date?->format('d M Y') }} · {{ $iv->scheduled_time }} · {{ $iv->room }} · Panel: {{ collect($iv->panel ?? [])->join(', ') }}</div>
                                </div>
                                <x-filament::badge :color="$iv->status === 'completed' ? 'success' : 'warning'">{{ $iv->recommendation ?: $iv->status }}</x-filament::badge>
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-400">No interviews scheduled.</div>
                    @endforelse
                </div>

            @elseif ($activeTab === 'deposit')
                @if ($deposit)
                    <div class="grid md:grid-cols-3 gap-4">
                        <div class="rounded-lg bg-gray-50 dark:bg-white/5 p-4 md:col-span-2">
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-xs uppercase font-bold text-gray-400 tracking-wider">Deposit detail</div>
                                <x-filament::badge :color="$deposit->status === 'verified' ? 'success' : 'warning'">{{ $deposit->status }}</x-filament::badge>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div><div class="text-xs text-gray-400">Amount</div><div class="font-bold text-xl text-gray-950 dark:text-white">Rp {{ number_format($deposit->amount, 0, ',', '.') }}</div></div>
                                <div><div class="text-xs text-gray-400">Paid date</div><div class="font-semibold">{{ $deposit->paid_at?->format('d M Y') ?: '—' }}</div></div>
                                <div><div class="text-xs text-gray-400">Bank</div><div class="font-semibold">{{ $deposit->bank }}</div></div>
                                <div><div class="text-xs text-gray-400">Refund eligible</div><div>{!! $deposit->refund_eligible ? '<span class="text-emerald-600 font-semibold">Yes</span>' : '<span class="text-gray-400">Not yet</span>' !!}</div></div>
                            </div>
                        </div>
                        <div class="rounded-lg bg-gray-50 dark:bg-white/5 p-4">
                            <div class="text-xs uppercase font-bold text-gray-400 tracking-wider mb-2">Receipt</div>
                            <div class="aspect-[3/4] bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-md flex items-center justify-center text-gray-300 text-5xl">📄</div>
                            <div class="text-xs text-center text-gray-500 mt-2">{{ $deposit->receipt ?: 'No receipt' }}</div>
                        </div>
                    </div>
                @else
                    <div class="text-sm text-gray-400">No deposit recorded yet.</div>
                @endif

            @elseif (in_array($activeTab, ['medical', 'psycho']))
                @php
                    $isMed = $activeTab === 'medical';
                    $data = $isMed ? $medical : $psycho;
                @endphp
                <div class="mb-4 rounded-lg bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 px-4 py-3 text-sm text-amber-800 dark:text-amber-300">
                    <b>Restricted access.</b> {{ $isMed ? 'Medical' : 'Psychological' }} records are visible to authorized roles only. All views are audit-logged.
                </div>
                @if ($data)
                    <div class="rounded-lg bg-gray-50 dark:bg-white/5 p-5 max-w-xl">
                        <div class="flex items-center justify-between mb-4">
                            <div class="font-semibold text-base">{{ $isMed ? 'Medical clearance' : 'Psychological evaluation' }}</div>
                            <x-filament::badge :color="in_array($data['status'] ?? '', ['passed','approved']) ? 'success' : 'warning'">{{ $data['status'] ?? '—' }}</x-filament::badge>
                        </div>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div><div class="text-xs text-gray-400">Date</div><div class="font-semibold">{{ $data['date'] ?? '—' }}</div></div>
                            <div><div class="text-xs text-gray-400">{{ $isMed ? 'Clinic' : 'Counselor' }}</div><div class="font-semibold">{{ $data['clinic'] ?? $data['counselor'] ?? '—' }}</div></div>
                        </div>
                    </div>
                @else
                    <div class="text-sm text-gray-400">No record yet.</div>
                @endif

            @elseif ($activeTab === 'timeline')
                <div class="space-y-3">
                    @forelse ($audits as $a)
                        <div class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                                @if (!$loop->last)<div class="w-px flex-1 bg-gray-200 dark:bg-white/10 mt-1"></div>@endif
                            </div>
                            <div class="pb-3">
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $a->action }} → {{ $a->to_value }}</div>
                                <div class="text-xs text-gray-400">{{ $a->occurred_at?->format('d M Y · H:i') }} · {{ $a->user_name }}</div>
                                @if ($a->note)<div class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ $a->note }}</div>@endif
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-400">No events.</div>
                    @endforelse
                </div>

            @elseif ($activeTab === 'audit')
                <table class="w-full text-sm">
                    <thead class="text-xs uppercase text-gray-400">
                        <tr><th class="text-left py-2">Time</th><th class="text-left">User</th><th class="text-left">Action</th><th class="text-left">Change</th><th class="text-left">Note</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($audits as $a)
                            <tr class="border-t border-gray-100 dark:border-white/5">
                                <td class="py-2 text-xs">{{ $a->occurred_at?->format('d M · H:i') }}</td>
                                <td class="font-semibold">{{ $a->user_name }}</td>
                                <td><x-filament::badge color="gray">{{ $a->action }}</x-filament::badge></td>
                                <td class="text-xs">{{ $a->from_value }} → <b>{{ $a->to_value }}</b></td>
                                <td class="text-gray-500">{{ $a->note }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-sm text-gray-400 py-6">No audit events for this candidate.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-filament-panels::page>
