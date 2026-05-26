@php
    use App\Models\SensitiveApprovalRequest;
    use App\Models\Announcement;

    $pendingStatuses = ['pending_first', 'pending_second'];
    $pendingApprovals = (int) SensitiveApprovalRequest::whereIn('status', $pendingStatuses)->count();
    $recentAnnouncements = (int) Announcement::where(function ($q) {
            $q->whereNull('sent_at')->orWhere('sent_at', '>=', now()->subDays(7));
        })->count();

    $approvalItems = SensitiveApprovalRequest::with(['targetTeacher', 'requester'])
        ->whereIn('status', $pendingStatuses)
        ->latest()
        ->limit(5)
        ->get();

    $announcementItems = Announcement::orderByDesc('pinned')
        ->orderByDesc(\DB::raw('COALESCE(sent_at, created_at)'))
        ->limit(5)
        ->get();

    $actionLabels = [
        'salary_change'       => 'Salary change',
        'contract_terminate'  => 'Contract termination',
        'title_demotion'      => 'Title demotion',
    ];
@endphp

<div class="fi-topbar-quick-icons flex items-center gap-1 me-2">
    {{-- Pending sensitive approvals --}}
    <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
        <button
            type="button"
            @click="open = ! open"
            class="fi-icon-btn relative flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 outline-none transition duration-75 hover:bg-gray-100 hover:text-gray-700 focus-visible:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
            title="Pending approvals"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
            </svg>
            @if ($pendingApprovals > 0)
                <span class="absolute -top-0.5 -end-0.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-danger-500 px-1 text-[10px] font-semibold leading-none text-white ring-2 ring-white dark:ring-gray-900">
                    {{ $pendingApprovals > 99 ? '99+' : $pendingApprovals }}
                </span>
            @endif
        </button>

        <div
            x-show="open"
            x-cloak
            x-transition.origin.top.right
            @click.outside="open = false"
            class="absolute end-0 mt-2 w-80 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 z-50"
        >
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-2.5 dark:border-white/5">
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Pending approvals</h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $pendingApprovals }} pending</span>
            </div>

            @if ($approvalItems->isEmpty())
                <div class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                    No pending approvals.
                </div>
            @else
                <ul class="divide-y divide-gray-100 dark:divide-white/5 max-h-80 overflow-y-auto">
                    @foreach ($approvalItems as $item)
                        <li>
                            <a
                                href="{{ url('/principal/sensitive-approvals/' . $item->id) }}"
                                class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5"
                            >
                                <span class="mt-1 inline-block h-2 w-2 flex-shrink-0 rounded-full bg-danger-500"></span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-950 dark:text-white">
                                        {{ $actionLabels[$item->action_type] ?? $item->action_type }}
                                    </p>
                                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                                        {{ optional($item->targetTeacher)->name ?? '—' }}
                                        · by {{ optional($item->requester)->name ?? 'unknown' }}
                                    </p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">
                                        {{ $item->created_at?->diffForHumans() }}
                                    </p>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif

            <a
                href="{{ url('/principal/sensitive-approvals') }}"
                class="block border-t border-gray-100 px-4 py-2.5 text-center text-xs font-medium text-primary-600 hover:bg-gray-50 dark:border-white/5 dark:text-primary-400 dark:hover:bg-white/5"
            >
                View all →
            </a>
        </div>
    </div>

    {{-- Announcements --}}
    <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
        <button
            type="button"
            @click="open = ! open"
            class="fi-icon-btn relative flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 outline-none transition duration-75 hover:bg-gray-100 hover:text-gray-700 focus-visible:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
            title="Announcements"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />
            </svg>
            @if ($recentAnnouncements > 0)
                <span class="absolute -top-0.5 -end-0.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-primary-500 px-1 text-[10px] font-semibold leading-none text-white ring-2 ring-white dark:ring-gray-900">
                    {{ $recentAnnouncements > 99 ? '99+' : $recentAnnouncements }}
                </span>
            @endif
        </button>

        <div
            x-show="open"
            x-cloak
            x-transition.origin.top.right
            @click.outside="open = false"
            class="absolute end-0 mt-2 w-80 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 z-50"
        >
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-2.5 dark:border-white/5">
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Announcements</h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">last 7 days</span>
            </div>

            @if ($announcementItems->isEmpty())
                <div class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                    No announcements yet.
                </div>
            @else
                <ul class="divide-y divide-gray-100 dark:divide-white/5 max-h-80 overflow-y-auto">
                    @foreach ($announcementItems as $item)
                        <li>
                            <a
                                href="{{ url('/principal/announcements/' . $item->id) }}"
                                class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5"
                            >
                                <span @class([
                                    'mt-1 inline-block h-2 w-2 flex-shrink-0 rounded-full',
                                    'bg-warning-500' => $item->pinned,
                                    'bg-primary-500' => ! $item->pinned,
                                ])></span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-950 dark:text-white">
                                        {{ $item->title }}
                                    </p>
                                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                                        {{ ucfirst($item->category ?? 'general') }}
                                        · {{ $item->author_name }}
                                    </p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">
                                        {{ ($item->sent_at ?? $item->created_at)?->diffForHumans() }}
                                    </p>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif

            <a
                href="{{ url('/principal/announcements') }}"
                class="block border-t border-gray-100 px-4 py-2.5 text-center text-xs font-medium text-primary-600 hover:bg-gray-50 dark:border-white/5 dark:text-primary-400 dark:hover:bg-white/5"
            >
                View all →
            </a>
        </div>
    </div>
</div>
