<?php

namespace App\Filament\Principal\Resources\TeacherObservationResource\Pages;

use App\Filament\Principal\Resources\TeacherObservationResource;
use App\Models\TeacherObservation;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTeacherObservations extends ListRecords
{
    protected static string $resource = TeacherObservationResource::class;

    public ?string $activeTab = 'pending';

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }

    public function getTabs(): array
    {
        $count = fn (string $status) => TeacherObservation::query()->where('status', $status)->count();

        return [
            'pending' => Tab::make('Pending Review')
                ->icon('heroicon-o-clock')
                ->badge($count('pending'))
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending')),
            'approved' => Tab::make('Approved')
                ->icon('heroicon-o-check-circle')
                ->badge($count('approved'))
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved')),
            'rejected' => Tab::make('Rejected')
                ->icon('heroicon-o-x-circle')
                ->badge($count('rejected'))
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected')),
            'all' => Tab::make('All')
                ->icon('heroicon-o-rectangle-stack'),
        ];
    }
}
