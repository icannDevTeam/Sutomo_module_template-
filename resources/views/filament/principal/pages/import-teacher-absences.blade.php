<x-filament-panels::page>

    <x-principal.module-hero
        title="Import Teacher Absences"
        description="Upload an Excel or CSV file containing today's absences. The system will match teachers by name, map leave types, and create leave records automatically."
        icon="heroicon-o-arrow-up-tray"
        tone="slate"
    />

    <div class="sp-cm-intro">
        Use this page to bulk-record absences after the daily classroom check-round. Download the template, fill it in Excel, then upload it here. Valid rows are created as approved leave records instantly.
    </div>

    {{-- ① Download Template --}}
    <div class="sp-card">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="sp-card-h">Step 1 — Download the template</div>
                <div class="sp-card-sub mt-1">
                    Open in Excel, fill in the absences for today, save as <strong>.xlsx</strong> or <strong>.csv</strong>, then upload below.
                </div>
                <div class="mt-3 overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
                    <table class="min-w-full text-xs">
                        <thead class="bg-gray-50 dark:bg-white/5">
                            <tr>
                                @foreach(['Tanggal','Nama Guru','Jenis Ijin','Alasan','Jam Ke','Kelas','Guru Ganti','Disetujui Oleh'] as $col)
                                    <th class="whitespace-nowrap border-b border-gray-200 px-3 py-2 text-left font-semibold text-gray-600 dark:border-white/10 dark:text-gray-300">{{ $col }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="text-gray-500 dark:text-gray-400">
                                <td class="px-3 py-2 font-mono">16/10/2026</td>
                                <td class="px-3 py-2">Budi Santoso, S.Pd</td>
                                <td class="px-3 py-2">Sakit</td>
                                <td class="px-3 py-2">Demam</td>
                                <td class="px-3 py-2">1,2,3</td>
                                <td class="px-3 py-2">8-A</td>
                                <td class="px-3 py-2 text-gray-400 italic">optional</td>
                                <td class="px-3 py-2">Pak Hartono</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    <strong>Jenis Ijin</strong> accepts: Sakit, Darurat, Izin/Ijin, Setengah Hari, Singkat, Tugas Luar, Duka/Berkabung, Pernikahan/Nikah, Dispensasi/Sabbatical
                </div>
            </div>
            <div class="shrink-0">
                <button type="button" wire:click="downloadTemplate" class="sp-btn sp-btn-ghost gap-2">
                    <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                    Download Template (.csv)
                </button>
            </div>
        </div>
    </div>

    {{-- ② Upload --}}
    @if(!$imported)
    <div class="sp-card">
        <div class="sp-card-h">Step 2 — Upload your filled file</div>
        <div class="sp-card-sub mt-1">Accepts .xlsx, .xls, and .csv files. The first row must be the header row.</div>

        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Choose file</label>
            <input
                type="file"
                wire:model.live="upload"
                accept=".xlsx,.xls,.csv"
                class="block w-full text-sm text-gray-600 dark:text-gray-300
                       file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2
                       file:text-sm file:font-semibold file:text-indigo-700
                       hover:file:bg-indigo-100 dark:file:bg-indigo-500/10 dark:file:text-indigo-300"
            />
            <div wire:loading wire:target="upload" class="mt-2 text-xs text-indigo-600 dark:text-indigo-400">Parsing file…</div>
        </div>

        @if($parseError)
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                {{ $parseError }}
            </div>
        @endif
    </div>
    @endif

    {{-- ③ Preview --}}
    @if($rows !== null && !$imported)
        @php
            $validCount   = count(array_filter($rows, fn($r) => $r['valid']));
            $invalidCount = count($rows) - $validCount;

            $typeLabels = [
                'sick'           => 'Sick Leave',
                'emergency'      => 'Emergency',
                'sabbatical'     => 'Sabbatical',
                'midday'         => 'Mid-day Absence',
                'prior'          => 'Prior Notice',
                'brief_absence'  => 'Brief Absence',
                'assigned_work'  => 'Assigned Duty',
                'bereavement'    => 'Bereavement',
                'marriage'       => 'Marriage',
            ];
        @endphp

        <div class="sp-card">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="sp-card-h">Step 3 — Review & confirm</div>
                    <div class="sp-card-sub mt-1">
                        {{ count($rows) }} rows parsed ·
                        <span class="text-emerald-700 dark:text-emerald-400">{{ $validCount }} valid</span>
                        @if($invalidCount)
                            · <span class="text-red-600 dark:text-red-400">{{ $invalidCount }} with errors</span>
                        @endif
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="button" wire:click="resetImport" class="sp-btn sp-btn-ghost">
                        Clear
                    </button>
                    @if($validCount > 0)
                        <button type="button" wire:click="confirmImport"
                                wire:confirm="Import {{ $validCount }} valid row(s) as approved leave records?"
                                class="sp-btn" style="background:#4f46e5;color:#fff;border:none;">
                            <x-heroicon-o-check class="h-4 w-4" />
                            Import {{ $validCount }} valid row(s)
                        </button>
                    @endif
                </div>
            </div>

            <div class="mt-4 overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
                <table class="min-w-full border-collapse text-xs">
                    <thead class="bg-gray-50/80 dark:bg-white/[0.03]">
                        <tr>
                            <th class="border-b border-gray-200 px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:border-white/10 dark:text-gray-400">#</th>
                            <th class="border-b border-gray-200 px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:border-white/10 dark:text-gray-400">Date</th>
                            <th class="border-b border-gray-200 px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:border-white/10 dark:text-gray-400">Teacher</th>
                            <th class="border-b border-gray-200 px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:border-white/10 dark:text-gray-400">Type</th>
                            <th class="border-b border-gray-200 px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:border-white/10 dark:text-gray-400">Reason</th>
                            <th class="border-b border-gray-200 px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:border-white/10 dark:text-gray-400">Period / Class</th>
                            <th class="border-b border-gray-200 px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:border-white/10 dark:text-gray-400">Substitute</th>
                            <th class="border-b border-gray-200 px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:border-white/10 dark:text-gray-400">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr class="border-t border-gray-100 {{ $row['valid'] ? '' : 'bg-red-50/30 dark:bg-red-500/5' }}">
                                <td class="px-3 py-2 text-gray-400">{{ $row['row'] }}</td>
                                <td class="px-3 py-2 font-mono">{{ $row['date'] ?? $row['raw']['date'] ?? '—' }}</td>
                                <td class="px-3 py-2 font-medium text-gray-900 dark:text-white">{{ $row['teacher_name'] ?: '—' }}</td>
                                <td class="px-3 py-2">
                                    @if($row['type'])
                                        <span class="sp-pill sp-pill-blue">{{ $typeLabels[$row['type']] ?? $row['type'] }}</span>
                                    @else
                                        <span class="sp-pill sp-pill-red">{{ $row['raw']['type'] ?? '—' }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $row['reason'] ?: '—' }}</td>
                                <td class="px-3 py-2 text-gray-600 dark:text-gray-300">
                                    @if($row['period'] || $row['class'])
                                        {{ implode(' / ', array_filter([$row['period'], $row['class']])) }}
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $row['substitute_name'] ?: '—' }}</td>
                                <td class="px-3 py-2">
                                    @if($row['valid'])
                                        <span class="sp-pill sp-pill-green">Ready</span>
                                    @else
                                        <div class="space-y-1">
                                            @foreach($row['errors'] as $err)
                                                <div class="rounded bg-red-50 px-2 py-0.5 text-[10px] text-red-700 dark:bg-red-500/10 dark:text-red-300">{{ $err }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ④ Result --}}
    @if($imported && $importResult)
        <div class="sp-card">
            <div class="flex items-start gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-500/20">
                    <x-heroicon-o-check-circle class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                </div>
                <div>
                    <div class="sp-card-h text-emerald-700 dark:text-emerald-400">Import complete</div>
                    <div class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $importResult }}</div>
                    <div class="mt-4 flex gap-3">
                        <button type="button" wire:click="resetImport" class="sp-btn sp-btn-ghost">
                            Import another file
                        </button>
                        <a href="{{ route('filament.principal.resources.teacher-leaves.index') }}" class="sp-btn" style="background:#4f46e5;color:#fff;border:none;text-decoration:none;">
                            View Teacher Leaves →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Help accordion --}}
    <div class="sp-card" x-data="{ open: false }">
        <button type="button" @click="open = !open" class="flex w-full items-center justify-between">
            <div class="sp-card-h">Tips & troubleshooting</div>
            <x-heroicon-m-chevron-down class="h-4 w-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" />
        </button>
        <div x-show="open" x-transition class="mt-4 space-y-3 text-xs text-gray-600 dark:text-gray-400">
            <div><strong class="text-gray-800 dark:text-gray-200">Teacher not found:</strong> The name must closely match what's in the system (e.g. "Budi Santoso" matches "Budi Santoso, S.Pd"). Copy names from the Teachers roster page.</div>
            <div><strong class="text-gray-800 dark:text-gray-200">Date format:</strong> Use DD/MM/YYYY (e.g. 16/10/2026) or YYYY-MM-DD. If Excel auto-formats dates, save as CSV to preserve the exact text.</div>
            <div><strong class="text-gray-800 dark:text-gray-200">Leave type:</strong> Accepted values — Sakit, Darurat, Izin, Setengah Hari, Singkat, Tugas Luar, Duka, Berkabung, Pernikahan, Nikah, Dispensasi, Sabbatical.</div>
            <div><strong class="text-gray-800 dark:text-gray-200">Duplicates:</strong> A row is skipped if a leave with the same teacher, date, and type already exists in the system.</div>
            <div><strong class="text-gray-800 dark:text-gray-200">Substitute teacher:</strong> Optional. If provided and found, the leave will be marked as covered. If not found, the row still imports without a substitute.</div>
        </div>
    </div>

</x-filament-panels::page>
