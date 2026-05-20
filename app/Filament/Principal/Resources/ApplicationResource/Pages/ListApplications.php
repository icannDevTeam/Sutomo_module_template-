<?php

namespace App\Filament\Principal\Resources\ApplicationResource\Pages;

use App\Filament\Principal\Resources\ApplicationResource;
use App\Models\Application;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;

    public ?string $activeTab = 'incoming';

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('New application')];
    }

    public function getTabs(): array
    {
        // A record is "confirmed" once payment is recorded OR the status has progressed
        // past 'submitted'. This closes the gap where status='payment_confirmed' but
        // payment_status is still NULL, which previously left the record stuck in Incoming.
        $confirmedStatuses = [
            'payment_confirmed', 'exam_scheduled', 'passed', 'failed', 'waitlisted',
            'accepted', 'declined', 'dev_fee', 'books', 'class_assigned',
            'observing', 'id_issued', 'tuition',
        ];
        $confirmedFilter = fn (Builder $query) => $query
            ->where(fn (Builder $q) => $q
                ->where('payment_status', 'paid')
                ->orWhereIn('status', $confirmedStatuses))
            ->whereNotIn('status', ['activated', 'withdrawn']);

        $incomingFilter = fn (Builder $query) => $query
            ->where('status', 'submitted')
            ->where(fn (Builder $q) => $q
                ->where('payment_status', '!=', 'paid')
                ->orWhereNull('payment_status'));

        return [
            'incoming' => Tab::make('Incoming')
                ->icon('heroicon-o-inbox-arrow-down')
                ->badge(Application::query()->tap($incomingFilter)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing($incomingFilter),

            'confirmed' => Tab::make('Confirmed')
                ->icon('heroicon-o-check-badge')
                ->badge(Application::query()->tap($confirmedFilter)->count())
                ->badgeColor('success')
                ->modifyQueryUsing($confirmedFilter),

            'all' => Tab::make('All')
                ->icon('heroicon-o-rectangle-stack'),
        ];
    }
}
