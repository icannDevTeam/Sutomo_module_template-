<?php

namespace App\Filament\Resources\CandidateResource\Pages;

use App\Filament\Resources\CandidateResource;
use App\Models\AuditLog;
use App\Models\Candidate;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCandidate extends ViewRecord
{
    protected static string $resource = CandidateResource::class;
    protected static string $view = 'filament.resources.candidate.profile';

    public ?string $activeTab = 'overview';
    public string $newNote = '';

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function addNote(): void
    {
        $text = trim($this->newNote);
        if ($text === '') return;
        $meta = $this->record->meta ?? [];
        $meta['notes'] = $meta['notes'] ?? [];
        array_unshift($meta['notes'], [
            'who'  => auth()->user()?->name ?? 'System',
            'when' => now()->toDateString(),
            'text' => $text,
        ]);
        $this->record->update(['meta' => $meta]);
        $this->newNote = '';
        Notification::make()->title('Note posted')->success()->send();
    }

    public function saveScores(int $written = null, int $interview = null, int $micro = null): void
    {
        $this->record->update([
            'score_written'   => $written,
            'score_interview' => $interview,
            'score_micro'     => $micro,
        ]);
        Notification::make()->title('Scores updated')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('email')->icon('heroicon-m-envelope')->color('gray')
                ->url(fn () => 'mailto:' . $this->record->email),
            Actions\Action::make('whatsapp')->icon('heroicon-m-chat-bubble-left')->color('gray')
                ->url(fn () => 'https://wa.me/' . preg_replace('/\D/', '', $this->record->phone ?? '')),
            Actions\Action::make('reject')->color('danger')->icon('heroicon-m-x-mark')
                ->requiresConfirmation()
                ->action(fn () => $this->moveStage('rejected')),
            Actions\Action::make('advance')->color('primary')->icon('heroicon-m-arrow-right')
                ->label('Advance stage')->action(fn () => $this->advance()),
            Actions\EditAction::make(),
        ];
    }

    public function advance(): void
    {
        $order = ['applied','screening','written','interview','psycho','medical','yayasan','opl','active'];
        $idx = array_search($this->record->stage, $order);
        if ($idx === false || $idx >= count($order) - 1) {
            Notification::make()->title('Already at final stage')->warning()->send();
            return;
        }
        $this->moveStage($order[$idx + 1]);
    }

    protected function moveStage(string $to): void
    {
        $from = $this->record->stage;
        if ($from === $to) return;
        $this->record->update(['stage' => $to]);
        AuditLog::create([
            'occurred_at' => now(),
            'user_name'   => auth()->user()?->name ?? 'System',
            'role'        => 'HR',
            'action'      => 'stage.move',
            'target'      => $this->record->code,
            'from_value'  => $from,
            'to_value'    => $to,
            'note'        => 'Updated from candidate profile.',
        ]);
        Notification::make()->title('Moved to ' . (Candidate::STAGES[$to] ?? $to))->success()->send();
    }

    public function getStages(): array
    {
        return ['applied','screening','written','interview','psycho','medical','yayasan','opl','active'];
    }
}
