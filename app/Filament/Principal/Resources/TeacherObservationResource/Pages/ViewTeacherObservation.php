<?php

namespace App\Filament\Principal\Resources\TeacherObservationResource\Pages;

use App\Filament\Principal\Resources\TeacherObservationResource;
use App\Models\TeacherObservation;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewTeacherObservation extends ViewRecord
{
    protected static string $resource = TeacherObservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->url(fn () => route('observations.print', $this->record))
                ->openUrlInNewTab(),
            Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status === 'pending')
                ->form([Forms\Components\Textarea::make('review_notes')->rows(3)])
                ->action(function (array $data) {
                    TeacherObservationResource::transitionStatus($this->record, 'approved', $data['review_notes'] ?? null);
                    $this->refreshFormData(['status']);
                }),
            Actions\Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->status === 'pending')
                ->form([Forms\Components\Textarea::make('review_notes')->rows(3)->required()])
                ->action(function (array $data) {
                    TeacherObservationResource::transitionStatus($this->record, 'rejected', $data['review_notes']);
                    $this->refreshFormData(['status']);
                }),
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $criteria = TeacherObservation::criteriaLabels();

        $criterionEntries = collect($criteria)->map(function (string $label, string $key) {
            return Infolists\Components\Group::make([
                Infolists\Components\TextEntry::make("dimensions.$key")
                    ->label($label)
                    ->badge()
                    ->placeholder('—')
                    ->color(fn ($state) => $state === null ? 'gray' : ((int) $state >= 4 ? 'success' : ((int) $state >= 3 ? 'warning' : 'danger'))),
                Infolists\Components\TextEntry::make("notes_by_criterion.$key")
                    ->label('Notes')
                    ->placeholder('—'),
            ])->columns(2);
        })->values()->all();

        return $infolist->schema([
            Infolists\Components\Section::make('Lesson')->columns(3)->schema([
                Infolists\Components\TextEntry::make('teacher.name')->label('Observee'),
                Infolists\Components\TextEntry::make('observer.name')->label('Observer')->placeholder('—'),
                Infolists\Components\TextEntry::make('observed_at')->dateTime('d M Y H:i'),
                Infolists\Components\TextEntry::make('lesson_subject')->label('Subject')->placeholder('—'),
                Infolists\Components\TextEntry::make('lesson_class_code')->label('Class')->placeholder('—'),
                Infolists\Components\TextEntry::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state ? ucfirst($state) : '—')
                    ->color(fn (?string $state) => TeacherObservationResource::STATUS_COLORS[$state] ?? 'gray'),
            ]),
            Infolists\Components\Section::make('Criteria')->columns(1)->schema($criterionEntries),
            Infolists\Components\Section::make('Narrative')->schema([
                Infolists\Components\TextEntry::make('strengths')->placeholder('—'),
                Infolists\Components\TextEntry::make('action_items')->placeholder('—'),
                Infolists\Components\TextEntry::make('follow_up_date')->date()->placeholder('—'),
            ]),
            Infolists\Components\Section::make('Review')
                ->visible(fn () => $this->record->status !== 'pending' || $this->record->review_notes)
                ->columns(3)->schema([
                    Infolists\Components\TextEntry::make('reviewer.name')->label('Reviewed by')->placeholder('—'),
                    Infolists\Components\TextEntry::make('reviewed_at')->dateTime('d M Y H:i')->placeholder('—'),
                    Infolists\Components\TextEntry::make('average_score')->label('Average')->badge()
                        ->color(fn ($state) => $state === null ? 'gray' : ($state >= 4 ? 'success' : ($state >= 3 ? 'warning' : 'danger'))),
                    Infolists\Components\TextEntry::make('review_notes')->columnSpanFull()->placeholder('—'),
                ]),
        ]);
    }
}
