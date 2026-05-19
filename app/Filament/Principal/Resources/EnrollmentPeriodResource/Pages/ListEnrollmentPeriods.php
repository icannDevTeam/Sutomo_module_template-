<?php
namespace App\Filament\Principal\Resources\EnrollmentPeriodResource\Pages;
use App\Filament\Principal\Resources\EnrollmentPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListEnrollmentPeriods extends ListRecords {
    protected static string $resource = EnrollmentPeriodResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
