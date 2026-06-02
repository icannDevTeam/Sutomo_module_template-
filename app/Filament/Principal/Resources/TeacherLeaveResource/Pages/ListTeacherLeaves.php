<?php
namespace App\Filament\Principal\Resources\TeacherLeaveResource\Pages;
use App\Filament\Principal\Resources\TeacherLeaveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeacherLeaves extends ListRecords
{
    protected static string $resource = TeacherLeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('newApplication')
                ->label('New Leave Application')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url(fn () => route('filament.principal.pages.submit-leave')),
            Actions\Action::make('substitutionTimetable')
                ->label('Substitution Timetable')
                ->icon('heroicon-o-arrows-right-left')
                ->color('gray')
                ->url(fn () => route('filament.principal.pages.substitution-timetable')),
        ];
    }
}
