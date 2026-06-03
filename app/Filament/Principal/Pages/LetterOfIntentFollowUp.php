<?php

namespace App\Filament\Principal\Pages;

use App\Filament\Principal\Resources\LetterOfIntentResource;
use App\Models\LetterOfIntent;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class LetterOfIntentFollowUp extends Page implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'letters-of-intent/{record}/follow-up';
    protected static ?string $title = 'Follow-up — Letter of Intent';
    protected static string $view = 'filament.principal.pages.letter-of-intent-follow-up';

    public ?LetterOfIntent $letter = null;

    public ?array $data = [];

    public function mount(int|string $record): void
    {
        $this->letter = LetterOfIntent::findOrFail($record);
        abort_unless($this->letter->status === 'declined', 404);

        $this->form->fill([
            'meeting_notes'           => $this->letter->meeting_notes,
            'actions_taken'           => $this->letter->actions_taken,
            'follow_up_outcome'       => $this->letter->follow_up_outcome,
            'resignation_letter_path' => $this->letter->resignation_letter_path,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Section::make('Meeting Notes')
                    ->description('Document your conversation with the teacher.')
                    ->schema([
                        Forms\Components\Textarea::make('meeting_notes')
                            ->rows(6)
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Actions Taken')
                    ->description('Interventions, support offered, and any follow-up activities.')
                    ->schema([
                        Forms\Components\Textarea::make('actions_taken')
                            ->rows(4)
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Outcome')
                    ->schema([
                        Forms\Components\Select::make('follow_up_outcome')
                            ->options(LetterOfIntent::FOLLOW_UP_OUTCOMES)
                            ->required()
                            ->live()
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Resignation Letter')
                    ->description('Required scan/upload before the case can be marked Ready for HR or Closed.')
                    ->visible(fn (Forms\Get $get) => $get('follow_up_outcome') === 'resigning')
                    ->schema([
                        Forms\Components\FileUpload::make('resignation_letter_path')
                            ->disk('public')
                            ->directory('letters-of-intent/resignations')
                            ->acceptedFileTypes(['application/pdf', 'image/png', 'image/jpeg'])
                            ->maxSize(8192)
                            ->required(fn (Forms\Get $get) => $get('follow_up_outcome') === 'resigning')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to Letter')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->url(fn () => LetterOfIntentResource::getUrl('view', ['record' => $this->letter->id])),
            Action::make('save')
                ->label('Save Notes')
                ->icon('heroicon-o-check')
                ->color('primary')
                ->action('saveNotes'),
            Action::make('mark_resignation_pending')
                ->label('Mark Resignation Pending')
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->visible(fn () => ($this->data['follow_up_outcome'] ?? null) === 'resigning'
                    && filled($this->data['resignation_letter_path'] ?? null))
                ->action('markResignationPending'),
            Action::make('mark_ready_for_hr')
                ->label('Mark Ready for HR')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn () => ($this->data['follow_up_outcome'] ?? null) === 'resigning'
                    && filled($this->data['resignation_letter_path'] ?? null))
                ->requiresConfirmation()
                ->action('markReadyForHr'),
            Action::make('close_case')
                ->label('Close Case')
                ->icon('heroicon-o-check-badge')
                ->color('gray')
                ->visible(fn () => ($this->data['follow_up_outcome'] ?? null) === 'continuing'
                    || (($this->data['follow_up_outcome'] ?? null) === 'resigning'
                        && filled($this->data['resignation_letter_path'] ?? null)))
                ->requiresConfirmation()
                ->action('closeCase'),
        ];
    }

    public function saveNotes(): void
    {
        $state = $this->form->getState();

        $payload = [
            'meeting_notes'           => $state['meeting_notes'] ?? null,
            'actions_taken'           => $state['actions_taken'] ?? null,
            'follow_up_outcome'       => $state['follow_up_outcome'] ?? null,
            'resignation_letter_path' => $state['resignation_letter_path'] ?? null,
        ];

        if (filled($payload['resignation_letter_path']) && is_null($this->letter->resignation_letter_uploaded_at)) {
            $payload['resignation_letter_uploaded_at'] = now();
        }

        if (is_null($this->letter->follow_up_status)) {
            $payload['follow_up_status'] = 'meeting_logged';
        }

        $this->letter->update($payload);
        $this->letter->refresh();

        Notification::make()->title('Follow-up notes saved')->success()->send();
    }

    public function markResignationPending(): void
    {
        $this->saveNotes();

        if (($this->letter->follow_up_outcome ?? null) !== 'resigning' || empty($this->letter->resignation_letter_path)) {
            Notification::make()->title('Resignation letter must be uploaded first.')->danger()->send();
            return;
        }

        $this->letter->update(['follow_up_status' => 'resignation_pending']);
        Notification::make()->title('Marked as Resignation Pending')->success()->send();
    }

    public function markReadyForHr(): void
    {
        $this->saveNotes();

        if (empty($this->letter->resignation_letter_path)) {
            Notification::make()
                ->title('Cannot mark Ready for HR')
                ->body('Resignation letter must be uploaded before HR handoff.')
                ->danger()
                ->send();
            return;
        }

        $this->letter->update([
            'follow_up_status'     => 'ready_for_hr',
            'hr_handoff_status'    => 'ready',
            'hr_handoff_marked_at' => now(),
        ]);

        Notification::make()->title('Marked Ready for HR — handoff flag set.')->success()->send();
    }

    public function closeCase(): void
    {
        $this->saveNotes();

        $outcome = $this->letter->follow_up_outcome;

        if ($outcome === 'resigning' && empty($this->letter->resignation_letter_path)) {
            Notification::make()
                ->title('Cannot close case')
                ->body('Resignation letter upload is mandatory before closing a resignation case.')
                ->danger()
                ->send();
            return;
        }

        $this->letter->update(['follow_up_status' => 'closed']);
        Notification::make()->title('Case closed')->success()->send();
    }
}
