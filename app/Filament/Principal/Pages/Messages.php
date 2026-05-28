<?php

namespace App\Filament\Principal\Pages;

use App\Models\MessageThread;
use App\Models\ThreadMessage;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;

/**
 * Messages — Principal inbox styled after the Sutomo Teacher messaging mockup.
 *
 * Three-pane layout: left list of threads (with category filter + search),
 * right thread view with composer. State held in Livewire props so the
 * topbar bubble counter stays in sync via simple polling.
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
        $thread->update(['last_message_at' => now()]);
        $this->draft = '';
    }

    protected function markRead(): void
    {
        if ($this->threadId) {
            MessageThread::where('id', $this->threadId)->update(['unread_count' => 0]);
        }
    }

    protected function getViewData(): array
    {
        $threads = MessageThread::query()
            ->when($this->category !== 'all', fn ($q) => $q->where('category', $this->category))
            ->when($this->search !== '', function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(fn ($qq) => $qq->where('partner_name', 'like', $s)
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
            ? MessageThread::with(['messages'])->find($this->threadId)
            : null;

        $messages = $active ? $active->messages : collect();

        return [
            'threads'  => $threads,
            'active'   => $active,
            'messages' => $messages,
            'tabs'     => [
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
}
