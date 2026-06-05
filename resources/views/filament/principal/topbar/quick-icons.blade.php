@php
    use App\Models\Announcement;
    use App\Models\MessageThread;

    $recentAnnouncements = (int) Announcement::where(function ($q) {
            $q->whereNull('sent_at')->orWhere('sent_at', '>=', now()->subDays(7));
        })->count();
    $unreadMessages = (int) MessageThread::sum('unread_count');
    $recentThreads = MessageThread::with(['messages' => fn ($q) => $q->latest('sent_at')->limit(1)])
        ->orderByDesc('last_message_at')->limit(5)->get();

    $announcementItems = Announcement::orderByDesc('pinned')
        ->orderByDesc(\DB::raw('COALESCE(sent_at, created_at)'))
        ->limit(5)
        ->get();
@endphp

<div class="fi-topbar-quick-icons flex items-center gap-1 me-2">
    {{-- Messages --}}
    <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
        <button
            type="button"
            @click="open = ! open"
            class="fi-icon-btn relative flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 outline-none transition duration-75 hover:bg-gray-100 hover:text-gray-700 focus-visible:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
            title="Messages"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.241.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
            </svg>
            @if ($unreadMessages > 0)
                <span class="absolute -top-0.5 -end-0.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-warning-500 px-1 text-[10px] font-semibold leading-none text-white ring-2 ring-white dark:ring-gray-900">
                    {{ $unreadMessages > 99 ? '99+' : $unreadMessages }}
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
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Messages</h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $unreadMessages }} unread</span>
            </div>

            @if ($recentThreads->isEmpty())
                <div class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                    No messages yet.
                </div>
            @else
                <ul class="divide-y divide-gray-100 dark:divide-white/5 max-h-80 overflow-y-auto">
                    @foreach ($recentThreads as $thr)
                        @php $last = $thr->messages->first(); @endphp
                        <li>
                            <a href="{{ url('/principal/messages?t=' . $thr->id) }}"
                               class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5">
                                <span @class([
                                    'mt-0.5 inline-flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-[11px] font-semibold text-white',
                                    'bg-emerald-500' => $thr->partner_color === 'emerald',
                                    'bg-blue-500'    => $thr->partner_color === 'blue',
                                    'bg-rose-500'    => $thr->partner_color === 'rose',
                                    'bg-violet-500'  => $thr->partner_color === 'violet',
                                    'bg-amber-500'   => $thr->partner_color === 'amber',
                                    'bg-slate-500'   => $thr->partner_color === 'slate',
                                ])>{{ $thr->partner_initials }}</span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="truncate text-sm font-medium text-gray-950 dark:text-white">{{ $thr->partner_name }}</p>
                                        <span class="text-[11px] text-gray-400 flex-shrink-0">{{ optional($thr->last_message_at)->diffForHumans(null, true) }}</span>
                                    </div>
                                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ \Illuminate\Support\Str::limit($last?->body ?? '', 60) }}</p>
                                </div>
                                @if($thr->unread_count > 0)
                                    <span class="mt-2 inline-block h-2 w-2 flex-shrink-0 rounded-full bg-blue-500"></span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif

            <a href="{{ url('/principal/messages') }}"
               class="block border-t border-gray-100 px-4 py-2.5 text-center text-xs font-medium text-primary-600 hover:bg-gray-50 dark:border-white/5 dark:text-primary-400 dark:hover:bg-white/5">
                Open Messages →
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
