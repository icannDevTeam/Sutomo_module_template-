@php
    use App\Filament\Principal\Resources\TeacherObservationResource;
    $statusColors = TeacherObservationResource::STATUS_COLORS;
@endphp

<div class="overflow-hidden rounded-lg border border-gray-200 dark:border-white/10">
    @if(($observations ?? collect())->isEmpty())
        <div class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
            No observations recorded yet.
        </div>
    @else
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
            <thead class="bg-gray-50 dark:bg-white/5">
                <tr class="text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                    <th class="px-3 py-2">Date</th>
                    <th class="px-3 py-2">Subject</th>
                    <th class="px-3 py-2">Class</th>
                    <th class="px-3 py-2">Avg</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2">Observer</th>
                    <th class="px-3 py-2 text-right">View</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white dark:divide-white/5 dark:bg-gray-900">
                @foreach($observations as $o)
                    @php
                        $avg = $o->average_score;
                        $avgColor = $avg === null ? 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300' : ($avg >= 4 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : ($avg >= 3 ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300'));
                        $statusKey = $o->status ?? 'pending';
                        $statusTone = $statusColors[$statusKey] ?? 'gray';
                        $statusClass = match ($statusTone) {
                            'success' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
                            'warning' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
                            'danger'  => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
                            default   => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300',
                        };
                        $url = TeacherObservationResource::getUrl('view', ['record' => $o->id], panel: 'principal');
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-200">{{ $o->observed_at?->format('d M Y H:i') }}</td>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $o->lesson_subject ?: '—' }}</td>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $o->lesson_class_code ?: '—' }}</td>
                        <td class="px-3 py-2">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $avgColor }}">
                                {{ $avg !== null ? $avg . '/5' : '—' }}
                            </span>
                        </td>
                        <td class="px-3 py-2">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusClass }}">
                                {{ ucfirst($statusKey) }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ optional($o->observer)->name ?: '—' }}</td>
                        <td class="px-3 py-2 text-right">
                            <a href="{{ $url }}"
                               target="_blank"
                               class="inline-flex items-center gap-1 text-xs font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                                View
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
