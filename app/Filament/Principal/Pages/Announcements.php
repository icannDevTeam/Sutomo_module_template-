<?php

namespace App\Filament\Principal\Pages;

use App\Models\Announcement;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class Announcements extends Page implements HasForms, HasActions
{
    use InteractsWithActions, InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationGroup = 'Communication';
    protected static ?string $title = 'Announcements';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.principal.pages.announcements';

    #[Url] public string $folder = 'inbox';   // inbox | scheduled | drafts | sent | archived
    #[Url] public string $q = '';
    #[Url] public ?int $selected = null;

    public function mount(): void
    {
        if (! $this->selected) {
            $this->selected = $this->listQuery()->orderByDesc('pinned')->orderByDesc('sent_at')->orderByDesc('updated_at')->value('id');
        }
    }

    public function setFolder(string $folder): void
    {
        $this->folder = $folder;
        $this->selected = $this->listQuery()->orderByDesc('pinned')->orderByDesc('sent_at')->orderByDesc('updated_at')->value('id');
    }

    public function select(int $id): void
    {
        $this->selected = $id;
    }

    public function listQuery()
    {
        $q = Announcement::query();
        match ($this->folder) {
            'scheduled' => $q->where('status', 'scheduled'),
            'drafts'    => $q->where('status', 'draft'),
            'sent'      => $q->where('status', 'sent'),
            'archived'  => $q->where('status', 'archived'),
            default     => $q->whereIn('status', ['sent','scheduled']),
        };
        if (filled($this->q)) {
            $term = '%' . $this->q . '%';
            $q->where(fn ($x) => $x->where('title', 'like', $term)->orWhere('body', 'like', $term)->orWhere('author_name', 'like', $term));
        }
        return $q;
    }

    public function getViewData(): array
    {
        $list = $this->listQuery()
            ->orderByDesc('pinned')
            ->orderByDesc('sent_at')
            ->orderByDesc('updated_at')
            ->take(80)
            ->get();
        $current = $this->selected ? Announcement::find($this->selected) : null;
        $counts = [
            'inbox'     => Announcement::whereIn('status', ['sent','scheduled'])->count(),
            'scheduled' => Announcement::where('status', 'scheduled')->count(),
            'drafts'    => Announcement::where('status', 'draft')->count(),
            'sent'      => Announcement::where('status', 'sent')->count(),
            'archived'  => Announcement::where('status', 'archived')->count(),
        ];
        return compact('list', 'current', 'counts');
    }

    public function createAction(): Action
    {
        return Action::make('create')
            ->label('Create announcement')
            ->icon('heroicon-o-plus')
            ->color('danger')
            ->url(fn () => CreateAnnouncement::getUrl());
    }

    public function editAction(): Action
    {
        return Action::make('edit')
            ->label('Edit')
            ->icon('heroicon-o-pencil-square')
            ->color('gray')
            ->url(fn (array $arguments) => CreateAnnouncement::getUrl(['record' => $arguments['id'] ?? 0]));
    }

    public function publishAction(): Action
    {
        return Action::make('publish')
            ->label('Publish')
            ->icon('heroicon-o-paper-airplane')
            ->color('success')
            ->requiresConfirmation()
            ->action(function (array $arguments) {
                Announcement::findOrFail($arguments['id'])->update(['status' => 'sent', 'sent_at' => now()]);
                Notification::make()->title('Published')->success()->send();
            });
    }

    public function pinAction(): Action
    {
        return Action::make('pin')
            ->label(fn (array $arguments) => Announcement::find($arguments['id'])?->pinned ? 'Unpin' : 'Pin')
            ->icon('heroicon-o-bookmark')
            ->color('warning')
            ->action(function (array $arguments) {
                $a = Announcement::findOrFail($arguments['id']);
                $a->update(['pinned' => ! $a->pinned]);
            });
    }

    public function archiveAction(): Action
    {
        return Action::make('archive')
            ->label('Archive')
            ->icon('heroicon-o-archive-box')
            ->color('gray')
            ->requiresConfirmation()
            ->action(function (array $arguments) {
                Announcement::find($arguments['id'])?->update(['status' => 'archived']);
                Notification::make()->title('Archived')->success()->send();
                $this->selected = null;
            });
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->label('Delete')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function (array $arguments) {
                Announcement::find($arguments['id'])?->delete();
                Notification::make()->title('Deleted')->success()->send();
                $this->selected = null;
            });
    }

    protected function announcementFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('title')->required()->maxLength(160)->placeholder('e.g. UN Day Celebration'),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('author_name')->required()->default(fn () => auth()->user()?->name ?? 'Principal'),
                Forms\Components\TextInput::make('author_role')->default('Principal')->placeholder('e.g. Principal, Coordinator'),
            ]),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('category')->options(Announcement::CATEGORIES)->required()->default('general')->native(false),
                Forms\Components\Toggle::make('pinned')->label('Pin to top')->inline(false),
            ]),
            Forms\Components\Select::make('audiences')->label('Audience')->multiple()->required()
                ->options(Announcement::AUDIENCES)->searchable()->native(false),
            Forms\Components\Select::make('channels')->label('Delivery channels')->multiple()->required()
                ->options(Announcement::CHANNELS)->default(['app'])->native(false),
            Forms\Components\RichEditor::make('body')->label('Message')->required()->columnSpanFull()
                ->disableToolbarButtons(['attachFiles']),
            Forms\Components\DateTimePicker::make('scheduled_at')->label('Schedule for later')->seconds(false)
                ->helperText('Leave empty to publish immediately when you click Publish.'),
        ];
    }

    protected function buildPayload(array $data, string $status): array
    {
        $scheduledAt = $data['scheduled_at'] ?? null;
        $publishFlag = (bool) ($data['_publish'] ?? false);

        if ($publishFlag) {
            $status = $scheduledAt ? 'scheduled' : 'sent';
        } else {
            if ($scheduledAt && $status === 'draft') $status = 'scheduled';
        }

        return [
            'title'        => $data['title'],
            'body'         => $data['body'],
            'author_name'  => $data['author_name'] ?? (auth()->user()?->name ?? 'Principal'),
            'author_role'  => $data['author_role'] ?? 'Principal',
            'audiences'    => $data['audiences'] ?? [],
            'channels'     => $data['channels'] ?? ['app'],
            'category'     => $data['category'] ?? 'general',
            'pinned'       => (bool)($data['pinned'] ?? false),
            'status'       => $status,
            'scheduled_at' => $scheduledAt,
            'sent_at'      => $status === 'sent' ? now() : null,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [ $this->createAction() ];
    }
}
