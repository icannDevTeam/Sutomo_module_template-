<?php

namespace App\Filament\Principal\Pages;

use App\Models\MessageThread;
use App\Models\ThreadMessage;
use App\Models\ThreadParticipant;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;

/**
 * Messages — Principal inbox styled after the Sutomo Teacher messaging mockup.
 *
 * Supports 1:1 threads, group chats (Teams-style), adding members to an
 * existing thread, and @mention autocomplete (including @everyone).
 */
class Messages extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Communication';
    protected static ?int $navigationSort = 2;
    protected static ?string $title = 'Messages';
    protected static ?string $navigationLabel = 'Messages';
    protected static ?string $slug = 'messages';
    protected static string $view = 'filament.principal.pages.messages';

    #[Url(as: 't')]
    public ?int $threadId = null;

    #[Url(as: 'cat')]
    public string $category = 'all';

    public string $search = '';
    public string $draft = '';

    // ---- New / Add modals ----
    public bool $showNewModal = false;
    public bool $newIsGroup = false;
    public string $newGroupName = '';
    public string $newCategory = 'parent';
    /** @var array<int,string> */
    public array $newMembers = [];

    public bool $showAddModal = false;
    /** @var array<int,string> */
    public array $addMembers = [];

    public static function getNavigationBadge(): ?string
    {
        $n = (int) MessageThread::sum('unread_count');
        return $n > 0 ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public function mount(): void
    {
        if (! $this->threadId) {
            $this->threadId = MessageThread::orderByDesc('last_message_at')->value('id');
        }
        $this->markRead();
    }

    public function selectThread(int $id): void
    {
        $this->threadId = $id;
        $this->draft = '';
        $this->markRead();
        $this->dispatch('msg-thread-changed');
    }

    public function setCategory(string $cat): void
    {
        $this->category = in_array($cat, ['all', 'parent', 'students', 'staff'], true) ? $cat : 'all';
    }

    public function send(): void
    {
        $body = trim($this->draft);
        if ($body === '' || ! $this->threadId) return;

        $thread = MessageThread::find($this->threadId);
        if (! $thread) return;

        ThreadMessage::create([
            'thread_id'       => $thread->id,
            'is_me'           => true,
            'sender_name'     => 'Sarah Rahayu',
            'sender_initials' => 'SR',
            'body'            => $body,
            'sent_at'         => now(),
        ]);
        $thread->update([
            'last_message_at' => now(),
            'unread_count'    => 0,
        ]);
        $this->draft = '';
        $this->dispatch('msg-sent');
    }

    protected function markRead(): void
    {
        if ($this->threadId) {
            MessageThread::where('id', $this->threadId)->update(['unread_count' => 0]);
        }
    }

    // ---------- New chat / group ----------
    public function openNew(): void
    {
        $this->showNewModal = true;
        $this->newIsGroup = false;
        $this->newGroupName = '';
        $this->newCategory = 'parent';
        $this->newMembers = [];
    }

    public function closeNew(): void { $this->showNewModal = false; }

    public function toggleNewMember(string $name): void
    {
        if (in_array($name, $this->newMembers, true)) {
            $this->newMembers = array_values(array_filter($this->newMembers, fn ($n) => $n !== $name));
        } else {
            $this->newMembers[] = $name;
        }
    }

    public function createThread(): void
    {
        $members = array_values(array_unique(array_filter($this->newMembers)));
        if (empty($members)) return;

        $directory = collect($this->directory());

        if ($this->newIsGroup || count($members) > 1) {
            $name = trim($this->newGroupName) !== '' ? trim($this->newGroupName) : 'New group';
            $thread = MessageThread::create([
                'subject'          => $name,
                'category'         => $this->newCategory,
                'is_group'         => true,
                'group_name'       => $name,
                'partner_name'     => $name,
                'partner_role'     => count($members) . ' members',
                'partner_initials' => mb_strtoupper(mb_substr($name, 0, 2)),
                'partner_color'    => 'violet',
                'last_message_at'  => now(),
                'unread_count'     => 0,
            ]);
        } else {
            $p = $directory->firstWhere('name', $members[0]) ?? ['name' => $members[0], 'role' => null, 'color' => 'slate'];
            $thread = MessageThread::create([
                'subject'          => $p['role'] ?? 'Conversation',
                'category'         => $this->newCategory,
                'is_group'         => false,
                'partner_name'     => $p['name'],
                'partner_role'     => $p['role'] ?? '',
                'partner_initials' => $this->initialsOf($p['name']),
                'partner_color'    => $p['color'] ?? 'slate',
                'last_message_at'  => now(),
                'unread_count'     => 0,
            ]);
        }

        ThreadParticipant::create([
            'thread_id' => $thread->id,
            'name' => 'Sarah Rahayu', 'initials' => 'SR', 'color' => 'slate', 'role' => 'Principal', 'is_me' => true,
        ]);
        foreach ($members as $name) {
            $p = $directory->firstWhere('name', $name) ?? ['name' => $name, 'role' => null, 'color' => 'slate'];
            ThreadParticipant::create([
                'thread_id' => $thread->id,
                'name' => $p['name'],
                'initials' => $this->initialsOf($p['name']),
                'color' => $p['color'] ?? 'slate',
                'role' => $p['role'] ?? null,
                'is_me' => false,
            ]);
        }

        $this->showNewModal = false;
        $this->threadId = $thread->id;
        $this->dispatch('msg-thread-changed');
    }

    // ---------- Add members to existing thread ----------
    public function openAdd(): void
    {
        $this->showAddModal = true;
        $this->addMembers = [];
    }

    public function closeAdd(): void { $this->showAddModal = false; }

    public function toggleAddMember(string $name): void
    {
        if (in_array($name, $this->addMembers, true)) {
            $this->addMembers = array_values(array_filter($this->addMembers, fn ($n) => $n !== $name));
        } else {
            $this->addMembers[] = $name;
        }
    }

    public function addMembersToThread(): void
    {
        if (! $this->threadId) return;
        $thread = MessageThread::with('participants')->find($this->threadId);
        if (! $thread) return;

        $existing = $thread->participants->pluck('name')->all();
        $directory = collect($this->directory());

        if (! $thread->is_group) {
            if (! in_array('Sarah Rahayu', $existing, true)) {
                ThreadParticipant::create([
                    'thread_id' => $thread->id,
                    'name' => 'Sarah Rahayu', 'initials' => 'SR', 'color' => 'slate', 'role' => 'Principal', 'is_me' => true,
                ]);
                $existing[] = 'Sarah Rahayu';
            }
            if ($thread->partner_name && ! in_array($thread->partner_name, $existing, true)) {
                ThreadParticipant::create([
                    'thread_id' => $thread->id,
                    'name' => $thread->partner_name,
                    'initials' => $thread->partner_initials ?: $this->initialsOf($thread->partner_name),
                    'color' => $thread->partner_color ?: 'slate',
                    'role' => $thread->partner_role,
                    'is_me' => false,
                ]);
                $existing[] = $thread->partner_name;
            }
        }

        $added = [];
        foreach ($this->addMembers as $name) {
            if (in_array($name, $existing, true)) continue;
            $p = $directory->firstWhere('name', $name) ?? ['name' => $name, 'role' => null, 'color' => 'slate'];
            ThreadParticipant::create([
                'thread_id' => $thread->id,
                'name' => $p['name'],
                'initials' => $this->initialsOf($p['name']),
                'color' => $p['color'] ?? 'slate',
                'role' => $p['role'] ?? null,
                'is_me' => false,
            ]);
            $added[] = $p['name'];
        }

        if (! empty($added) || ! $thread->is_group) {
            $count = $thread->participants()->count();
            $groupName = $thread->group_name
                ?: ($thread->is_group ? $thread->displayName() : ($thread->partner_name . ' group'));

            $thread->update([
                'is_group'         => true,
                'group_name'       => $groupName,
                'partner_role'     => $count . ' members',
                'partner_initials' => mb_strtoupper(mb_substr($groupName, 0, 2)),
                'partner_color'    => $thread->partner_color === 'slate' ? 'violet' : $thread->partner_color,
                'last_message_at'  => now(),
            ]);

            if (! empty($added)) {
                ThreadMessage::create([
                    'thread_id' => $thread->id,
                    'is_me' => true,
                    'sender_name' => 'System',
                    'sender_initials' => 'SY',
                    'body' => 'Sarah Rahayu added ' . implode(', ', $added) . ' to the chat.',
                    'sent_at' => now(),
                ]);
                $thread->update(['last_message_at' => now()]);
            }
        }

        $this->showAddModal = false;
        $this->dispatch('msg-sent');
    }

    protected function getViewData(): array
    {
        $threads = MessageThread::query()
            ->when($this->category !== 'all', fn ($q) => $q->where('category', $this->category))
            ->when($this->search !== '', function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(fn ($qq) => $qq->where('partner_name', 'like', $s)
                    ->orWhere('group_name', 'like', $s)
                    ->orWhere('subject', 'like', $s));
            })
            ->orderByDesc('last_message_at')
            ->get()
            ->each(function ($t) {
                $last = $t->messages()->latest('sent_at')->first();
                $t->preview = $last?->body ? str($last->body)->limit(48)->toString() : '';
                $t->stamp   = $this->formatStamp($t->last_message_at);
            });

        $active = $this->threadId
            ? MessageThread::with(['messages', 'participants'])->find($this->threadId)
            : null;

        $messages = $active ? $active->messages : collect();
        $participants = $active ? $active->participants : collect();

        $mentionables = collect();
        $mentionables->push(['key' => 'everyone', 'label' => 'everyone', 'sub' => 'Notify all members']);
        if ($active) {
            if ($participants->isNotEmpty()) {
                foreach ($participants->where('is_me', false) as $p) {
                    $mentionables->push(['key' => $p->name, 'label' => $p->name, 'sub' => $p->role ?: '']);
                }
            } elseif (! $active->is_group && $active->partner_name) {
                $mentionables->push(['key' => $active->partner_name, 'label' => $active->partner_name, 'sub' => $active->partner_role ?: '']);
            }
        }

        return [
            'threads'      => $threads,
            'active'       => $active,
            'messages'     => $messages,
            'participants' => $participants,
            'mentionables' => $mentionables->values()->all(),
            'directory'    => $this->directoryWithoutMe(),
            'tabs'         => [
                'all'      => 'All',
                'parent'   => 'Parents',
                'students' => 'Students',
                'staff'    => 'Staff',
            ],
        ];
    }

    public function formatStamp(?Carbon $dt): string
    {
        if (! $dt) return '';
        if ($dt->isToday())     return $dt->format('H:i');
        if ($dt->isYesterday()) return 'Yesterday';
        if ($dt->diffInDays() < 7) return $dt->format('D');
        return $dt->format('M j');
    }

    protected function initialsOf(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        return mb_strtoupper(mb_substr($parts[0] ?? '', 0, 1) . mb_substr($parts[1] ?? '', 0, 1));
    }

    protected function directory(): array
    {
        return [
            ['name' => 'Andi Laksono',   'role' => 'Principal',                       'color' => 'blue',    'category' => 'staff'],
            ['name' => 'Maya Sari',      'role' => 'Vice Principal',                  'color' => 'blue',    'category' => 'staff'],
            ['name' => 'Indah Lestari',  'role' => 'Math Teacher',                    'color' => 'emerald', 'category' => 'staff'],
            ['name' => 'Rahmat Hidayat', 'role' => 'Science Teacher',                 'color' => 'emerald', 'category' => 'staff'],
            ['name' => 'HR Admin',       'role' => 'Staff',                           'color' => 'violet',  'category' => 'staff'],
            ['name' => 'Siti Aminah',    'role' => 'Counselor',                       'color' => 'rose',    'category' => 'staff'],
            ['name' => 'Budi Pratiwi',   'role' => 'Parent · Dian Pratiwi (4A)',      'color' => 'emerald', 'category' => 'parent'],
            ['name' => 'Rina Permata',   'role' => 'Parent · Fajar Hidayat (4A)',     'color' => 'rose',    'category' => 'parent'],
            ['name' => 'Dewi Susanti',   'role' => 'Parent · Yusuf Prakoso (4B)',     'color' => 'amber',   'category' => 'parent'],
            ['name' => 'Hendra Wijaya',  'role' => 'Parent · Lestari Wijaya (5A)',    'color' => 'blue',    'category' => 'parent'],
            ['name' => 'Dian Pratiwi',   'role' => 'Student · 4A',                    'color' => 'emerald', 'category' => 'students'],
            ['name' => 'Fajar Hidayat',  'role' => 'Student · 4A',                    'color' => 'rose',    'category' => 'students'],
            ['name' => 'Yusuf Prakoso',  'role' => 'Student · 4B',                    'color' => 'amber',   'category' => 'students'],
            ['name' => 'Lestari Wijaya', 'role' => 'Student · 5A',                    'color' => 'blue',    'category' => 'students'],
        ];
    }

    protected function directoryWithoutMe(): array
    {
        return array_values(array_filter($this->directory(), fn ($p) => $p['name'] !== 'Sarah Rahayu'));
    }
}
